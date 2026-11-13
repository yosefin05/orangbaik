<?php

namespace Tests\Unit;

use App\Models\Donasi;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Models\Pembayaran;
use App\Services\PaymentGateways\GenericApiDriver;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Facade;
use Mockery;
use PHPUnit\Framework\TestCase;

class PaymentGatewayCredentialTest extends TestCase
{
    private Container $app;
    private ?Container $originalContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalContainer = Container::getInstance();
        $this->app = new Container();
        Container::setInstance($this->app);
        Facade::setFacadeApplication($this->app);
        Facade::clearResolvedInstances();
        $this->app->instance('config', new Repository([]));
        Model::clearBootedModels();
        Model::setEventDispatcher(new Dispatcher($this->app));
        $this->app->instance('encrypter', new Encrypter(str_repeat('a', 32), 'AES-256-CBC'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance($this->originalContainer);
        Model::unsetEventDispatcher();
        Model::clearBootedModels();

        parent::tearDown();
    }

    public function test_legacy_credentials_remain_readable_but_are_hidden_and_encrypted_when_saved(): void
    {
        $gateway = new class extends PaymentGateway {
            public function dispatchSavingEvent(): void
            {
                $this->fireModelEvent('saving', false);
            }
        };
        $gateway->config = [
            'api_key' => 'legacy-api-secret',
            'webhook_token' => 'legacy-webhook-token',
            'endpoint_url' => 'https://api.payment-provider.example',
        ];

        self::assertSame('legacy-api-secret', $gateway->getConfigValue('api_key'));
        self::assertArrayNotHasKey('config', $gateway->toArray());

        $gateway->dispatchSavingEvent();
        $savedConfig = $gateway->config;

        self::assertStringStartsWith('encrypted:v1:', $savedConfig['api_key']);
        self::assertStringStartsWith('encrypted:v1:', $savedConfig['webhook_token']);
        self::assertSame(
            'legacy-api-secret',
            Crypt::decryptString(substr($savedConfig['api_key'], strlen('encrypted:v1:')))
        );
        self::assertSame(
            'https://api.payment-provider.example',
            $savedConfig['endpoint_url']
        );
    }

    public function test_generic_gateway_logs_exception_type_without_exception_message(): void
    {
        $logger = Mockery::mock();
        $logger->shouldReceive('error')
            ->once()
            ->with('GenericApiDriver createTransaction failed', [
                'exception_type' => \InvalidArgumentException::class,
            ]);
        $this->app->instance('log', $logger);

        $gateway = new PaymentGateway();
        $gateway->config = [
            'api_key' => 'do-not-log-api-key',
            'endpoint_url' => 'https://localhost/payments',
            'webhook_token' => 'do-not-log-webhook-token',
        ];

        $driver = new class extends GenericApiDriver {
            public function isConfigured(): bool
            {
                return true;
            }
        };
        $driver->setGateway($gateway);

        try {
            $driver->createTransaction(new Donasi(), new Pembayaran(), new PaymentChannel());
            self::fail('An unsafe endpoint should throw before making an HTTP request.');
        } catch (\InvalidArgumentException) {
            self::assertTrue(true);
        }
    }
}
