<?php

namespace App\Services;

use App\Contracts\PaymentDriverInterface;
use App\Models\PaymentGateway;
use App\Services\PaymentGateways\MidtransDriver;
use App\Services\PaymentGateways\FlipDriver;
use App\Services\PaymentGateways\ManualTransferDriver;
use App\Services\PaymentGateways\GenericApiDriver;
use InvalidArgumentException;

/**
 * PaymentGatewayManager — Manager modular untuk meresolve driver payment gateway secara dinamis.
 */
class PaymentGatewayManager
{
    /**
     * Cache driver instances yang sudah di-instantiate.
     */
    protected array $drivers = [];

    /**
     * Driver kustom yang didaftarkan secara runtime (jika ada).
     */
    protected array $customCreators = [];

    /**
     * Dapatkan driver berdasarkan kode gateway (string atau instance PaymentGateway).
     */
    public function driver(string|PaymentGateway $gateway): PaymentDriverInterface
    {
        $gatewayModel = is_string($gateway)
            ? PaymentGateway::where('code', $gateway)->first()
            : $gateway;

        $code = is_string($gateway) ? $gateway : $gateway->code;

        if (!isset($this->drivers[$code])) {
            $this->drivers[$code] = $this->createDriver($code, $gatewayModel);
        }

        if ($gatewayModel) {
            $this->drivers[$code]->setGateway($gatewayModel);
        }

        return $this->drivers[$code];
    }

    /**
     * Buat instance driver baru.
     */
    protected function createDriver(string $code, ?PaymentGateway $gatewayModel = null): PaymentDriverInterface
    {
        if (isset($this->customCreators[$code])) {
            return call_user_func($this->customCreators[$code], $gatewayModel);
        }

        return match ($code) {
            'midtrans' => app(MidtransDriver::class),
            'flip'     => app(FlipDriver::class),
            'manual'   => app(ManualTransferDriver::class),
            default    => app(GenericApiDriver::class),
        };
    }

    /**
     * Daftarkan custom driver creator baru secara dinamis.
     */
    public function extend(string $driver, \Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }
}
