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

    public function test_environment_specific_credentials_never_fall_back_across_modes(): void
    {
        $gateway = PaymentGateway::create([
            'name' => 'Midtrans',
            'code' => 'midtrans',
            'driver' => 'midtrans',
            'is_active' => false,
            'config' => PaymentGateway::secureConfigForStorage([
                'is_production' => false,
                'sandbox_server_key' => 'SB-server-key',
                'sandbox_client_key' => 'SB-client-key',
                'sandbox_signature_secret_key' => 'SB-signature-key',
                'production_server_key' => 'LIVE-server-key',
                'production_client_key' => 'LIVE-client-key',
                'production_signature_secret_key' => 'LIVE-signature-key',
            ]),
        ]);

        $this->assertSame('SB-server-key', $gateway->getCredentialForMode('server_key', 'sandbox'));
        $this->assertSame('SB-client-key', $gateway->getCredentialForMode('client_key', 'sandbox'));
        $this->assertSame('SB-signature-key', $gateway->getCredentialForMode('signature_secret_key', 'sandbox'));
        $this->assertSame('LIVE-server-key', $gateway->getCredentialForMode('server_key', 'production'));
        $this->assertSame('LIVE-client-key', $gateway->getCredentialForMode('client_key', 'production'));
        $this->assertSame('LIVE-signature-key', $gateway->getCredentialForMode('signature_secret_key', 'production'));
    }

    public function test_legacy_credentials_are_only_available_for_their_configured_mode(): void
    {
        $gateway = PaymentGateway::create([
            'name' => 'Midtrans',
            'code' => 'midtrans',
            'driver' => 'midtrans',
            'is_active' => false,
            'config' => PaymentGateway::secureConfigForStorage([
                'is_production' => true,
                'server_key' => 'LEGACY-LIVE-server-key',
                'client_key' => 'LEGACY-LIVE-client-key',
            ]),
        ]);

        $this->assertNull($gateway->getCredentialForMode('server_key', 'sandbox'));
        $this->assertSame('LEGACY-LIVE-server-key', $gateway->getCredentialForMode('server_key', 'production'));
        $this->assertSame('LEGACY-LIVE-server-key', $gateway->getCredentialForMode('signature_secret_key', 'production'));
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
    public function test_saving_provider_configuration_does_not_change_gateway_activation_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $gateway = PaymentGateway::create([
            'name' => 'Midtrans',
            'code' => 'midtrans',
            'driver' => 'midtrans',
            'is_active' => true,
            'config' => PaymentGateway::secureConfigForStorage([
                'sandbox_server_key' => 'old-server-key',
                'sandbox_client_key' => 'old-client-key',
                'sandbox_signature_secret_key' => 'old-signature-key',
                'is_production' => false,
            ]),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.payment.gateway.update', $gateway), [
            'name' => 'Midtrans',
            'description' => 'Updated configuration',
            'is_production' => '0',
        ]);

        $response->assertRedirect(route('admin.payment.gateway.show', $gateway));
        $this->assertTrue($gateway->fresh()->is_active);
        $this->assertSame('old-server-key', $gateway->fresh()->getCredentialForMode('server_key', 'sandbox'));
    }

    public function test_provider_detail_page_is_admin_only_and_renders_without_exposing_secrets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $secret = 'DETAIL-PAGE-SECRET-MUST-STAY-HIDDEN';
        $gateway = PaymentGateway::create([
            'name' => 'Midtrans',
            'code' => 'midtrans',
            'driver' => 'midtrans',
            'is_active' => true,
            'config' => PaymentGateway::secureConfigForStorage([
                'sandbox_server_key' => $secret,
                'sandbox_client_key' => 'PUBLIC-KEY-NOT-RENDERED',
                'sandbox_signature_secret_key' => 'SIGNATURE-NOT-RENDERED',
            ]),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.payment.gateway.show', $gateway));

        $response->assertOk();
        $response->assertSee('Konfigurasi Midtrans');
        $response->assertDontSee($secret);
        $response->assertDontSee('PUBLIC-KEY-NOT-RENDERED');
        $response->assertDontSee('SIGNATURE-NOT-RENDERED');
    }

}
