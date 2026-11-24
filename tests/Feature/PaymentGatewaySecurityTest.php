<?php

namespace Tests\Feature;

use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentGatewaySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_secrets_are_encrypted_at_rest_and_decrypted_for_runtime_only(): void
    {
        $serverKey = 'SB-Mid-server-private-test-secret';
        $clientKey = 'SB-Mid-client-public-test-key';
        $signatureSecret = 'SB-Mid-signature-secret-test';

        $gateway = PaymentGateway::create([
            'name' => 'Midtrans',
            'code' => 'midtrans',
            'driver' => 'midtrans',
            'is_active' => false,
            'config' => PaymentGateway::secureConfigForStorage([
                'server_key' => $serverKey,
                'client_key' => $clientKey,
                'signature_secret_key' => $signatureSecret,
                'is_production' => false,
            ]),
        ]);

        $storedConfig = (string) $gateway->getRawOriginal('config');

        $this->assertStringNotContainsString($serverKey, $storedConfig);
        $this->assertStringNotContainsString($clientKey, $storedConfig);
        $this->assertStringNotContainsString($signatureSecret, $storedConfig);
        $this->assertSame($serverKey, $gateway->getConfigValue('server_key'));
        $this->assertSame($clientKey, $gateway->getConfigValue('client_key'));
        $this->assertSame($signatureSecret, $gateway->getConfigValue('signature_secret_key'));
        $this->assertTrue($gateway->isConfigured());
    }

    public function test_midtrans_is_not_configured_when_either_required_key_is_missing(): void
    {
        $gateway = PaymentGateway::create([
            'name' => 'Midtrans',
            'code' => 'midtrans',
            'driver' => 'midtrans',
            'is_active' => false,
            'config' => PaymentGateway::secureConfigForStorage([
                'server_key' => 'server-key-only',
            ]),
        ]);

        $this->assertFalse($gateway->isConfigured());

        $gateway->config = PaymentGateway::secureConfigForStorage([
            'server_key' => 'server-key-only',
            'client_key' => 'client-key',
        ]);
        $gateway->save();

        $this->assertTrue($gateway->fresh()->isConfigured());
    }

    public function test_manager_rejects_provider_without_a_dedicated_adapter(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(PaymentGatewayManager::class)->driver('future-provider');
    }

    public function test_admin_gateway_page_does_not_render_raw_gateway_secrets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $secret = 'DO-NOT-RENDER-THIS-SERVER-SECRET';

        PaymentGateway::create([
            'name' => 'Midtrans',
            'code' => 'midtrans',
            'driver' => 'midtrans',
            'is_active' => false,
            'config' => PaymentGateway::secureConfigForStorage([
                'server_key' => $secret,
                'client_key' => 'PUBLIC-CLIENT-KEY',
                'is_production' => false,
            ]),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.payment.gateway.index'));

        $response->assertOk();
        $response->assertDontSee($secret);
        $response->assertDontSee('PUBLIC-CLIENT-KEY');
        $response->assertSee('Kredensial tersedia');
    }
}
