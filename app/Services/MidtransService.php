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
    public function handleWebhook($payload)
    {
        $notification = new Notification($payload);

        $orderId            = $notification->order_id;
        $transactionStatus  = $notification->transaction_status;
        $paymentType        = $notification->payment_type;
        $transactionId      = $notification->transaction_id;

        $pembayaran = Pembayaran::where('order_id', $orderId)->first();

        if (!$pembayaran) {
            Log::warning('Webhook: Pembayaran tidak ditemukan untuk order_id ' . $orderId);
            return false;
        }

        $pembayaran->payment_type       = $paymentType;
        $pembayaran->transaction_id     = $transactionId;
        $pembayaran->transaction_status = $transactionStatus;

        if ($transactionStatus == 'settlement') {
            $pembayaran->paid_at = now();
        }

        $pembayaran->save();

        Log::info('Webhook processed for order_id ' . $orderId . ' status: ' . $transactionStatus);
        return true;
    }
}