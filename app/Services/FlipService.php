<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * FlipService — Integrasi Resmi dengan Flip API (Big Flip / Acceptance PWF API)
 *
 * Mendukung pembuatan Virtual Account / Payment Link dan Webhook Callback.
 *
 * Konfigurasi .env:
 *     FLIP_API_KEY=your_api_key_here
 *     FLIP_IS_PRODUCTION=false
 *     FLIP_WEBHOOK_TOKEN=your_webhook_token_here
 *
 * Dokumentasi Flip: https://docs.flip.id/
 */
class FlipService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $isProduction  = config('payment.flip.is_production', false);
        $this->baseUrl = $isProduction
            ? (config('payment.flip.base_url_production') ?? 'https://bigflip.id/api')
            : (config('payment.flip.base_url_sandbox') ?? 'https://bigflip.id/big_sandbox_api');
        $this->apiKey  = config('payment.flip.api_key');
    }

    /**
     * Cek apakah Flip sudah dikonfigurasi (API Key tersedia).
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Buat Virtual Account / Bill Payment via Flip API.
     *
     * @param  Donasi          $donasi
     * @param  Pembayaran      $pembayaran
     * @param  PaymentChannel  $channel
     * @return array  Data VA dari Flip
     * @throws \Exception jika Flip belum dikonfigurasi atau request gagal
     */
    public function createVirtualAccount(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Flip belum dikonfigurasi. Tambahkan FLIP_API_KEY di file .env.');
        }

        $bankCode = strtolower($channel->channel_code);
        $title    = 'Donasi: ' . Str::limit($donasi->campaign->judul ?? 'OrangBaik.id', 35);

        // Payload untuk Flip Accept (PWF / Bill API)
        $payload = [
            'title'                    => $title,
            'type'                     => 'SINGLE',
            'amount'                   => (int) $donasi->nominal,
            'expired_date'             => now()->addDays(1)->format('Y-m-d H:i'),
            'redirect_url'             => route('donasi.bayar.instruksi', ['pembayaran' => $pembayaran->id]),
            'is_address_required'      => 0,
            'is_phone_number_required' => 0,
            'step'                     => 3,
            'sender_name'              => $donasi->nama_donatur ?: 'Hamba Allah',
            'sender_email'             => $donasi->email ?? 'donatur@orangbaik.id',
            'sender_phone_number'      => $donasi->no_hp ?? '08123456789',
            'sender_bank'              => $bankCode,
            'sender_bank_type'         => 'virtual_account',
        ];

        try {
            // Flip API menggunakan Basic Auth dengan apiKey sebagai username (password dikosongkan)
            $response = Http::withBasicAuth($this->apiKey, '')
                ->asForm()
                ->post($this->baseUrl . '/v2/pwf/bill', $payload);

            // Jika /v2/pwf/bill 404 (beberapa sandbox Flip menggunakan /disbursement atau /bill langsung)
            if ($response->status() === 404) {
                $response = Http::withBasicAuth($this->apiKey, '')
                    ->asForm()
                    ->post($this->baseUrl . '/bill', $payload);
            }

            if ($response->failed()) {
                Log::error('Flip createVirtualAccount gagal', [
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'order_id' => $pembayaran->order_id,
                ]);
                throw new \Exception('Gagal membuat Virtual Account Flip: ' . ($response->json('message') ?? $response->body()));
            }

            $data = $response->json();

            // Ekstrak nomor Virtual Account dari response Flip
            $accountNumber = $data['bill_payment']['receiver_bank_account']['account_number']
                ?? $data['account_number']
                ?? $data['virtual_account_number']
                ?? null;

            $flipId = $data['link_id'] ?? $data['id'] ?? null;

            // Simpan transaction_id dari Flip dan gateway response
            $pembayaran->update([
                'transaction_id'   => (string) $flipId,
                'gateway_response' => array_merge($data, [
                    'account_number' => $accountNumber,
                    'link_url'       => $data['link_url'] ?? null,
                    'payment_url'    => $data['payment_url'] ?? null,
                ]),
            ]);

            Log::info('Flip VA created successfully', [
                'order_id'       => $pembayaran->order_id,
                'flip_id'        => $flipId,
                'bank_code'      => $bankCode,
                'account_number' => $accountNumber,
            ]);

            return [
                'id'             => $flipId,
                'account_number' => $accountNumber,
                'bank_code'      => $bankCode,
                'link_url'       => $data['link_url'] ?? null,
                'payment_url'    => $data['payment_url'] ?? null,
                'expired_date'   => $data['expired_date'] ?? null,
                'amount'         => (int) $donasi->nominal,
            ];

        } catch (\Exception $e) {
            Log::error('Flip createVirtualAccount exception', [
                'message'  => $e->getMessage(),
                'order_id' => $pembayaran->order_id,
            ]);
            throw $e;
        }
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
         * 1. VERIFIKASI TOKEN
         * =====================================================
         */
        $expectedToken = config('payment.flip.webhook_token');

        if (!empty($expectedToken)) {
            $receivedToken = !empty($token) ? $token : ($payload['token'] ?? '');
            if (!hash_equals($expectedToken, (string) $receivedToken)) {
                Log::warning('Flip webhook: token tidak valid', [
                    'expected' => substr($expectedToken, 0, 4) . '***',
                ]);
                return false;
            }
        }

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

        $flipId = $data['id'] ?? $data['link_id'] ?? $data['bill_link_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$flipId || !$status) {
            Log::warning('Flip webhook: data tidak lengkap', ['payload' => $payload]);
            return false;
        }

        /*
         * =====================================================
         * 3. CARI PEMBAYARAN BERDASARKAN TRANSACTION_ID (FLIP ID)
         * =====================================================
         */
        $pembayaran = Pembayaran::with('donasi')
            ->where('transaction_id', (string) $flipId)
            ->first();

        if (!$pembayaran) {
            Log::warning('Flip webhook: pembayaran tidak ditemukan', ['flip_id' => $flipId]);
            return false;
        }

        /*
         * =====================================================
         * 4. CEGAH ROLLBACK STATUS (IDEMPOTENT)
         * =====================================================
         */
        if ($pembayaran->transaction_status === 'settlement') {
            Log::info('Flip webhook: transaksi sudah settlement (idempotent skip)', ['flip_id' => $flipId]);
            return true;
        }

        /*
         * =====================================================
         * 5. MAP STATUS FLIP → STATUS INTERNAL (pending, settlement, failed, expired)
         * =====================================================
         */
        $internalStatus = match (strtoupper($status)) {
            'SUCCESSFUL', 'SETTLEMENT', 'PAID' => 'settlement',
            'FAILED', 'CANCELLED'              => 'failed',
            'EXPIRED'                          => 'expired',
            default                            => 'pending',
        };

        $updateData = [
            'transaction_status' => $internalStatus,
            'gateway_response'   => $data,
        ];

        if ($internalStatus === 'settlement') {
            $updateData['paid_at'] = now();
        }

        $pembayaran->update($updateData);

        Log::info('Flip webhook berhasil diproses', [
            'flip_id'         => $flipId,
            'flip_status'     => $status,
            'internal_status' => $internalStatus,
            'order_id'        => $pembayaran->order_id,
        ]);

        return true;
    }
}
