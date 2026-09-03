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
        $vaData = $this->service->createVirtualAccount($donasi, $pembayaran, $channel);

        return [
            'type'           => 'virtual_account',
            'order_id'       => $pembayaran->order_id,
            'donasi_id'      => $donasi->id,
            'bank_name'      => $channel->name,
            'account_number' => $vaData['account_number'] ?? null,
            'account_name'   => $channel->account_name ?? 'OrangBaik',
            'amount'         => $donasi->nominal,
            'expired_at'     => $vaData['expired_date'] ?? null,
            'redirect_url'   => route('donasi.bayar.instruksi', ['pembayaran' => $pembayaran->id]),
        ];
    }

    public function handleWebhook(Request $request): bool
    {
        $token   = $request->header('X-CALLBACK-TOKEN', '');
        $payload = $request->all();

        return $this->service->handleWebhook($token, $payload);
    }
}
