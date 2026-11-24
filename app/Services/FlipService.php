<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
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
     * Create an OVO/DANA payment with Flip Accept Payment Direct API v3.
     * Flip v3 E-Wallet is available through the merchant staging environment
     * or the live endpoint; it is not available on the legacy Sandbox endpoint.
     */
    public function createEwalletDirectApiV3(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        $gateway = $channel->gateway;
        if (!$gateway || !in_array(strtolower($channel->channel_code), ['ovo', 'dana'], true)) {
            throw new \\RuntimeException('Direct API v3 hanya digunakan untuk channel OVO dan DANA milik Flip.');
        }

        $mode = $gateway->activeCredentialMode();
        if (!in_array($mode, ['staging', 'production'], true)) {
            throw new \\RuntimeException('OVO dan DANA melalui Flip Direct API v3 memerlukan mode Staging Flip atau Production. API v3 tidak tersedia pada Sandbox.');
        }

        $apiKey = $gateway->getCredentialForMode('api_key', $mode);
        $webhookToken = $gateway->getCredentialForMode('webhook_token', $mode);
        if (blank($apiKey) || blank($webhookToken)) {
            throw new \\RuntimeException('API Secret Key dan Webhook Token Flip untuk mode yang dipilih belum lengkap.');
        }

        $endpoint = $mode === 'production'
            ? 'https://bigflip.id/api/v3/pwf/bill'
            : rtrim((string) $gateway->getConfigValue('staging_base_url'), '/') . '/big_api/v3/pwf/bill';

        if ($mode === 'staging' && blank($gateway->getConfigValue('staging_base_url'))) {
            throw new \\RuntimeException('Staging Base URL Flip belum diisi pada dashboard admin.');
        }

        $payload = [
            'title' => 'Donasi: ' . Str::limit($donasi->campaign->judul ?? 'OrangBaik.id', 27),
            'type' => 'SINGLE',
            'step' => 'direct_api',
            'amount' => (int) $donasi->nominal,
            'sender_name' => $donasi->nama_donatur ?: 'Hamba Allah',
            'sender_email' => $donasi->email ?? 'donatur@orangbaik.id',
            'sender_bank' => strtolower($channel->channel_code),
            'sender_bank_type' => 'wallet_account',
            'redirect_url' => route('donasi.bayar.instruksi', [
                'pembayaran' => $pembayaran->id,
                'token' => $pembayaran->payment_token,
            ]),
            'reference_id' => $pembayaran->order_id,
            'expired_date' => now()->addDay()->format('Y-m-d H:i'),
        ];

        try {
            $response = Http::connectTimeout(5)
                ->timeout(25)
                ->withBasicAuth($apiKey, '')
                ->acceptJson()
                ->asJson()
                ->post($endpoint, $payload);
        } catch (\\Throwable $exception) {
            Log::error('Flip Direct API v3 request failed before a confirmed response', [
                'order_id' => $pembayaran->order_id,
                'channel' => $channel->channel_code,
                'mode' => $mode,
                'exception_class' => get_class($exception),
            ]);

            throw new \\RuntimeException('Flip Direct API v3 tidak dapat dihubungi. Periksa status transaksi di dashboard Flip sebelum mencoba ulang.');
        }

        if (!$response->successful()) {
            Log::warning('Flip Direct API v3 rejected payment creation', [
                'order_id' => $pembayaran->order_id,
                'channel' => $channel->channel_code,
                'mode' => $mode,
                'http_status' => $response->status(),
            ]);

            throw new \\RuntimeException('Flip menolak pembuatan pembayaran e-wallet (HTTP ' . $response->status() . ').');
        }

        $data = $response->json();
        $paymentUrl = data_get($data, 'payment_url') ?? data_get($data, 'bill_payment.payment_url');
        $paymentId = data_get($data, 'bill_payment.id') ?? data_get($data, 'id');

        if (!is_array($data) || blank($paymentUrl) || blank($paymentId)) {
            Log::error('Flip Direct API v3 returned an incomplete payment response', [
                'order_id' => $pembayaran->order_id,
                'mode' => $mode,
                'http_status' => $response->status(),
            ]);

            throw new \\RuntimeException('Respons Flip Direct API v3 belum memuat ID pembayaran dan payment URL yang diperlukan.');
        }

        $pembayaran->update([
            'transaction_id' => (string) $paymentId,
            'gateway_response' => array_merge($pembayaran->gateway_response ?? [], [
                'credential_mode' => $mode,
                'flip_api_version' => 'v3',
                'flip_flow' => 'direct_api',
                'flip_payment_id' => $paymentId,
                'payment_url' => $paymentUrl,
                'reference_id' => $pembayaran->order_id,
                'requested_amount' => (int) $donasi->nominal,
                'sender_bank' => strtolower($channel->channel_code),
                'sender_bank_type' => 'wallet_account',
                'expires_at' => data_get($data, 'expired_date') ?? data_get($data, 'bill_payment.expired_date'),
            ]),
        ]);

        return [
            'type' => 'redirect',
            'provider_type' => 'flip_ewallet',
            'order_id' => $pembayaran->order_id,
            'donasi_id' => $donasi->id,
            'redirect_url' => $paymentUrl,
            'payment_url' => $paymentUrl,
        ];
    }

    /**
     * Normalisasi kode bank untuk Flip API.
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
     * Menentukan tipe bank default pada Flip:
     * - Bank dengan Virtual Account: bca, bni, bri, bsi, mandiri, permata, cimb, danamon.
     * - Bank Muamalat di Flip menggunakan bank_account (transfer bank unik).
     */
    public static function determineSenderBankType(string $bankCode): string
    {
        $code = strtolower(trim($bankCode));

        if ($code === 'muamalat') {
            return 'bank_account';
        }

        return 'virtual_account';
    }

    /**
     * Buat Virtual Account / Bill Payment via Flip API.
     * Menggunakan satu request agar kegagalan jaringan tidak membuat bill duplikat.
     *
     * @param  Donasi          $donasi
     * @param  Pembayaran      $pembayaran
     * @param  PaymentChannel  $channel
     * @return array  Data pembayaran dari Flip
     * @throws \Exception jika Flip belum dikonfigurasi atau semua request gagal
     */
    public function createVirtualAccount(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        $gateway = $channel->gateway;
        if (!$gateway) {
            throw new \RuntimeException('Gateway Flip tidak ditemukan.');
        }

        $credentialMode = $gateway->activeCredentialMode();
        $isProduction = $credentialMode === 'production';

        $this->baseUrl = $isProduction
            ? (config('payment.flip.base_url_production') ?? 'https://bigflip.id/api')
            : (config('payment.flip.base_url_sandbox') ?? 'https://bigflip.id/big_sandbox_api');
        $this->apiKey = $gateway->getCredentialForMode('api_key', $credentialMode);

        if (!$gateway || !$gateway->isConfigured()) {
            throw new \RuntimeException('Kredensial Flip belum lengkap. Lengkapi API Key dan Webhook Token pada pengaturan gateway.');
        }

        $bankCode = self::normalizeBankCode($channel->channel_code);
        $senderBankType = self::determineSenderBankType($bankCode);
        $title = 'Donasi: ' . Str::limit($donasi->campaign->judul ?? 'OrangBaik.id', 27);

        // Siapkan base payload
        $basePayload = [
            'title' => $title,
            'type' => 'SINGLE',
            'amount' => (int) $donasi->nominal,
            'expired_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'redirect_url' => route('donasi.bayar.instruksi', [
                'pembayaran' => $pembayaran->id,
                'token' => $pembayaran->payment_token,
            ]),
            'is_address_required' => 0,
            'is_phone_number_required' => 0,
            'sender_name' => $donasi->nama_donatur ?: 'Hamba Allah',
            'sender_email' => $donasi->email ?? 'donatur@orangbaik.id',
            'sender_phone_number' => $donasi->no_hp ?? '08123456789',
        ];

        // Create exactly one bill per donation. Do not retry a non-idempotent create
        // request after timeouts or ambiguous provider responses.
        $requestPayload = array_merge($basePayload, [
            'step' => 3,
            'sender_bank' => $bankCode,
            'sender_bank_type' => $senderBankType,
        ]);

        try {
            $response = $this->sendBillRequest($requestPayload);
        } catch (\Throwable $exception) {
            Log::error('Flip bill request failed before a confirmed response', [
                'bank' => $bankCode,
                'order_id' => $pembayaran->order_id,
                'exception_class' => get_class($exception),
            ]);

            throw new \RuntimeException('Flip tidak dapat dihubungi. Periksa koneksi dan status transaksi sebelum mencoba kembali.');
        }

        if (!$response->successful()) {
            Log::warning('Flip rejected bill creation request', [
                'bank' => $bankCode,
                'order_id' => $pembayaran->order_id,
                'http_status' => $response->status(),
            ]);

            throw new \RuntimeException('Flip menolak pembuatan pembayaran (HTTP ' . $response->status() . ').');
        }

        $successData = $response->json();
        if (!is_array($successData) || empty($successData)) {
            Log::error('Flip returned an invalid bill creation response', [
                'order_id' => $pembayaran->order_id,
                'http_status' => $response->status(),
            ]);

            throw new \RuntimeException('Respons Flip tidak valid. Jangan membuat transaksi ulang sebelum memeriksa dashboard Flip.');
        }

        return $this->processSuccessfulFlipResponse($successData, $pembayaran, $donasi, $channel, $bankCode, $credentialMode);
    }

    /**
     * Kirim satu HTTP POST ke endpoint Flip PWF v2.
     */
    protected function sendBillRequest(array $payload)
    {
        return Http::connectTimeout(5)
            ->timeout(20)
            ->withBasicAuth($this->apiKey, '')
            ->asForm()
            ->post($this->baseUrl . '/v2/pwf/bill', $payload);
    }

    /**
     * Proses parsing response sukses dari Flip dan simpan ke Pembayaran.
     */
    protected function processSuccessfulFlipResponse(
        array $data,
        Pembayaran $pembayaran,
        Donasi $donasi,
        PaymentChannel $channel,
        string $bankCode,
        string $credentialMode
    ): array {
        // Ekstrak nomor Virtual Account / Rekening dari berbagai kemungkinan format response Flip
        $accountNumber = $data['bill_payment']['receiver_bank_account']['account_number']
            ?? $data['bill_payment']['account_number']
            ?? $data['bill_payment']['virtual_account_number']
            ?? $data['bill_payment']['va_number']
            ?? $data['receiver_bank_account']['account_number']
            ?? $data['virtual_account_number']
            ?? $data['account_number']
            ?? $data['va_number']
            ?? null;

        // Ekstrak nama rekening penerima
        $accountName = $data['bill_payment']['receiver_bank_account']['account_name']
            ?? $data['receiver_bank_account']['account_name']
            ?? $data['account_name']
            ?? $channel->account_name
            ?? 'OrangBaik.id / Flip';

        // URL pembayaran Flip (PWF Link)
        $linkUrl = $data['link_url']
            ?? $data['url']
            ?? null;

        $paymentUrl = $data['payment_url']
            ?? $linkUrl;

        // Flip Sandbox v2 kadang mengembalikan URL tanpa scheme.
            // Normalisasi agar browser tidak menganggapnya sebagai relative URL.
        if ($paymentUrl && !preg_match('#^https?://#i', $paymentUrl)) {
            $paymentUrl = 'https://' . $paymentUrl;
        }

        // Flip ID / Link ID
        $flipId = $data['link_id']
            ?? $data['id']
            ?? $data['bill_payment']['id']
            ?? null;

        // Total nominal (jika Flip menambahkan kode unik untuk transfer)
        $finalAmount = $data['bill_payment']['amount']
            ?? $data['amount']
            ?? (int) $donasi->nominal;

        $uniqueCode = $data['bill_payment']['unique_code'] ?? 0;

        // Simpan transaction_id dari Flip dan gateway response
        $pembayaran->update([
            'transaction_id' => (string) $flipId,
            'gateway_response' => array_merge($data, [
                'credential_mode' => $credentialMode,
                'flip_id' => $flipId,
                'link_id' => $data['link_id'] ?? $flipId,
                'account_number' => $accountNumber,
                'account_name' => $accountName,
                'link_url' => $linkUrl,
                'payment_url' => $paymentUrl,
                'final_amount' => $finalAmount,
                'unique_code' => $uniqueCode,
            ]),
        ]);

        Log::info('Flip payment created successfully', [
            'order_id' => $pembayaran->order_id,
            'flip_id' => $flipId,
            'bank_code' => $bankCode,
            'account_number' => $accountNumber,
            'amount' => $finalAmount,
        ]);

        return [
            'id' => $flipId,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'bank_code' => $bankCode,
            'link_url' => $linkUrl,
            'payment_url' => $paymentUrl,
            'expired_date' => $data['expired_date'] ?? null,
            'amount' => $finalAmount,
            'unique_code' => $uniqueCode,
        ];
    }

    /**
     * Menangani webhook callback dari Flip.
     * Flip mengirim POST request dengan header X-CALLBACK-TOKEN atau token di payload.
     *
     * @param  string  $token    Nilai dari header X-CALLBACK-TOKEN
     * @param  array   $payload  Body request dari Flip
     * @return bool
     */
    public function handleWebhook(string $token, array $payload): bool
    {
        /*
         * =====================================================
         * 1. VERIFIKASI TOKEN (FAIL-CLOSED)
         * =====================================================
         */
        /*
         * =====================================================
         * 2. PARSE PAYLOAD DARI FLIP
         * Flip dapat mengirim data dalam form-encoded 'data' JSON string
         * atau langsung sebagai payload JSON.
         * =====================================================
         */
        $data = $payload;
        if (isset($payload['data'])) {
            if (is_string($payload['data'])) {
                $decoded = json_decode($payload['data'], true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            } elseif (is_array($payload['data'])) {
                $data = $payload['data'];
            }
        }

        // Flip PWF Callback ID fields
        $billLinkId = $data['bill_link_id'] ?? $data['link_id'] ?? null;
        $paymentId = $data['id'] ?? $data['bill_payment_id'] ?? data_get($data, 'bill_payment.id') ?? null;
        $referenceId = $data['reference_id'] ?? data_get($data, 'bill_payment.reference_id') ?? null;
        $status = $data['status'] ?? data_get($data, 'bill_payment.status') ?? null;

        if ((!$billLinkId && !$paymentId && !$referenceId) || !$status) {
            Log::warning('Flip webhook: data tidak lengkap', ['payload' => $payload]);
            return false;
        }

        /*
         * =====================================================
         * 3. CARI PEMBAYARAN BERDASARKAN TRANSACTION_ID (FLIP LINK ID ATAU PAYMENT ID)
         * =====================================================
         */
        $pembayaran = null;
        if ($billLinkId || $paymentId) {
            $pembayaran = Pembayaran::with(['donasi', 'paymentChannel.gateway', 'paymentGateway'])
                ->where(function ($q) use ($billLinkId, $paymentId) {
                    if ($billLinkId) {
                        $q->where('transaction_id', (string) $billLinkId);
                    }
                    if ($paymentId) {
                        $q->orWhere('transaction_id', (string) $paymentId);
                    }
                })
                ->first();
        }

        // Direct API v3 can identify the transaction by the merchant reference ID.
        if (!$pembayaran && $referenceId) {
            $pembayaran = Pembayaran::with(['donasi', 'paymentChannel.gateway', 'paymentGateway'])
                ->where('order_id', (string) $referenceId)
                ->first();
        }

        if (!$pembayaran && $billLinkId) {
            $pembayaran = Pembayaran::with(['donasi', 'paymentChannel.gateway'])
                ->where('gateway_response->link_id', $billLinkId)
                ->first();
        }

        if (!$pembayaran) {
            Log::warning('Flip webhook: pembayaran tidak ditemukan', [
                'bill_link_id' => $billLinkId,
                'payment_id' => $paymentId,
            ]);
            return false;
        }

        /*
         * =====================================================
         * 4. VERIFIKASI TOKEN SESUAI GATEWAY TRANSAKSI
         * =====================================================
         */
        $gateway = $pembayaran->resolvedGateway();
        if (!$gateway || $gateway->code !== 'flip') {
            Log::warning('Flip webhook ditolak: transaksi bukan milik gateway Flip.');
            return false;
        }

        $credentialMode = data_get($pembayaran->gateway_response ?? [], 'credential_mode')
            ?: $gateway->activeCredentialMode();
        $expectedToken = $gateway->getCredentialForMode('webhook_token', $credentialMode);
        $receivedToken = !empty($token) ? $token : ($payload['token'] ?? '');

        if (blank($expectedToken) || blank($receivedToken) || !hash_equals((string) $expectedToken, (string) $receivedToken)) {
            Log::warning('Flip webhook ditolak: token tidak cocok atau tidak dikonfigurasi.');
            return false;
        }

        /*
         * =====================================================
         * 5. CEGAH PERUBAHAN STATUS TERMINAL (IDEMPOTENT)
         * =====================================================
         */
        if (in_array($pembayaran->transaction_status, ['settlement', 'failed', 'expired', 'expire'], true)) {
            Log::info('Flip webhook: transaksi sudah settlement (idempotent skip)', [
                'order_id' => $pembayaran->order_id,
            ]);
            return true;
        }

        /*
         * =====================================================
         * 5. MAP STATUS FLIP → STATUS INTERNAL (pending, settlement, failed, expired)
         * =====================================================
         */
        $internalStatus = match (strtoupper($status)) {
            'SUCCESSFUL', 'SETTLEMENT', 'PAID' => 'settlement',
            'FAILED' => 'failed',
            'CANCELLED', 'EXPIRED' => 'expired',
            default => null,
        };

        if ($internalStatus === null) {
            Log::warning('Flip webhook ditolak: status tidak dikenal.', [
                'order_id' => $pembayaran->order_id,
            ]);
            return false;
        }

        /*
         * =====================================================
         * 5.5. VALIDASI INTEGRITAS AMOUNT
         * =====================================================
         */
        if ($internalStatus === 'settlement') {
            $expectedNominal = (int) ($pembayaran->donasi?->nominal ?? 0);
            $callbackAmount = isset($data['amount'])
                ? (int) $data['amount']
                : (isset($data['bill_payment']['amount']) ? (int) $data['bill_payment']['amount'] : null);

            if ($callbackAmount === null) {
                Log::error('Flip webhook settlement ditolak: nominal callback tidak tersedia.', [
                    'order_id' => $pembayaran->order_id,
                ]);
                return false;
            }

            if ($callbackAmount !== null) {
                $savedUniqueCode = (int) ($pembayaran->gateway_response['unique_code'] ?? 0);
                $isMatching = ($callbackAmount === $expectedNominal) ||
                    ($savedUniqueCode > 0 && $callbackAmount === ($expectedNominal + $savedUniqueCode));

                if (!$isMatching) {
                    Log::error('Flip webhook: nominal tidak sesuai (Amount Integrity Mismatch)', [
                        'bill_link_id' => $billLinkId,
                        'payment_id' => $paymentId,
                        'expected_amount' => $expectedNominal,
                        'callback_amount' => $callbackAmount,
                        'order_id' => $pembayaran->order_id,
                    ]);
                    return false;
                }
            }
        }

        $updateData = [
            'transaction_status' => $internalStatus,
            'gateway_response' => array_merge($pembayaran->gateway_response ?? [], $data),
        ];

        if ($internalStatus === 'settlement') {
            $updateData['paid_at'] = now();
        }

        $pembayaran->update($updateData);

        Log::info('Flip webhook berhasil diproses', [
            'bill_link_id' => $billLinkId,
            'payment_id' => $paymentId,
            'flip_status' => $status,
            'internal_status' => $internalStatus,
            'order_id' => $pembayaran->order_id,
        ]);

        return true;
    }
}
