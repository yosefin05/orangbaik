<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.serverKey');
        Config::$clientKey    = config('midtrans.clientKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized  = config('midtrans.isSanitized');
        Config::$is3ds        = config('midtrans.is3ds');
    }

    /**
     * Membuat transaksi Midtrans dan mengembalikan Snap Token.
     * Jika payment channel memiliki channel_code, gunakan sebagai enabled_payments
     * agar donatur langsung diarahkan ke metode yang dipilih.
     */
    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, ?PaymentChannel $channel = null)
    {
        $params = [
            'transaction_details' => [
                'order_id'     => $pembayaran->order_id,
                'gross_amount' => (int) $donasi->nominal,
            ],
            'customer_details' => [
                'first_name' => $donasi->nama_donatur,
                'email'      => $donasi->email ?? 'customer@example.com',
                'phone'      => $donasi->no_hp ?? '08123456789',
            ],
            'item_details' => [
                [
                    'id'       => 'donasi-' . $donasi->id,
                    'price'    => (int) $donasi->nominal,
                    'quantity' => 1,
                    'name'     => 'Donasi untuk ' . $donasi->campaign->judul,
                ]
            ],
        ];

        // Jika channel code tersedia, batasi metode pembayaran ke channel yang dipilih
        // Ini memastikan donatur langsung melihat metode yang mereka pilih (QRIS, GoPay, dll.)
        if ($channel && $channel->channel_code) {
            $params['enabled_payments'] = [$channel->channel_code];
        }

        try {
            $snapToken = Snap::getSnapToken($params);

            // Simpan snap token ke pembayaran
            $pembayaran->update(['snap_token' => $snapToken]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error', [
                'message'  => $e->getMessage(),
                'order_id' => $pembayaran->order_id,
                'donasi_id' => $donasi->id,
            ]);
            throw new \Exception('Gagal membuat transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Menangani webhook dari Midtrans.
     * Idempotent: jika transaksi sudah settlement, langsung return true.
     */
    public function handleWebhook($payload)
    {
        $orderId       = $payload['order_id'] ?? null;
        $statusCode    = $payload['status_code'] ?? null;
        $grossAmount   = $payload['gross_amount'] ?? null;
        $signatureKey  = $payload['signature_key'] ?? null;

        // Pastikan data penting tersedia
        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            Log::warning('Midtrans webhook: data tidak lengkap', ['payload' => $payload]);
            return false;
        }

        /*
         * =====================================================
         * 1. VERIFIKASI SIGNATURE MIDTRANS
         * =====================================================
         */
        $serverKey = config('midtrans.serverKey');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Midtrans webhook: signature tidak valid', ['order_id' => $orderId]);
            return false;
        }

        /*
         * =====================================================
         * 2. CARI DATA PEMBAYARAN
         * =====================================================
         */
        $pembayaran = Pembayaran::with('paymentChannel.gateway')
            ->where('order_id', $orderId)
            ->first();

        if (!$pembayaran) {
            Log::warning('Midtrans webhook: pembayaran tidak ditemukan', ['order_id' => $orderId]);
            return false;
        }

        /*
         * =====================================================
         * 3. CEGAH ROLLBACK — STATUS SETTLEMENT TIDAK BOLEH MUNDUR
         * =====================================================
         */
        if ($pembayaran->transaction_status === 'settlement') {
            Log::info('Midtrans webhook: transaksi sudah settlement (idempotent skip)', ['order_id' => $orderId]);
            return true;
        }

        /*
         * =====================================================
         * 4. AMBIL DATA TRANSAKSI DARI PAYLOAD & MAP KE STATUS INTERNAL
         * =====================================================
         */
        $rawStatus   = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        // Map status Midtrans ke 4 status internal: pending, settlement, failed, expired
        $internalStatus = match ($rawStatus) {
            'capture' => ($fraudStatus === 'accept' ? 'settlement' : 'pending'),
            'settlement' => 'settlement',
            'pending'    => 'pending',
            'deny', 'cancel' => 'failed',
            'expire'     => 'expired',
            default      => $rawStatus ?? 'pending',
        };

        /*
         * =====================================================
         * 5. UPDATE PEMBAYARAN
         * =====================================================
         */
        $updateData = [
            'payment_type'       => $paymentType,
            'transaction_id'     => $transactionId,
            'transaction_status' => $internalStatus,
            'gateway_response'   => $payload, // simpan raw response untuk audit
        ];

        if ($internalStatus === 'settlement') {
            $updateData['paid_at'] = now();
        }

        $pembayaran->update($updateData);

        /*
         * =====================================================
         * 6. LOG
         * =====================================================
         */
        Log::info('Midtrans webhook berhasil diproses', [
            'order_id'           => $orderId,
            'raw_status'         => $rawStatus,
            'internal_status'    => $internalStatus,
            'payment_type'       => $paymentType,
            'transaction_id'     => $transactionId,
        ]);

        return true;
    }
}