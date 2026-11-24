<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Configure the SDK from the gateway attached to the selected payment channel.
     * Live mode is only allowed when the application itself runs in production.
     */
    protected function configureGateway(?PaymentGateway $gateway): void
    {
        if (!$gateway) {
            throw new \RuntimeException('Gateway Midtrans tidak ditemukan.');
        }

        $mode = $gateway->activeCredentialMode();
        $serverKey = $gateway->getCredentialForMode('server_key', $mode);
        $clientKey = $gateway->getCredentialForMode('client_key', $mode);
        $isProduction = $mode === 'production';

        if (blank($serverKey) || blank($clientKey) || blank($gateway->getCredentialForMode('signature_secret_key', $mode))) {
            throw new \RuntimeException('Kredensial Midtrans belum lengkap. Lengkapi Server Key dan Client Key.');
        }

        Config::$serverKey = $serverKey;
        Config::$clientKey = $clientKey;
        Config::$isProduction = $isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Membuat transaksi Midtrans dan mengembalikan Snap Token.
     * Jika payment channel memiliki channel_code, gunakan sebagai enabled_payments
     * agar donatur langsung diarahkan ke metode yang dipilih.
     */
    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, ?PaymentChannel $channel = null)
    {
        if (!$channel || $channel->gateway?->code !== 'midtrans') {
            throw new \RuntimeException('Channel pembayaran tidak terhubung ke gateway Midtrans.');
        }

        $this->configureGateway($channel->gateway);

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
            $pembayaran->update([
                'snap_token' => $snapToken,
                'gateway_response' => array_merge($pembayaran->gateway_response ?? [], [
                    'credential_mode' => $channel->gateway->activeCredentialMode(),
                ]),
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error', [
                'exception_class' => get_class($e),
                'order_id' => $pembayaran->order_id,
                'donasi_id' => $donasi->id,
            ]);
            throw new \Exception('Gagal membuat transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Cek apakah Midtrans sudah dikonfigurasi (Server Key tersedia).
     */
    public function isConfigured(?PaymentGateway $gateway = null): bool
    {
        if (!$gateway) {
            return false;
        }

        $mode = $gateway->activeCredentialMode();

        return filled($gateway->getCredentialForMode('server_key', $mode))
            && filled($gateway->getCredentialForMode('client_key', $mode))
            && filled($gateway->getCredentialForMode('signature_secret_key', $mode));
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
            Log::warning('Midtrans webhook: data tidak lengkap', ['payload_keys' => array_keys($payload)]);
            return false;
        }

        /*
         * =====================================================
         * 1. CARI TRANSAKSI DAN GATEWAY PEMILIKNYA
         * =====================================================
         */
        $pembayaran = Pembayaran::with(['donasi', 'paymentChannel.gateway'])
            ->where('order_id', $orderId)
            ->first();

        if (!$pembayaran || $pembayaran->paymentChannel?->gateway?->code !== 'midtrans') {
            Log::warning('Midtrans webhook: pembayaran tidak ditemukan atau bukan milik Midtrans', ['order_id' => $orderId]);
            return false;
        }

        /*
         * =====================================================
         * 2. VERIFIKASI SIGNATURE DENGAN SIGNATURE SECRET KEY GATEWAY TERKAIT
         * =====================================================
         */
        $gateway = $pembayaran->paymentChannel->gateway;
        $credentialMode = data_get($pembayaran->gateway_response ?? [], 'credential_mode')
            ?: $gateway->activeCredentialMode();
        $signatureSecretKey = $gateway->getCredentialForMode('signature_secret_key', $credentialMode);

        if (blank($signatureSecretKey)) {
            Log::critical('Midtrans webhook ditolak: Signature Secret Key gateway belum dikonfigurasi.');
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $signatureSecretKey);

        if (!hash_equals($expectedSignature, (string) $signatureKey)) {
            Log::warning('Midtrans webhook: signature tidak valid', ['order_id' => $orderId]);
            return false;
        }

        /*
         * =====================================================
         * 3. CEGAH ROLLBACK — STATUS SETTLEMENT TIDAK BOLEH MUNDUR
         * =====================================================
         */
        if (in_array($pembayaran->transaction_status, ['settlement', 'failed', 'expired', 'expire'], true)) {
            Log::info('Midtrans webhook: transaksi sudah berstatus terminal (idempotent skip)', ['order_id' => $orderId]);
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
            default      => null,
        };

        if ($internalStatus === null) {
            Log::warning('Midtrans webhook: status transaksi tidak dikenal', ['order_id' => $orderId, 'status' => $rawStatus]);
            return false;
        }

        /*
         * =====================================================
         * 4.5. VALIDASI INTEGRITAS AMOUNT
         * =====================================================
         */
        if ($internalStatus === 'settlement') {
            $expectedNominal = (int) ($pembayaran->donasi?->nominal ?? 0);
            $actualGrossAmount = (int) round((float) $grossAmount);

            if ($expectedNominal <= 0 || $actualGrossAmount !== $expectedNominal) {
                Log::error('Midtrans webhook: nominal tidak sesuai (Amount Integrity Mismatch)', [
                    'order_id'        => $orderId,
                    'expected_amount' => $expectedNominal,
                    'actual_amount'   => $actualGrossAmount,
                ]);
                return false;
            }
        }

        /*
         * =====================================================
         * 5. UPDATE PEMBAYARAN
         * =====================================================
         */
        $updateData = [
            'payment_type'       => $paymentType,
            'transaction_id'     => $transactionId,
            'transaction_status' => $internalStatus,
            'gateway_response'   => array_merge($pembayaran->gateway_response ?? [], $payload), // preserve credential mode and callback audit
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