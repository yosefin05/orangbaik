<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * FlipService — Integrasi Resmi dengan Flip API (Big Flip / Acceptance PWF API)
 *
 * Mendukung Virtual Account, Bank Transfer Otomatis, dan Payment Link untuk seluruh bank:
 * BCA, BNI, BRI, BSI, Mandiri, Permata, CIMB Niaga, Danamon, Muamalat.
 *
 * Konfigurasi:
 *     config('payment.flip.api_key')
 *     config('payment.flip.is_production')
 *     config('payment.flip.webhook_token')
 *
 * Dokumentasi: https://docs.flip.id/
 */
class FlipService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    /**
     * Parse an IDR amount without rounding or truncating fractional values.
     * Provider callbacks may encode whole amounts as integers or decimal
     * strings ending in .00; any non-zero fractional component is rejected.
     */
    private function parseWholeRupiahAmount(mixed $value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $amount = trim((string) $value);
        if (!preg_match('/^\\d+(?:\\.0+)?$/D', $amount)) {
            return null;
        }

        $whole = explode('.', $amount, 2)[0];
        $parsed = filter_var($whole, FILTER_VALIDATE_INT);

        return $parsed === false ? null : $parsed;
    }

    public function __construct()
    {
        // Credentials are resolved from the selected PaymentGateway per transaction.
        // Never preload a live key from environment config into a Sandbox request.
        $this->baseUrl = config('payment.flip.base_url_sandbox') ?? 'https://bigflip.id/big_sandbox_api';
        $this->apiKey = null;
    }

    /**
     * Configuration readiness belongs to the selected database gateway.
     */
    public function isConfigured(?PaymentGateway $gateway = null): bool
    {
        return $gateway?->isConfigured() ?? false;
    }


    /**
     * Create OVO, DANA, or QRIS payment through Flip Accept Payment Direct API v3.
     * Staging and Production are supported; the legacy Sandbox endpoint is not.
     */
    public function createEwalletDirectApiV3(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        $gateway = $channel->gateway;
        $bankCode = strtolower(trim($channel->channel_code));

        if (!$gateway || $gateway->code !== 'flip' || !in_array($bankCode, ['ovo', 'dana', 'qris'], true)) {
            throw new \RuntimeException('Direct API v3 hanya mendukung OVO, DANA, dan QRIS melalui gateway Flip.');
        }

        return $this->createDirectApiV3Bill(
            $donasi,
            $pembayaran,
            $channel,
            $bankCode,
            'wallet_account'
        );
    }

    /**
     * Normalize channel identifiers to Flip's documented bank codes.
     */
    public static function normalizeBankCode(string $channelCode): string
    {
        $code = strtolower(trim($channelCode));

        return match ($code) {
            'cimb_niaga' => 'cimb',
            'bsi', 'syariah_mandiri' => 'bsm',
            default => $code,
        };
    }

    /**
     * Create a Virtual Account payment through Flip Accept Payment Direct API v3.
     */
    public function createVirtualAccount(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        $gateway = $channel->gateway;

        if (!$gateway || $gateway->code !== 'flip' || $channel->payment_type !== 'va') {
            throw new \RuntimeException('Channel Virtual Account tidak valid untuk gateway Flip.');
        }

        $bankCode = self::normalizeBankCode($channel->channel_code);
        $supportedBanks = [
            'bca', 'bni', 'bri', 'bsm', 'mandiri',
            'permata', 'cimb', 'danamon', 'seabank',
        ];

        if (!in_array($bankCode, $supportedBanks, true)) {
            throw new \RuntimeException('Bank Virtual Account ini belum didukung oleh integrasi Flip Direct API v3.');
        }

        return $this->createDirectApiV3Bill(
            $donasi,
            $pembayaran,
            $channel,
            $bankCode,
            'virtual_account'
        );
    }

    /**
     * Shared, single-attempt Direct API v3 bill creation.
     * No automatic retries are performed because Create Bill is non-idempotent.
     */
    protected function createDirectApiV3Bill(
        Donasi $donasi,
        Pembayaran $pembayaran,
        PaymentChannel $channel,
        string $bankCode,
        string $senderBankType
    ): array {
        $gateway = $channel->gateway;
        $mode = $gateway->activeCredentialMode();

        if (!in_array($mode, ['staging', 'production'], true)) {
            throw new \RuntimeException('Flip Direct API v3 hanya tersedia pada mode Staging atau Production.');
        }

        $minimumAmount = $senderBankType === 'virtual_account' ? 10000 : 1000;
        $amount = (int) $donasi->nominal;

        if ($amount < $minimumAmount) {
            throw new \RuntimeException(
                'Nominal minimum untuk metode pembayaran ini adalah Rp' .
                number_format($minimumAmount, 0, ',', '.') . '.'
            );
        }

        $apiKey = $gateway->getCredentialForMode('api_key', $mode);
        $webhookToken = $gateway->getCredentialForMode('webhook_token', $mode);

        if (blank($apiKey) || blank($webhookToken)) {
            throw new \RuntimeException('API Key dan Webhook Token Flip untuk mode yang dipilih belum lengkap.');
        }

        if ($mode === 'production') {
            $endpoint = 'https://bigflip.id/api/v3/pwf/bill';
        } else {
            $stagingBaseUrl = rtrim(trim((string) $gateway->getConfigValue('staging_base_url')), '/');
            $parts = parse_url($stagingBaseUrl);
            $stagingHost = is_array($parts)
                ? strtolower((string) ($parts['host'] ?? ''))
                : '';
            $isIpAddress = filter_var($stagingHost, FILTER_VALIDATE_IP) !== false;

            if (
                blank($stagingBaseUrl) ||
                !is_array($parts) ||
                strtolower($parts['scheme'] ?? '') !== 'https' ||
                blank($stagingHost) ||
                $isIpAddress ||
                in_array($stagingHost, ['localhost'], true) ||
                str_ends_with($stagingHost, '.local') ||
                str_ends_with($stagingHost, '.internal') ||
                isset($parts['port']) && (int) $parts['port'] !== 443 ||
                isset($parts['user']) ||
                isset($parts['pass']) ||
                isset($parts['query']) ||
                isset($parts['fragment']) ||
                isset($parts['path']) && $parts['path'] !== ''
            ) {
                throw new \RuntimeException('Staging Base URL Flip tidak valid. Gunakan origin HTTPS staging yang diberikan Flip tanpa path tambahan.');
            }

            $endpoint = rtrim($stagingBaseUrl, '/') . '/big_api/v3/pwf/bill';
        }

        $title = 'Donasi ' . $pembayaran->order_id . ' - ' .
            Str::limit($donasi->campaign->judul ?? 'OrangBaik.id', 35);

        $payload = [
            'title' => Str::limit($title, 80),
            'type' => 'SINGLE',
            'step' => 'direct_api',
            'amount' => $amount,
            'sender_name' => Str::limit($donasi->nama_donatur ?: 'Hamba Allah', 100),
            'sender_email' => $donasi->email ?: 'donatur+' . strtolower($pembayaran->order_id) . '@orangbaik.id',
            'sender_bank' => $bankCode,
            'sender_bank_type' => $senderBankType,
            'reference_id' => $pembayaran->order_id,
            'redirect_url' => route('donasi.bayar.instruksi', [
                'pembayaran' => $pembayaran->id,
                'token' => $pembayaran->payment_token,
            ]),
            'expired_date' => now()->addDay()->format('Y-m-d H:i'),
            'is_address_required' => false,
            'is_phone_number_required' => false,
        ];

        try {
            $response = Http::connectTimeout(5)
                ->timeout(25)
                ->withBasicAuth($apiKey, '')
                ->acceptJson()
                ->asJson()
                ->withOptions(['allow_redirects' => false])
                ->post($endpoint, $payload);
        } catch (\Throwable $exception) {
            Log::error('Flip Direct API v3 request failed before a confirmed response', [
                'order_id' => $pembayaran->order_id,
                'channel' => $bankCode,
                'mode' => $mode,
                'exception_class' => get_class($exception),
            ]);

            throw new \RuntimeException(
                'Flip tidak dapat dihubungi. Periksa dashboard Flip sebelum mencoba ulang agar tidak membuat tagihan ganda.'
            );
        }

        if (!$response->successful()) {
            // Record only a small allowlist of provider diagnostics. Never log the
            // raw response body because it may contain personal or credential data.
            $providerError = $response->json();
            $providerMessage = null;
            $providerErrorCode = null;
            $providerErrorFields = [];
            $providerErrors = [];

            // Keep diagnostics useful without logging the raw response or donor data.
            $sanitizeProviderText = static function ($value, int $limit = 300): ?string {
                if (!is_string($value) && !is_numeric($value)) {
                    return null;
                }

                $message = strip_tags((string) $value);
                $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[redacted-email]', $message);
                $message = preg_replace('/(?:\+?62|0)\d{8,13}/', '[redacted-phone]', $message);
                // Also redact non-phone numeric identifiers such as bank
                // account numbers and provider/customer reference numbers.
                $message = preg_replace('/\b\d{8,}\b/', '[redacted-number]', $message);
                $message = preg_replace(
                    '/\b(password|token|secret|api[_-]?key|authorization)\s*[:=]\s*[^\s,;]+/i',
                    '$1=[redacted]',
                    $message
                );

                return Str::limit(trim($message), $limit);
            };

            if (is_array($providerError)) {
                foreach (['code', 'error_code'] as $key) {
                    if (isset($providerError[$key]) && is_scalar($providerError[$key])) {
                        $providerErrorCode = $sanitizeProviderText($providerError[$key], 100);
                        break;
                    }
                }

                foreach (['message', 'error_description'] as $key) {
                    if (isset($providerError[$key]) && is_string($providerError[$key])) {
                        $providerMessage = $sanitizeProviderText($providerError[$key]);
                        break;
                    }
                }

                if (isset($providerError['errors']) && is_array($providerError['errors'])) {
                    // Flip may return a list of {attribute, code, message} objects.
                    if (array_is_list($providerError['errors'])) {
                        foreach (array_slice($providerError['errors'], 0, 20) as $error) {
                            if (!is_array($error)) {
                                continue;
                            }

                            $attribute = $sanitizeProviderText($error['attribute'] ?? null, 80);
                            $code = $sanitizeProviderText($error['code'] ?? null, 100);
                            $message = $sanitizeProviderText($error['message'] ?? null, 300);

                            if ($attribute !== null) {
                                $providerErrorFields[] = $attribute;
                            }

                            if ($attribute !== null || $code !== null || $message !== null) {
                                $providerErrors[] = array_filter([
                                    'attribute' => $attribute,
                                    'code' => $code,
                                    'message' => $message,
                                ], static fn ($value) => $value !== null);
                            }
                        }
                    } else {
                        // Preserve compatibility with older associative error maps.
                        $providerErrorFields = array_slice(array_map(
                            static fn ($key) => Str::limit((string) $key, 80),
                            array_keys($providerError['errors'])
                        ), 0, 20);
                    }
                }
            }
            Log::warning('Flip Direct API v3 rejected bill creation', [
                'order_id' => $pembayaran->order_id,
                'channel' => $bankCode,
                'mode' => $mode,
                'http_status' => $response->status(),
                'provider_error_code' => $providerErrorCode,
                'provider_message' => $providerMessage,
                'provider_error_fields' => array_values(array_unique($providerErrorFields)),
                'provider_errors' => $providerErrors,
                'response_content_type' => Str::limit((string) $response->header('Content-Type'), 100),
            ]);

            throw new \RuntimeException(
                'Flip menolak pembuatan pembayaran (HTTP ' . $response->status() . ').'
            );
        }

        $data = json_decode($response->body(), true, 512, JSON_BIGINT_AS_STRING);
        $linkId = data_get($data, 'link_id');
        $paymentId = data_get($data, 'bill_payment.id');
        $paymentUrl = data_get($data, 'payment_url');

        if (!is_array($data) || blank($linkId) || blank($paymentId) || blank($paymentUrl)) {
            Log::error('Flip Direct API v3 returned an incomplete bill response', [
                'order_id' => $pembayaran->order_id,
                'channel' => $bankCode,
                'mode' => $mode,
                'http_status' => $response->status(),
            ]);

            throw new \RuntimeException(
                'Respons Flip belum lengkap. Jangan membuat transaksi ulang sebelum memeriksa dashboard Flip.'
            );
        }

        $billPayment = data_get($data, 'bill_payment', []);
        $receiver = data_get($billPayment, 'receiver_bank_account', []);

        $accountNumber = data_get($receiver, 'account_number');
        $accountName = data_get($receiver, 'account_holder');
        $qrCodeData = data_get($receiver, 'qr_code_data');

        if ($senderBankType === 'virtual_account' && blank($accountNumber)) {
            Log::error('Flip VA v3 response missing virtual account number', [
                'order_id' => $pembayaran->order_id,
                'channel' => $bankCode,
            ]);

            throw new \RuntimeException(
                'Flip belum mengembalikan nomor Virtual Account. Periksa dashboard Flip sebelum mencoba ulang.'
            );
        }

        if ($bankCode === 'qris' && blank($qrCodeData) && blank($paymentUrl)) {
            throw new \RuntimeException('Flip belum mengembalikan QRIS atau tautan pembayaran yang valid.');
        }

        $uniqueCode = (int) data_get($billPayment, 'unique_code', 0);
        $finalAmount = (int) data_get($billPayment, 'amount', $amount);

        $gatewayResponse = array_merge($pembayaran->gateway_response ?? [], [
            'credential_mode' => $mode,
            'flip_api_version' => 'v3',
            'flip_flow' => 'direct_api',
            'flip_id' => (string) $linkId,
            'link_id' => (string) $linkId,
            'flip_payment_id' => (string) $paymentId,
            'payment_url' => $paymentUrl,
            'reference_id' => $pembayaran->order_id,
            'requested_amount' => $amount,
            'final_amount' => $finalAmount,
            'unique_code' => $uniqueCode,
            'sender_bank' => $bankCode,
            'sender_bank_type' => $senderBankType,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'qr_code_data' => $qrCodeData,
            'expires_at' => data_get($data, 'expired_date'),
        ]);

        $pembayaran->update([
            // Flip link_id is the bill identifier used by the v3 Get Payment API and callbacks.
            'transaction_id' => (string) $linkId,
            'gateway_response' => $gatewayResponse,
        ]);

        Log::info('Flip Direct API v3 bill created', [
            'order_id' => $pembayaran->order_id,
            'link_id' => (string) $linkId,
            'payment_id' => (string) $paymentId,
            'channel' => $bankCode,
            'mode' => $mode,
        ]);

        return [
            'type' => $senderBankType === 'virtual_account' ? 'virtual_account' : 'redirect',
            'provider_type' => $bankCode === 'qris' ? 'flip_qris' : 'flip_direct_api',
            'order_id' => $pembayaran->order_id,
            'donasi_id' => $donasi->id,
            'bank_code' => $bankCode,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'qr_code_data' => $qrCodeData,
            'payment_url' => $paymentUrl,
            'redirect_url' => $paymentUrl,
            'amount' => $finalAmount,
            'unique_code' => $uniqueCode,
            'expired_date' => data_get($data, 'expired_date'),
        ];
    }

    /**
     * Process Flip Accept Payment callbacks.
     *
     * The callback token is verified against the credential mode stored with
     * the payment. Raw callback payloads are never written to application logs.
     */
    public function handleWebhook(string $token, array $payload): bool
    {
        $data = $payload;

        if (isset($payload['data'])) {
            if (is_string($payload['data'])) {
                $decoded = json_decode($payload['data'], true, 512, JSON_BIGINT_AS_STRING);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            } elseif (is_array($payload['data'])) {
                $data = $payload['data'];
            }
        }

        $billLinkId = isset($data['bill_link_id']) ? (string) $data['bill_link_id'] : null;
        $paymentId = isset($data['id']) ? (string) $data['id'] : (
            isset($data['bill_payment_id']) ? (string) $data['bill_payment_id'] : null
        );
        $referenceId = isset($data['reference_id']) ? (string) $data['reference_id'] : null;
        $status = strtoupper(trim((string) ($data['status'] ?? '')));

        if ((!$billLinkId && !$paymentId && !$referenceId) || $status === '') {
            Log::warning('Flip webhook rejected: required identifiers or status are missing.', [
                'has_bill_link_id' => filled($billLinkId),
                'has_payment_id' => filled($paymentId),
                'has_reference_id' => filled($referenceId),
                'has_status' => $status !== '',
            ]);
            return false;
        }

        $pembayaran = Pembayaran::with([
            'donasi',
            'paymentChannel.gateway',
            'paymentGateway',
        ])->where(function ($query) use ($billLinkId, $paymentId, $referenceId) {
            if ($billLinkId) {
                $query->orWhere('transaction_id', $billLinkId)
                    ->orWhere('gateway_response->link_id', $billLinkId);
            }

            if ($paymentId) {
                $query->orWhere('gateway_response->flip_payment_id', $paymentId)
                    ->orWhere('transaction_id', $paymentId);
            }

            if ($referenceId) {
                $query->orWhere('order_id', $referenceId);
            }
        })->first();

        if (!$pembayaran) {
            Log::warning('Flip webhook rejected: payment record not found.', [
                'bill_link_id' => $billLinkId,
                'payment_id' => $paymentId,
                'reference_id_present' => filled($referenceId),
            ]);
            return false;
        }

        $gateway = $pembayaran->resolvedGateway();

        if (!$gateway || $gateway->code !== 'flip') {
            Log::warning('Flip webhook rejected: matched transaction does not belong to Flip.', [
                'order_id' => $pembayaran->order_id,
            ]);
            return false;
        }

        $gatewayResponse = $pembayaran->gateway_response ?? [];
        $credentialMode = data_get($gatewayResponse, 'credential_mode')
            ?: $gateway->activeCredentialMode();

        $expectedToken = $gateway->getCredentialForMode('webhook_token', $credentialMode);
        $receivedToken = $token !== '' ? $token : (string) ($payload['token'] ?? '');

        if (
            blank($expectedToken) ||
            blank($receivedToken) ||
            !hash_equals((string) $expectedToken, (string) $receivedToken)
        ) {
            Log::warning('Flip webhook rejected: callback token mismatch.', [
                'order_id' => $pembayaran->order_id,
                'credential_mode' => $credentialMode,
            ]);
            return false;
        }

        // Cross-check every supplied identifier against the stored transaction.
        $storedLinkId = data_get($gatewayResponse, 'link_id');
        $storedPaymentId = data_get($gatewayResponse, 'flip_payment_id');

        if ($billLinkId && $storedLinkId && !hash_equals((string) $storedLinkId, $billLinkId)) {
            Log::warning('Flip webhook rejected: bill link ID mismatch.', [
                'order_id' => $pembayaran->order_id,
            ]);
            return false;
        }

        if ($paymentId && $storedPaymentId && !hash_equals((string) $storedPaymentId, $paymentId)) {
            Log::warning('Flip webhook rejected: payment ID mismatch.', [
                'order_id' => $pembayaran->order_id,
            ]);
            return false;
        }

        if ($referenceId && !hash_equals((string) $pembayaran->order_id, $referenceId)) {
            Log::warning('Flip webhook rejected: merchant reference mismatch.', [
                'order_id' => $pembayaran->order_id,
            ]);
            return false;
        }

        $internalStatus = match ($status) {
            'SUCCESSFUL', 'SETTLEMENT', 'PAID' => 'settlement',
            'FAILED' => 'failed',
            'CANCELLED', 'EXPIRED' => 'expired',
            default => null,
        };

        if ($internalStatus === null) {
            Log::warning('Flip webhook rejected: unknown payment status.', [
                'order_id' => $pembayaran->order_id,
                'status' => $status,
            ]);
            return false;
        }

        // Terminal states cannot be reversed by a later or out-of-order callback.
        if (in_array($pembayaran->transaction_status, ['settlement', 'failed', 'expired', 'expire'], true)) {
            return $pembayaran->transaction_status === $internalStatus
                || ($pembayaran->transaction_status === 'expire' && $internalStatus === 'expired');
        }

        if ($internalStatus === 'settlement') {
            $expectedAmount = (int) ($pembayaran->donasi?->nominal ?? 0);
            $callbackAmount = array_key_exists('amount', $data)
                ? $this->parseWholeRupiahAmount($data['amount'])
                : null;
            $savedUniqueCode = (int) data_get($gatewayResponse, 'unique_code', 0);

            if ($callbackAmount === null || $expectedAmount <= 0) {
                Log::error('Flip webhook settlement rejected: amount is missing.', [
                    'order_id' => $pembayaran->order_id,
                ]);
                return false;
            }

            $amountMatches = $callbackAmount === $expectedAmount
                || ($savedUniqueCode > 0 && $callbackAmount === ($expectedAmount + $savedUniqueCode));

            if (!$amountMatches) {
                Log::error('Flip webhook settlement rejected: amount mismatch.', [
                    'order_id' => $pembayaran->order_id,
                    'expected_amount' => $expectedAmount,
                    'callback_amount' => $callbackAmount,
                ]);
                return false;
            }
        }

        // Store only the callback fields needed for audit; never overwrite our
        // credential mode, reference, bill ID, or other internal gateway metadata.
        $gatewayResponse['last_callback'] = [
            'payment_id' => $paymentId,
            'bill_link_id' => $billLinkId,
            'reference_id' => $referenceId,
            'amount' => isset($data['amount']) ? (int) $data['amount'] : null,
            'status' => $status,
            'sender_bank' => isset($data['sender_bank']) ? (string) $data['sender_bank'] : null,
            'created_at' => $data['created_at'] ?? null,
            'received_at' => now()->toIso8601String(),
        ];

        // Lock and re-check the row so concurrent callback retries cannot race
        // or overwrite a terminal payment state.
        return DB::transaction(function () use (
            $pembayaran,
            $internalStatus,
            $gatewayResponse,
            $status
        ): bool {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->first();

            if (!$locked) {
                return false;
            }

            if (in_array($locked->transaction_status, ['settlement', 'failed', 'expired', 'expire'], true)) {
                return $locked->transaction_status === $internalStatus
                    || ($locked->transaction_status === 'expire' && $internalStatus === 'expired');
            }

            $update = [
                'transaction_status' => $internalStatus,
                'gateway_response' => $gatewayResponse,
            ];

            if ($internalStatus === 'settlement') {
                $update['paid_at'] = now();
            }

            $locked->update($update);

            Log::info('Flip webhook processed successfully.', [
                'order_id' => $locked->order_id,
                'flip_status' => $status,
                'internal_status' => $internalStatus,
            ]);

            return true;
        });
    }
}
