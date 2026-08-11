<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use App\Models\Donasi;
use App\Models\Pembayaran;
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
     * Membuat transaksi dan mengembalikan Snap Token
     */
    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran)
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

        try {
            return Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            throw new \Exception('Gagal membuat transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Menangani webhook dari Midtrans
     */
    /**
 * Menangani webhook dari Midtrans
 */
public function handleWebhook($payload)
{
    $orderId = $payload['order_id'] ?? null;
    $statusCode = $payload['status_code'] ?? null;
    $grossAmount = $payload['gross_amount'] ?? null;
    $signatureKey = $payload['signature_key'] ?? null;

    // Pastikan data penting tersedia
    if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
        Log::warning('Midtrans webhook: data tidak lengkap', [
            'payload' => $payload,
        ]);

        return false;
    }

    /*
     * =====================================================
     * 1. VERIFIKASI SIGNATURE MIDTRANS
     * =====================================================
     */

    $serverKey = config('midtrans.serverKey');

    $expectedSignature = hash(
        'sha512',
        $orderId . $statusCode . $grossAmount . $serverKey
    );

    if (!hash_equals($expectedSignature, $signatureKey)) {
        Log::warning('Midtrans webhook: signature tidak valid', [
            'order_id' => $orderId,
        ]);

        return false;
    }

    /*
     * =====================================================
     * 2. CARI DATA PEMBAYARAN
     * =====================================================
     */

    $pembayaran = Pembayaran::where('order_id', $orderId)->first();

    if (!$pembayaran) {
        Log::warning(
            'Midtrans webhook: pembayaran tidak ditemukan',
            [
                'order_id' => $orderId,
            ]
        );

        return false;
    }

    /*
     * =====================================================
     * 3. AMBIL DATA TRANSAKSI
     * =====================================================
     */

    $transactionStatus = $payload['transaction_status'] ?? null;
    $paymentType = $payload['payment_type'] ?? null;
    $transactionId = $payload['transaction_id'] ?? null;

    /*
     * =====================================================
     * 4. JANGAN BIARKAN STATUS SETTLEMENT MUNDUR
     * =====================================================
     */

    if ($pembayaran->transaction_status === 'settlement') {
        Log::info(
            'Midtrans webhook: transaksi sudah settlement',
            [
                'order_id' => $orderId,
            ]
        );

        return true;
    }

    /*
     * =====================================================
     * 5. UPDATE PEMBAYARAN
     * =====================================================
     */

    $pembayaran->payment_type = $paymentType;
    $pembayaran->transaction_id = $transactionId;
    $pembayaran->transaction_status = $transactionStatus;

    /*
     * =====================================================
     * 6. CATAT WAKTU PEMBAYARAN BERHASIL
     * =====================================================
     */

    if ($transactionStatus === 'settlement') {
        $pembayaran->paid_at = now();
    }

    $pembayaran->save();

    /*
     * =====================================================
     * 7. LOG
     * =====================================================
     */

    Log::info(
        'Midtrans webhook berhasil diproses',
        [
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'payment_type' => $paymentType,
            'transaction_id' => $transactionId,
        ]
    );

    return true;
}
}