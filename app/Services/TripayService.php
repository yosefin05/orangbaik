<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    protected string $apiKey;
    protected string $privateKey;
    protected string $merchantCode;
    protected string $baseUrl;

    public function __construct()
    {
        $isProduction       = config('payment.tripay.is_production', false);
        $this->apiKey       = config('payment.tripay.api_key') ?? '';
        $this->privateKey   = config('payment.tripay.private_key') ?? '';
        $this->merchantCode = config('payment.tripay.merchant_code') ?? '';
        $this->baseUrl      = $isProduction
            ? config('payment.tripay.base_url_production')
            : config('payment.tripay.base_url_sandbox');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->privateKey);
    }

    /**
     * Buat transaksi Tripay (Closed Payment / ShopeePay, etc.)
     */
    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Tripay belum dikonfigurasi. Tambahkan TRIPAY_API_KEY di file .env.');
        }

        $payload = [
            'method'         => strtoupper($channel->channel_code),
            'merchant_ref'   => $pembayaran->order_id,
            'amount'         => (int) $donasi->nominal,
            'customer_name'  => $donasi->nama_donatur,
            'customer_email' => $donasi->email ?? 'donor@orangbaik.id',
            'customer_phone' => $donasi->no_hp ?? '08123456789',
            'order_items'    => [
                [
                    'sku'      => 'DONASI-' . $donasi->id,
                    'name'     => 'Donasi: ' . ($donasi->campaign->judul ?? 'Campaign OrangBaik'),
                    'price'    => (int) $donasi->nominal,
                    'quantity' => 1,
                ]
            ],
            'return_url'   => route('donasi.status', 'sukses'),
            'expired_time' => now()->addDay()->timestamp,
            'signature'    => hash_hmac('sha256', $this->merchantCode . $pembayaran->order_id . (int) $donasi->nominal, $this->privateKey),
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->post($this->baseUrl . '/transaction/create', $payload);

            if ($response->failed() || !$response->json('success')) {
                Log::error('Tripay createTransaction gagal', [
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'order_id' => $pembayaran->order_id,
                ]);
                throw new \Exception('Gagal membuat transaksi Tripay: ' . ($response->json('message') ?? 'Error'));
            }

            $data = $response->json('data');

            $pembayaran->update([
                'transaction_id'   => $data['reference'] ?? null,
                'gateway_response' => $data,
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::error('Tripay createTransaction exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Webhook handler Tripay
     */
    public function handleWebhook(string $signature, string $jsonPayload, array $data): bool
    {
        $callbackSignature = hash_hmac('sha256', $jsonPayload, $this->privateKey);

        if (!hash_equals($callbackSignature, $signature)) {
            Log::warning('Tripay webhook: signature tidak cocok');
            return false;
        }

        $orderId = $data['merchant_ref'] ?? null;
        $status  = $data['status'] ?? null;

        if (!$orderId || !$status) {
            return false;
        }

        $pembayaran = Pembayaran::where('order_id', $orderId)->first();
        if (!$pembayaran) {
            return false;
        }

        if ($pembayaran->transaction_status === 'settlement') {
            return true;
        }

        $internalStatus = match (strtoupper($status)) {
            'PAID'    => 'settlement',
            'FAILED', 'EXPIRED' => 'failed',
            default   => 'pending',
        };

        $update = [
            'transaction_status' => $internalStatus,
            'gateway_response'   => $data,
        ];
        if ($internalStatus === 'settlement') {
            $update['paid_at'] = now();
        }

        $pembayaran->update($update);
        return true;
    }
}
