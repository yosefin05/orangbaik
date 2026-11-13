<?php

namespace Tests\Unit;

use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Services\PaymentGateways\PaymentGatewayConnectivityVerifier;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Encryption\Encrypter;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\TestCase;

class PaymentGatewayAdminTest extends TestCase
{
    private Container $app;
    private Factory $http;
    private ?Container $originalContainer;
    private $originalConnectionResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnectionResolver = Model::getConnectionResolver();
        $this->originalContainer = Container::getInstance();
        $this->app = new Container();
        Container::setInstance($this->app);
        Facade::setFacadeApplication($this->app);
        Facade::clearResolvedInstances();
        $this->app->instance('config', new Repository([]));
        Model::clearBootedModels();
        Model::setEventDispatcher(new Dispatcher($this->app));
        $this->app->instance('encrypter', new Encrypter(str_repeat('a', 32), 'AES-256-CBC'));
        $this->http = new Factory();
        $this->http->preventStrayRequests();
        $this->app->instance(Factory::class, $this->http);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance($this->originalContainer);
        Model::unsetEventDispatcher();
        Model::clearBootedModels();
        if ($this->originalConnectionResolver) {
            Model::setConnectionResolver($this->originalConnectionResolver);
        } else {
            Model::unsetConnectionResolver();
        }

