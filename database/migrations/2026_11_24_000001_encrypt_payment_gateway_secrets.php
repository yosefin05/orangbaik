<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $secretKeys = [
        'api_key',
        'server_key',
        'client_key',
        'signature_secret_key',
        'webhook_token',
        'sandbox_api_key',
        'production_api_key',
        'sandbox_webhook_token',
        'production_webhook_token',
        'sandbox_server_key',
        'sandbox_client_key',
        'sandbox_signature_secret_key',
        'production_server_key',
        'production_client_key',
        'production_signature_secret_key',
    ];

    public function up(): void
    {
        DB::table('payment_gateways')
            ->select(['id', 'config'])
            ->orderBy('id')
            ->chunkById(100, function ($gateways): void {
                foreach ($gateways as $gateway) {
                    $config = json_decode($gateway->config ?: '{}', true);
                    if (!is_array($config)) {
                        continue;
                    }

                    $changed = false;
                    foreach ($this->secretKeys as $key) {
                        $value = $config[$key] ?? null;
                        if (!is_string($value) || $value === '' || str_starts_with($value, 'encrypted:v1:')) {
                            continue;
                        }

                        $config[$key] = 'encrypted:v1:' . Crypt::encryptString($value);
                        $changed = true;
                    }

                    if ($changed) {
                        DB::table('payment_gateways')
                            ->where('id', $gateway->id)
                            ->update(['config' => json_encode($config)]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Intentionally irreversible: credentials must remain encrypted at rest.
    }
};
