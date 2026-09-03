<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentDriverInterface;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GenericApiDriver — Driver fleksibel untuk payment gateway baru yang ditambahkan via Admin UI
 *
 * Menerima konfigurasi Endpoint URL, API Key / Secret, dan Callback URL dari database.
 */
class GenericApiDriver implements PaymentDriverInterface
{
    protected ?PaymentGateway $gateway = null;

    public function setGateway(PaymentGateway $gateway): self
    {
        $this->gateway = $gateway;
        return $this;
    }

    public function isConfigured(): bool
    {
        if (!$this->gateway) {
            return false;
        }
        return !empty($this->gateway->getConfigValue('api_key')) || !empty($this->gateway->getConfigValue('server_key'));
    }

    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        $endpointUrl = $this->gateway?->getConfigValue('endpoint_url');
        $apiKey      = $this->gateway?->getConfigValue('api_key') ?? $this->gateway?->getConfigValue('server_key');

        if (!$endpointUrl || !$apiKey) {
            // Jika gateway belum memiliki endpoint API lengkap, fallback ke halaman instruksi
            $pembayaran->update([
                'payment_type'       => $channel->payment_type ?? 'instant',
                'transaction_status' => 'pending',
            ]);

            return [
                'type'         => 'custom_gateway',
                'order_id'     => $pembayaran->order_id,
                'donasi_id'    => $donasi->id,
                'redirect_url' => route('donasi.bayar.instruksi', ['pembayaran' => $pembayaran->id]),
            ];
        }

        try {
            $payload = [
                'external_id'    => $pembayaran->order_id,
                'amount'         => (int) $donasi->nominal,
                'payer_name'     => $donasi->nama_donatur,
                'payer_email'    => $donasi->email ?? 'donatur@orangbaik.id',
                'payer_phone'    => $donasi->no_hp ?? '08123456789',
                'channel_code'   => $channel->channel_code,
                'description'    => 'Donasi: ' . ($donasi->campaign->judul ?? 'OrangBaik'),
                'success_url'    => route('donasi.status', 'sukses'),
                'callback_url'   => route('payment.gateway.webhook', ['gateway' => $this->gateway->code]),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post($endpointUrl, $payload);

            $data = $response->json() ?? [];

            $pembayaran->update([
                'transaction_id'   => $data['id'] ?? $data['reference'] ?? null,
                'gateway_response' => $data,
            ]);

            $checkoutUrl = $data['checkout_url'] ?? $data['payment_url'] ?? $data['invoice_url'] ?? null;

            return [
                'type'         => 'custom_gateway',
                'order_id'     => $pembayaran->order_id,
                'donasi_id'    => $donasi->id,
                'redirect_url' => $checkoutUrl ?? route('donasi.bayar.instruksi', ['pembayaran' => $pembayaran->id]),
            ];

        } catch (\Exception $e) {
            Log::error('GenericApiDriver createTransaction error', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function handleWebhook(Request $request): bool
    {
        $payload = $request->all();
        $orderId = $payload['external_id'] ?? $payload['order_id'] ?? $payload['reference_id'] ?? null;
        $status  = $payload['status'] ?? $payload['transaction_status'] ?? null;

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
            'success', 'settlement', 'paid', 'completed' => 'settlement',
            'failed', 'cancel', 'cancelled', 'deny'      => 'failed',
            'expired', 'expire'                          => 'expired',
            default                                      => 'pending',
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
