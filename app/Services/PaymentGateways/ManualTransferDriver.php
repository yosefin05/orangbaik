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
        // Update data pembayaran untuk transfer manual
        $pembayaran->update([
            'payment_type'       => 'transfer',
            'transaction_status' => 'pending',
        ]);

        return [
            'type'         => 'manual_transfer',
            'order_id'     => $pembayaran->order_id,
            'donasi_id'    => $donasi->id,
            'redirect_url' => route('donasi.bayar.instruksi', ['pembayaran' => $pembayaran->id]),
        ];
    }

    public function handleWebhook(Request $request): bool
    {
        return true; // Manual transfer tidak menggunakan webhook
    }
}
