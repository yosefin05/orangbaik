<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentDriverInterface;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Services\ManualTransferService;
use Illuminate\Http\Request;

class ManualTransferDriver implements PaymentDriverInterface
{
    protected ?PaymentGateway $gateway = null;

    public function __construct(
        protected ManualTransferService $service
    ) {}

    public function setGateway(PaymentGateway $gateway): self
    {
        $this->gateway = $gateway;
        return $this;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        $paymentDetails = [
            'name'           => $channel->name,
            'account_name'   => $channel->account_name,
            'account_number' => $channel->account_number,
            'instructions'   => $channel->instructions,
        ];

        $pembayaran->update([
            'payment_type'       => 'transfer',
            'transaction_status' => 'pending',
            'gateway_response'   => array_merge($pembayaran->gateway_response ?? [], [
                'payment_method' => $paymentDetails,
            ]),
        ]);

        return [
            'type'         => 'manual_transfer',
            'order_id'     => $pembayaran->order_id,
            'donasi_id'    => $donasi->id,
            'redirect_url' => route('donasi.bayar.instruksi', [
                'pembayaran' => $pembayaran->id,
                'token'      => $pembayaran->payment_token,
            ]),
        ];
    }

    public function handleWebhook(Request $request): bool
    {
        return true; // Manual transfer tidak menggunakan webhook
    }
}
