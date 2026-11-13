<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentDriverInterface;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Services\PaymentWebhookProcessor;
use App\Services\PaymentGateways\PublicGatewayEndpoint;
use App\Support\PaymentAmount;
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
        $apiKey = $this->gateway->getConfigValue('api_key') ?: $this->gateway->getConfigValue('server_key');
        $endpoint = $this->gateway->getConfigValue('endpoint_url');

        return filled($apiKey)
            && filled($endpoint)
            && PublicGatewayEndpoint::isSafeUrl((string) $endpoint)
            && filled($this->gateway->getConfigValue('webhook_token'));
    }

    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        $endpointUrl = $this->gateway?->getConfigValue('endpoint_url');
        $apiKey      = $this->gateway?->getConfigValue('api_key') ?: $this->gateway?->getConfigValue('server_key');

        if (!$this->isConfigured() || !$endpointUrl || !$apiKey) {
            throw new \RuntimeException('Gateway kustom belum memiliki endpoint, API key, dan token webhook yang lengkap.');
        }

        try {
            $curlOptions = PublicGatewayEndpoint::curlResolveOption($endpointUrl);
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

            $response = Http::timeout(20)
                ->connectTimeout(5)
                ->withOptions([
                    'allow_redirects' => false,
                    'proxy' => '',
                    'curl' => $curlOptions,
                ])
                ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post($endpointUrl, $payload);
            $response->throw();

            $data = $response->json() ?? [];
            $checkoutUrl = $data['checkout_url'] ?? $data['payment_url'] ?? $data['invoice_url'] ?? null;
            if (!is_string($checkoutUrl)) {
                throw new \UnexpectedValueException('Gateway kustom tidak mengembalikan URL pembayaran yang valid.');
            }
            PublicGatewayEndpoint::assertSafeUrl($checkoutUrl);

            $pembayaran->update([
                'transaction_id'   => $data['id'] ?? $data['reference'] ?? null,
                'gateway_response' => $data,
            ]);

            return [
                'type'         => 'custom_gateway',
                'order_id'     => $pembayaran->order_id,
                'donasi_id'    => $donasi->id,
                'redirect_url' => $checkoutUrl,
            ];

        } catch (\Exception $e) {
            Log::error('GenericApiDriver createTransaction failed', [
                'exception_type' => get_class($e),
            ]);
            throw $e;
        }
    }

    public function handleWebhook(Request $request): bool
    {
        $expectedToken = $this->gateway?->getConfigValue('webhook_token');
        $receivedToken = $request->header('X-CALLBACK-TOKEN');
        if (!$receivedToken && preg_match('/^Bearer\\s+(.+)$/i', (string) $request->header('Authorization'), $matches)) {
            $receivedToken = $matches[1];
        }
        if (!$expectedToken || !$receivedToken || !hash_equals((string) $expectedToken, (string) $receivedToken)) {
            Log::warning('Generic payment webhook rejected: invalid callback token.');
            return false;
        }

        $payload = $request->all();
        $orderId = $payload['external_id'] ?? $payload['order_id'] ?? $payload['reference_id'] ?? null;
        $status  = $payload['status'] ?? $payload['transaction_status'] ?? null;

        if (!$orderId || !$status) {
            return false;
        }

        $pembayaran = Pembayaran::with(['donasi', 'paymentChannel.gateway'])
            ->where('order_id', $orderId)
            ->first();
        if (!$pembayaran || $pembayaran->paymentChannel?->gateway?->code !== $this->gateway?->code) {
            return false;
        }

        $callbackReference = $payload['transaction_id'] ?? $payload['reference'] ?? null;
        if (
            $callbackReference !== null &&
            $pembayaran->transaction_id !== null &&
            !hash_equals((string) $pembayaran->transaction_id, (string) $callbackReference)
        ) {
            return false;
        }

        $internalStatus = match (strtolower((string) $status)) {
            'success', 'settlement', 'paid', 'completed' => 'settlement',
            'failed', 'cancel', 'cancelled', 'deny'      => 'failed',
            'expired', 'expire'                          => 'expired',
            'pending', 'processing', 'waiting'           => 'pending',
            default                                      => null,
        };
        if ($internalStatus === null) {
            return false;
        }

        $callbackAmount = PaymentAmount::parseIdr($payload['amount'] ?? null);
        $providedAmount = array_key_exists('amount', $payload);
        $expectedAmount = PaymentAmount::parseIdr($pembayaran->donasi?->nominal);

        if (
            $expectedAmount === null ||
            $providedAmount && ($callbackAmount === null || $callbackAmount !== $expectedAmount) ||
            $internalStatus === 'settlement' && $callbackAmount !== $expectedAmount
        ) {
            return false;
        }

        $attributes = [];
        if (isset($payload['transaction_id'])) {
            $attributes['transaction_id'] = (string) $payload['transaction_id'];
        } elseif (isset($payload['reference'])) {
            $attributes['transaction_id'] = (string) $payload['reference'];
        }

        return app(PaymentWebhookProcessor::class)->process(
            $pembayaran->id,
            (string) $this->gateway?->code,
            $internalStatus,
            $payload,
            fn (Pembayaran $lockedPayment): bool =>
                $callbackAmount !== null &&
                $callbackAmount === PaymentAmount::parseIdr($lockedPayment->donasi?->nominal),
            $attributes
        );
    }
}
