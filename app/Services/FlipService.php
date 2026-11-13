<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Services\PaymentWebhookProcessor;
use App\Support\PaymentAmount;
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
    protected ?string $webhookToken;

    public function __construct()
    {
        $isProduction = config('payment.flip.is_production', false);
        $this->baseUrl = $isProduction
            ? (config('payment.flip.base_url_production') ?? 'https://bigflip.id/api')
            : (config('payment.flip.base_url_sandbox') ?? 'https://bigflip.id/big_sandbox_api');
        $this->apiKey = config('payment.flip.api_key');
        $this->webhookToken = config('payment.flip.webhook_token');
    }

    public function useGateway(PaymentGateway $gateway): void
    {
        $isProduction = (bool) $gateway->getConfigValue(
            'is_production',
            config('payment.flip.is_production', false)
        );
        $this->baseUrl = $isProduction
            ? (config('payment.flip.base_url_production') ?? 'https://bigflip.id/api')
            : (config('payment.flip.base_url_sandbox') ?? 'https://bigflip.id/big_sandbox_api');
        $this->apiKey = $gateway->getConfigValue('api_key', config('payment.flip.api_key'));
        $this->webhookToken = $gateway->getConfigValue('webhook_token', config('payment.flip.webhook_token'));
    }

    /**
     * Cek apakah Flip sudah dikonfigurasi (API Key tersedia).
     */
    public function isConfigured(): bool
    {
        return filled($this->apiKey) && filled($this->webhookToken);
    }

    /**
     * Normalisasi kode bank untuk Flip API.
     */
    public static function normalizeBankCode(string $channelCode): string
    {
        $code = strtolower(trim($channelCode));

        return match ($code) {
            'cimb_niaga' => 'cimb',
            'bsm', 'syariah_mandiri' => 'bsi',
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
     * Mendukung retry & fallback otomatis agar tidak ada bank yang gagal.
     *
     * @param  Donasi          $donasi
     * @param  Pembayaran      $pembayaran
     * @param  PaymentChannel  $channel
     * @return array  Data pembayaran dari Flip
     * @throws \Exception jika Flip belum dikonfigurasi atau semua request gagal
     */
    public function createVirtualAccount(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Flip belum dikonfigurasi. Tambahkan FLIP_API_KEY di file .env.');
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

        // 1. Coba request dengan bank spesifik (step = 3)
        $attempts = [
            // Attempt 1: Tipe yang ditentukan (VA untuk 8 bank, bank_account untuk Muamalat)
            array_merge($basePayload, [
                'step' => 3,
                'sender_bank' => $bankCode,
                'sender_bank_type' => $senderBankType,
            ]),
        ];

        // Attempt 2: Jika tipe awal adalah VA tapi gagal (misal BCA di sandbox/tier tertentu), fallback ke bank_account
        if ($senderBankType === 'virtual_account') {
            $attempts[] = array_merge($basePayload, [
                'step' => 3,
                'sender_bank' => $bankCode,
                'sender_bank_type' => 'bank_account',
            ]);
        }

        // Attempt 3: Jika CIMB atau BSI memiliki kode alternatif di beberapa versi API Flip
        if ($bankCode === 'cimb') {
            $attempts[] = array_merge($basePayload, [
                'step' => 3,
                'sender_bank' => 'cimb_niaga',
                'sender_bank_type' => 'virtual_account',
            ]);
        } elseif ($bankCode === 'bsi') {
            $attempts[] = array_merge($basePayload, [
                'step' => 3,
                'sender_bank' => 'bsm',
                'sender_bank_type' => 'virtual_account',
            ]);
        }

        // Attempt 4: Fallback terakhir: Buat Bill umum (step = 2) tanpa mengunci bank spesifik
        $attempts[] = array_merge($basePayload, [
            'step' => 2,
        ]);

        $response = null;
        $successData = null;
        $lastErrorCode = null;

        foreach ($attempts as $index => $payload) {
            try {
                $response = $this->sendBillRequest($payload);

                if ($response->successful()) {
                    $successData = $response->json();
                    break;
                }

                $lastErrorCode = 'provider_http_' . $response->status();
                Log::warning('Flip attempt ' . ($index + 1) . ' failed', [
                    'bank' => $bankCode,
                    'status' => $response->status(),
                ]);
            } catch (\Exception $e) {
                $lastErrorCode = 'transport_error';
                Log::warning('Flip attempt failed', [
                    'attempt' => $index + 1,
                    'exception_type' => get_class($e),
                ]);
            }
        }

        if (!$successData) {
            Log::error('Semua percobaan pembuatan transaksi Flip gagal', [
                'bank' => $bankCode,
                'failure_code' => $lastErrorCode,
            ]);
            throw new \Exception('Gagal membuat transaksi Flip. Silakan coba lagi atau hubungi admin.');
        }

        return $this->processSuccessfulFlipResponse($successData, $pembayaran, $donasi, $channel, $bankCode);
    }

    /**
     * Kirim HTTP POST ke Flip API (dengan fallback endpoint jika 404).
     */
    protected function sendBillRequest(array $payload)
    {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->asForm()
            ->post($this->baseUrl . '/v2/pwf/bill', $payload);

        if ($response->status() === 404) {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->asForm()
                ->post($this->baseUrl . '/bill', $payload);
        }

        return $response;
    }

    /**
     * Proses parsing response sukses dari Flip dan simpan ke Pembayaran.
     */
    protected function processSuccessfulFlipResponse(
        array $data,
        Pembayaran $pembayaran,
        Donasi $donasi,
        PaymentChannel $channel,
        string $bankCode
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

        $billLinkId = $data['bill_link_id'] ?? $data['link_id'] ?? null;
        $paymentId = $data['id'] ?? $data['bill_payment_id'] ?? null;
        $status = $data['status'] ?? null;
        if ((!$billLinkId && !$paymentId) || !$status) {
            Log::warning('Flip webhook: required identifiers or status missing.');
            return false;
        }

        $pembayaran = Pembayaran::with(['donasi', 'paymentChannel.gateway'])
            ->where(function ($q) use ($billLinkId, $paymentId) {
                if ($billLinkId) {
                    $q->where('transaction_id', (string) $billLinkId);
                }
                if ($paymentId) {
                    $q->orWhere('transaction_id', (string) $paymentId);
                }
            })
            ->first();

        if (!$pembayaran) {
            $pembayaran = Pembayaran::with(['donasi', 'paymentChannel.gateway'])
                ->where(function ($query) use ($billLinkId, $paymentId) {
                    if ($billLinkId) {
                        $query->where('gateway_response->link_id', (string) $billLinkId)
                            ->orWhere('gateway_response->flip_id', (string) $billLinkId);
                    }
                    if ($paymentId) {
                        $query->orWhere('gateway_response->flip_id', (string) $paymentId);
                    }
                })
                ->first();
        }

        if (!$pembayaran || $pembayaran->paymentChannel?->gateway?->code !== 'flip') {
            return false;
        }
        $gateway = $pembayaran->paymentChannel->gateway;
        $this->useGateway($gateway);

        $expectedToken = $this->webhookToken;
        $receivedToken = $token ?: ($payload['token'] ?? $data['token'] ?? '');
        if (
            !filled($expectedToken) ||
            !filled($receivedToken) ||
            !hash_equals((string) $expectedToken, (string) $receivedToken)
        ) {
            Log::warning('Flip webhook rejected: invalid callback token.');
            return false;
        }

        $internalStatus = match (strtoupper((string) $status)) {
            'SUCCESSFUL', 'SETTLEMENT', 'PAID' => 'settlement',
            'FAILED', 'CANCELLED' => 'failed',
            'EXPIRED' => 'expired',
            'PENDING', 'PROCESSING' => 'pending',
            default => null,
        };
        if ($internalStatus === null) {
            return false;
        }

        $rawAmount = $data['amount'] ?? $data['bill_payment']['amount'] ?? null;
        $callbackAmount = PaymentAmount::parseIdr($rawAmount);
        $expectedAmount = PaymentAmount::parseIdr($pembayaran->donasi?->nominal);
        $savedUniqueCode = PaymentAmount::parseIdr($pembayaran->gateway_response['unique_code'] ?? 0);
        $amountMatches = $callbackAmount !== null && $expectedAmount !== null && (
            $callbackAmount === $expectedAmount ||
            $savedUniqueCode !== null && $savedUniqueCode > 0 &&
            $callbackAmount === $expectedAmount + $savedUniqueCode
        );

        if (
            $expectedAmount === null ||
            $internalStatus === 'settlement' && !$amountMatches ||
            $rawAmount !== null && !$amountMatches
        ) {
            Log::warning('Flip webhook rejected: amount mismatch.', [
                'order_id' => $pembayaran->order_id,
            ]);
            return false;
        }

        $attributes = [];
        if ($paymentId) {
            $attributes['transaction_id'] = (string) $paymentId;
        }

        return app(PaymentWebhookProcessor::class)->process(
            $pembayaran->id,
            'flip',
            $internalStatus,
            $data,
            function (Pembayaran $lockedPayment) use ($amountMatches, $callbackAmount, $savedUniqueCode): bool {
                $lockedAmount = PaymentAmount::parseIdr($lockedPayment->donasi?->nominal);

                return $amountMatches &&
                    $lockedAmount !== null &&
                    (
                        $callbackAmount === $lockedAmount ||
                        $savedUniqueCode !== null &&
                        $savedUniqueCode > 0 &&
                        $callbackAmount === $lockedAmount + $savedUniqueCode
                    );
            },
            $attributes
        );
    }
}
