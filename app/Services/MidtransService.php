<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Services\PaymentWebhookProcessor;
use App\Support\PaymentAmount;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected ?string $serverKey = null;
    protected ?string $clientKey = null;

    public function __construct()
    {
        $this->serverKey = config('midtrans.serverKey') ?: config('payment.midtrans.server_key');
        $this->clientKey = config('midtrans.clientKey') ?: config('payment.midtrans.client_key');
        $this->applySdkConfig();
    }

    public function useGateway(PaymentGateway $gateway): void
    {
        $this->serverKey = $gateway->getConfigValue(
            'server_key',
            config('midtrans.serverKey') ?: config('payment.midtrans.server_key')
        );
        $this->clientKey = $gateway->getConfigValue(
            'client_key',
            config('midtrans.clientKey') ?: config('payment.midtrans.client_key')
        );
        $this->applySdkConfig((bool) $gateway->getConfigValue(
            'is_production',
            config('midtrans.isProduction') ?? config('payment.midtrans.is_production', false)
        ));
    }

    private function applySdkConfig(?bool $isProduction = null): void
    {
        Config::$serverKey    = $this->serverKey;
        Config::$clientKey    = $this->clientKey;
        Config::$isProduction = $isProduction ?? config('midtrans.isProduction') ?? config('payment.midtrans.is_production', false);
        Config::$isSanitized  = config('midtrans.isSanitized') ?? config('payment.midtrans.is_sanitized', true);
        Config::$is3ds        = config('midtrans.is3ds') ?? config('payment.midtrans.is_3ds', true);
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
                'exception_type' => get_class($e),
            ]);
            throw new \Exception('Gagal membuat transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Cek apakah Midtrans sudah dikonfigurasi (Server Key tersedia).
     */
    public function isConfigured(): bool
    {
        return filled($this->serverKey) && filled($this->clientKey);
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
        if (
            !is_string($orderId) || $orderId === '' ||
            !is_string($statusCode) || $statusCode === '' ||
            !is_string($grossAmount) || $grossAmount === '' ||
            !is_string($signatureKey) || $signatureKey === ''
        ) {
            Log::warning('Midtrans webhook: data tidak lengkap');
            return false;
        }

        $pembayaran = Pembayaran::with('paymentChannel.gateway')
            ->where('order_id', $orderId)
            ->first();

        if (!$pembayaran || $pembayaran->paymentChannel?->gateway?->code !== 'midtrans') {
            Log::warning('Midtrans webhook: pembayaran tidak ditemukan', ['order_id' => $orderId]);
            return false;
        }

        $gateway = $pembayaran->paymentChannel?->gateway;
        $serverKey = $gateway->getConfigValue('server_key', $this->serverKey);
        if (empty($serverKey)) {
            Log::critical('Midtrans webhook ditolak: MIDTRANS_SERVER_KEY belum dikonfigurasi (fail-closed).');
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Midtrans webhook: signature tidak valid', ['order_id' => $orderId]);
            return false;
        }

        $pembayaran->load('donasi');

        $rawStatus   = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $internalStatus = match (strtolower((string) $rawStatus)) {
            'capture' => match (strtolower((string) $fraudStatus)) {
                'accept' => 'settlement',
                'deny' => 'failed',
                default => 'pending',
            },
            'settlement' => 'settlement',
            'pending' => 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire', 'expired' => 'expired',
            default => null,
        };
        if ($internalStatus === null) {
            return false;
        }

        $actualAmount = PaymentAmount::parseIdr($grossAmount);
        $expectedAmount = PaymentAmount::parseIdr($pembayaran->donasi?->nominal);
        if ($actualAmount === null || $expectedAmount === null || $actualAmount !== $expectedAmount) {
            Log::warning('Midtrans webhook rejected: amount mismatch.', ['order_id' => $orderId]);
            return false;
        }

        $attributes = [];
        if (isset($payload['payment_type'])) {
            $attributes['payment_type'] = $payload['payment_type'];
        }
        if (isset($payload['transaction_id'])) {
            $attributes['transaction_id'] = (string) $payload['transaction_id'];
        }

        return app(PaymentWebhookProcessor::class)->process(
            $pembayaran->id,
            'midtrans',
            $internalStatus,
            $payload,
            fn (Pembayaran $lockedPayment): bool =>
                $actualAmount === PaymentAmount::parseIdr($lockedPayment->donasi?->nominal),
            $attributes
        );
    }
}