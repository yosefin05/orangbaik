<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpaymuService
{
    protected string $apiKey;
    protected string $va;
    protected string $baseUrl;

    public function __construct()
    {
        $isProduction  = config('payment.ipaymu.is_production', false);
        $this->apiKey  = config('payment.ipaymu.api_key') ?? '';
        $this->va      = config('payment.ipaymu.va') ?? '';
        $this->baseUrl = $isProduction
            ? config('payment.ipaymu.base_url_production')
            : config('payment.ipaymu.base_url_sandbox');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->va);
    }

    /**
     * Buat transaksi Direct Payment / VA via iPaymu
     */
    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('iPaymu belum dikonfigurasi. Tambahkan IPAYMU_API_KEY di file .env.');
        }

        $body = [
            'name'         => $donasi->nama_donatur,
            'phone'        => $donasi->no_hp ?? '08123456789',
            'email'        => $donasi->email ?? 'donor@orangbaik.id',
            'amount'       => (int) $donasi->nominal,
            'notifyUrl'    => route('payment.ipaymu.webhook'),
            'comments'     => 'Donasi: ' . ($donasi->campaign->judul ?? 'Campaign OrangBaik'),
            'referenceId'  => $pembayaran->order_id,
            'paymentMethod'=> $channel->payment_type === 'va' ? 'va' : 'cstore',
            'paymentChannel' => strtolower($channel->channel_code),
        ];

        $jsonBody  = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', 'POST:' . $this->va . ':' . $jsonBody . ':' . $this->apiKey, $this->apiKey);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'va'           => $this->va,
                'signature'    => $signature,
                'timestamp'    => now()->format('YmdHis'),
            ])->post($this->baseUrl . '/payment/direct', $body);

            if ($response->failed() || $response->json('Status') != 200) {
                Log::error('iPaymu createTransaction gagal', [
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'order_id' => $pembayaran->order_id,
                ]);
                throw new \Exception('Gagal membuat transaksi iPaymu: ' . ($response->json('Message') ?? 'Error'));
            }

            $data = $response->json('Data');

            $pembayaran->update([
                'transaction_id'   => $data['TransactionId'] ?? null,
                'gateway_response' => $data,
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::error('iPaymu createTransaction exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Webhook callback handler iPaymu
     */
    public function handleWebhook(array $payload): bool
    {
        $orderId = $payload['reference_id'] ?? null;
        $status  = $payload['status'] ?? null;

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

        $internalStatus = match (strtolower($status)) {
            'berhasil', 'success', 'paid' => 'settlement',
            'gagal', 'failed', 'expired'  => 'failed',
            default                       => 'pending',
        };

        $update = [
            'transaction_status' => $internalStatus,
            'gateway_response'   => $payload,
        ];
        if ($internalStatus === 'settlement') {
            $update['paid_at'] = now();
        }

        $pembayaran->update($update);
        return true;
    }
}