        parent::tearDown();
    }

    public function test_channel_account_configuration_fields_are_fillable_and_manual_availability_is_fail_closed(): void
    {
        $channel = new PaymentChannel([
            'name' => 'Bank Syariah Indonesia',
            'channel_code' => 'bsi',
            'payment_type' => 'transfer',
            'account_number' => '3311131318',
            'account_name' => 'Orang Baik',
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $gateway = new PaymentGateway(['code' => 'manual', 'is_active' => true]);
        $channel->setRelation('gateway', $gateway);

        self::assertSame('3311131318', $channel->account_number);
        self::assertSame('Orang Baik', $channel->account_name);
        self::assertSame(3, $channel->sort_order);
        self::assertTrue($channel->isAvailable());

        $channel->is_active = false;
        self::assertFalse($channel->isAvailable());
    }

    public function test_channel_account_instructions_active_filter_and_sort_order_persist_in_isolated_sqlite(): void
    {
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $capsule->schema()->create('payment_gateways', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('driver')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('config')->nullable();
            $table->timestamps();
        });
        $capsule->schema()->create('payment_channels', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('payment_gateway_id');
            $table->string('name');
            $table->string('channel_code');
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('payment_type');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
        $capsule->schema()->create('pembayaran', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('payment_channel_id');
            $table->string('transaction_status')->default('pending');
        });

        $gateway = PaymentGateway::create([
            'name' => 'Manual',
            'code' => 'manual',
            'driver' => 'manual',
            'is_active' => true,
        ]);
        $channel = PaymentChannel::create([
            'payment_gateway_id' => $gateway->id,
            'name' => 'Bank Syariah Indonesia',
            'channel_code' => 'bsi',
            'account_number' => '3311131318',
            'account_name' => 'Orang Baik',
            'payment_type' => 'transfer',
            'sort_order' => 4,
            'is_active' => true,
        ]);
        $inactive = PaymentChannel::create([
            'payment_gateway_id' => $gateway->id,
            'name' => 'Nonaktif',
            'channel_code' => 'inactive',
            'account_number' => '123',
            'account_name' => 'Orang Baik',
            'payment_type' => 'transfer',
            'sort_order' => 5,
            'is_active' => false,
        ]);
        $gateway->setChannelInstructions($channel, 'Transfer sesuai nominal donasi.');

        $persisted = PaymentChannel::findOrFail($channel->id);
        self::assertSame('3311131318', $persisted->account_number);
        self::assertSame('Orang Baik', $persisted->account_name);
        self::assertSame(4, $persisted->sort_order);
        self::assertTrue($persisted->is_active);
        self::assertSame('Transfer sesuai nominal donasi.', $persisted->instructions);
        self::assertSame([$channel->id], PaymentChannel::active()->pluck('id')->all());
        self::assertNotContains($inactive->id, PaymentChannel::active()->pluck('id')->all());

        $capsule->getConnection()->table('pembayaran')->insert([
            'payment_channel_id' => $channel->id,
            'transaction_status' => 'pending',
        ]);
        self::assertTrue($gateway->hasUnresolvedTransactions());
        $capsule->getConnection()->table('pembayaran')->update([
            'transaction_status' => 'settlement',
        ]);
        self::assertFalse($gateway->hasUnresolvedTransactions());
    }

    public function test_gateway_secrets_are_hidden_and_gateway_requires_configuration_and_verification(): void
    {
        $gateway = $this->gateway('generic', [
            'api_key' => 'generic-secret',
            'webhook_token' => 'webhook-secret',
            'endpoint_url' => 'https://api.example.test/v1/payments',
            'verification_url' => 'https://api.example.test/health',
        ]);

        self::assertArrayNotHasKey('config', $gateway->toArray());
        self::assertTrue($gateway->isConfigured());
        self::assertFalse($gateway->isOperationallyVerified());

        $config = $gateway->config;
        $config['connectivity_verified_at'] = '2026-11-13T00:00:00+00:00';
        $gateway->config = $config;
        self::assertTrue($gateway->isOperationallyVerified());
    }

    public function test_missing_encryption_key_is_detected_without_exposing_a_key_value(): void
    {
        $encrypter = $this->app->make('encrypter');
        $this->app->forgetInstance('encrypter');
        Facade::clearResolvedInstances();

        self::assertFalse(PaymentGateway::encryptionKeyAvailable());
        self::assertFalse(PaymentGateway::canEncryptSensitiveConfig(['api_key' => 'secret']));

        $this->app->instance('encrypter', $encrypter);
        Facade::clearResolvedInstances();
    }

    public function test_changing_gateway_credentials_invalidates_verification_and_deactivates_gateway(): void
    {
        $originalConfig = PaymentGateway::encryptSensitiveConfig([
            'api_key' => 'previous-secret',
            'webhook_token' => 'previous-webhook',
            'endpoint_url' => 'https://api.example.test/v1/payments',
            'verification_url' => 'https://api.example.test/health',
            'connectivity_verified_at' => '2026-11-13T00:00:00+00:00',
        ]);
        $gateway = new class extends PaymentGateway {
            public function dispatchSavingEvent(): void
            {
                $this->fireModelEvent('saving', false);
            }
        };
        $gateway->setRawAttributes([
            'code' => 'generic',
            'driver' => 'generic',
            'is_active' => true,
            'config' => json_encode($originalConfig),
        ], true);
        $changedConfig = $originalConfig;
        $changedConfig['api_key'] = 'new-secret';
        $gateway->config = PaymentGateway::encryptSensitiveConfig($changedConfig);

        $gateway->dispatchSavingEvent();

        self::assertFalse($gateway->is_active);
        self::assertArrayNotHasKey('connectivity_verified_at', $gateway->config);
        self::assertStringContainsString('Konfigurasi berubah', $gateway->config['connectivity_verification_reason']);
    }

    public function test_midtrans_connectivity_uses_read_only_status_endpoint_and_validates_authentication(): void
    {
        $this->http->fake([
            '*' => Http::response([
                'status_code' => '404',
                'status_message' => "Transaction doesn't exist.",
            ], 404),
        ]);

        $gateway = $this->gateway('midtrans', [
            'server_key' => 'midtrans-server-secret',
            'client_key' => 'midtrans-client-key',
            'is_production' => false,
        ]);

        $result = (new PaymentGatewayConnectivityVerifier())->verify($gateway);

        self::assertTrue($result['success'], $result['message']);
        self::assertStringContainsString('Midtrans', $result['message']);
        $this->http->assertSent(fn ($request): bool =>
            str_contains($request->url(), '/v2/orangbaik-connectivity-') &&
            $request->method() === 'GET'
        );
    }

    public function test_flip_connectivity_uses_read_only_bank_list_endpoint(): void
    {
        $this->http->fake([
            '*' => Http::response([
                ['bank_code' => 'bsi', 'bank_name' => 'Bank Syariah Indonesia'],
            ], 200),
        ]);

        $gateway = $this->gateway('flip', [
            'api_key' => 'flip-api-secret',
            'webhook_token' => 'flip-webhook-secret',
            'is_production' => false,
        ]);

        $result = (new PaymentGatewayConnectivityVerifier())->verify($gateway);

        self::assertTrue($result['success'], $result['message']);
        $this->http->assertSent(fn ($request): bool =>
            str_ends_with($request->url(), '/v2/general/banks') &&
            $request->method() === 'GET'
        );
    }

    public function test_generic_connectivity_requires_authenticated_health_check_contract(): void
    {
        $this->http->fake([
            '*' => Http::response([
                'status' => 'ok',
                'authenticated' => true,
            ], 200),
        ]);
        $gateway = $this->gateway('generic', [
            'api_key' => 'generic-api-secret',
            'webhook_token' => 'generic-webhook-secret',
            'endpoint_url' => 'https://api.example.test/v1/payments',
            'verification_url' => 'https://api.example.test/health',
        ]);
        $verifier = new class extends PaymentGatewayConnectivityVerifier {
            protected function resolveGenericEndpoint(string $url): array
            {
                return [];
            }
        };

        $result = $verifier->verify($gateway);

        self::assertTrue($result['success'], $result['message']);
        $this->http->assertSent(fn ($request): bool =>
            $request->url() === 'https://api.example.test/health' &&
            $request->method() === 'GET' &&
            $request->hasHeader('Authorization', 'Bearer generic-api-secret')
        );
    }

    private function gateway(string $code, array $config): PaymentGateway
    {
        return new PaymentGateway([
            'code' => $code,
            'driver' => 'generic',
            'config' => PaymentGateway::encryptSensitiveConfig($config),
        ]);
    }
}
