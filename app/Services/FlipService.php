<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FlipService — Integrasi dengan Flip untuk Virtual Account
 *
 * CATATAN: Flip API Key belum tersedia saat implementasi ini dibuat.
 * Service ini sudah disiapkan sebagai skeleton yang siap diaktifkan
 * setelah API Key Flip dikonfigurasi di .env:
 *
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
            ? config('payment.flip.base_url_production')
            : config('payment.flip.base_url_sandbox');
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
     * Buat Virtual Account untuk pembayaran donasi.
     *
     * @param  Donasi          $donasi
     * @param  Pembayaran      $pembayaran
     * @param  PaymentChannel  $channel
     * @return array  Data VA dari Flip { bank_code, account_number, amount, ... }
     * @throws \Exception jika Flip belum dikonfigurasi atau request gagal
     */
    public function createVirtualAccount(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Flip belum dikonfigurasi. Tambahkan FLIP_API_KEY di file .env.');
        }

        $payload = [
            'bank_code'  => strtoupper($channel->channel_code),
            'name'       => $donasi->nama_donatur,
            'amount'     => (int) $donasi->nominal,
            // Flip menggunakan expired_date sebagai unix timestamp
            'expired_date' => now()->addDay()->timestamp,
        ];

        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post($this->baseUrl . '/disbursement', $payload);

            if ($response->failed()) {
                Log::error('Flip createVirtualAccount gagal', [
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'order_id' => $pembayaran->order_id,
                ]);
                throw new \Exception('Gagal membuat Virtual Account. Silakan coba lagi.');
            }

            $data = $response->json();

            // Simpan transaction_id dari Flip dan gateway response
            $pembayaran->update([
                'transaction_id'   => $data['id'] ?? null,
                'gateway_response' => $data,
            ]);

            Log::info('Flip VA created', [
                'order_id'    => $pembayaran->order_id,
                'flip_id'     => $data['id'] ?? null,
                'bank_code'   => $channel->channel_code,
                'account_num' => $data['account_number'] ?? null,
            ]);

            return $data;

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
     * Flip mengirim POST request dengan header X-CALLBACK-TOKEN untuk validasi.
     *
     * Dokumentasi webhook Flip:
     * https://docs.flip.id/
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

        if (empty($expectedToken) || !hash_equals($expectedToken, $token)) {
            Log::warning('Flip webhook: token tidak valid');
            return false;
        }

        /*
         * =====================================================
         * 2. AMBIL DATA DARI PAYLOAD
         * Flip mengirim data dalam format form-encoded.
         * Field penting: id, bill_link_id, payment_id, amount, status
         * =====================================================
         */
        $flipId = $payload['id'] ?? null;
        $status = $payload['status'] ?? null;
        $amount = $payload['amount'] ?? null;

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
         * 4. CEGAH ROLLBACK STATUS
         * =====================================================
         */
        if ($pembayaran->transaction_status === 'settlement') {
            Log::info('Flip webhook: transaksi sudah settlement (idempotent skip)', ['flip_id' => $flipId]);
            return true;
        }

        /*
         * =====================================================
         * 5. MAP STATUS FLIP → STATUS INTERNAL
         * Flip status: PENDING, SUCCESSFUL, FAILED, CANCELLED
         * =====================================================
         */
        $internalStatus = match (strtoupper($status)) {
            'SUCCESSFUL' => 'settlement',
            'FAILED'     => 'failed',
            'CANCELLED'  => 'failed',
            default      => 'pending',
        };

        $updateData = [
            'transaction_status' => $internalStatus,
            'gateway_response'   => $payload,
        ];

        if ($internalStatus === 'settlement') {
            $updateData['paid_at'] = now();
        }

        $pembayaran->update($updateData);

        Log::info('Flip webhook berhasil diproses', [
            'flip_id'         => $flipId,
            'flip_status'     => $status,
            'internal_status' => $internalStatus,
        ]);

        return true;
    }
}
