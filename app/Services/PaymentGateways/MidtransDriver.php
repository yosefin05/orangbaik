<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentDriverInterface;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class MidtransDriver implements PaymentDriverInterface
{
    protected ?PaymentGateway $gateway = null;

    public function __construct(
        protected MidtransService $service
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
        $snapToken = $this->service->createTransaction($donasi, $pembayaran, $channel);

        return [
            'type'       => 'midtrans',
            'snap_token' => $snapToken,
            'order_id'   => $pembayaran->order_id,
            'donasi_id'  => $donasi->id,
        ];
    }

    public function handleWebhook(Request $request): bool
    {
        return $this->service->handleWebhook($request->all());
    }
}
