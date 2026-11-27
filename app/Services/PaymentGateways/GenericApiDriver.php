<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentDriverInterface;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use LogicException;

/**
 * Disabled placeholder for a future provider-specific adapter.
 *
 * A generic HTTP payment driver cannot safely infer a provider's authentication,
 * signature, amount, idempotency, or status-transition contract. Keep this class
 * fail-closed until a concrete provider implementation and security tests exist.
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
        return false;
    }

    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array
    {
        throw new LogicException(
            'Generic payment gateway is disabled. Register a provider-specific adapter with verified security controls.'
        );
    }

    public function handleWebhook(Request $request): bool
    {
        return false;
    }
}
