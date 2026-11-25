<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentDriverInterface;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Services\FlipService;
use Illuminate\Http\Request;

class FlipDriver implements PaymentDriverInterface
{
    protected ?PaymentGateway $gateway = null;

    public function __construct(
        protected FlipService $service
    ) {}

    public function setGateway(PaymentGateway $gateway): self
    {
        $this->gateway = $gateway;
        return $this;
    }

    public function isConfigured(): bool
    {
        if ($this->gateway) {
            return $this->gateway->isConfigured();
        }
        return $this->service->isConfigured();
    }

    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        if ($channel->payment_type === 'instant') {
            return $this->service->createEwalletDirectApiV3($donasi, $pembayaran, $channel);
        }

        if ($channel->payment_type === 'va') {
            $vaData = $this->service->createVirtualAccount($donasi, $pembayaran, $channel);

            return [
                'type'           => 'virtual_account',
                'provider_type'  => 'flip_direct_api',
                'order_id'       => $pembayaran->order_id,
                'donasi_id'      => $donasi->id,
                'bank_name'      => $channel->name,
                'account_number' => $vaData['account_number'] ?? null,
                'account_name'   => $vaData['account_name'] ?? $channel->account_name ?? 'OrangBaik',
                'amount'         => $vaData['amount'] ?? $donasi->nominal,
                'expired_at'     => $vaData['expired_date'] ?? null,
                'payment_url'    => $vaData['payment_url'] ?? null,
                'redirect_url'   => route('donasi.bayar.instruksi', [
                    'pembayaran' => $pembayaran->id,
                    'token'      => $pembayaran->payment_token,
                ]),
            ];
        }

        throw new \RuntimeException('Tipe channel pembayaran ini tidak didukung oleh Flip.');
    }


    public function handleWebhook(Request $request): bool
    {
        $token   = $request->header('X-CALLBACK-TOKEN', '');
        $payload = $request->all();

        return $this->service->handleWebhook($token, $payload);
    }
}
