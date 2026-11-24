<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class PaymentGateway extends Model
{
    private const SECRET_CONFIG_KEYS = [
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

    protected $fillable = [
        'name',
        'code',
        'driver',
        'description',
        'is_active',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function channels()
    {
        return $this->hasMany(PaymentChannel::class);
    }

    public function activeChannels()
    {
        return $this->hasMany(PaymentChannel::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Payment capabilities exposed by the currently implemented provider adapters. */
    public function supportsPaymentType(string $paymentType): bool
    {
        return in_array($paymentType, match ($this->code) {
            'midtrans' => ['instant'],
            'flip' => ['va', 'instant'],
            'manual' => ['transfer'],
            default => [],
        }, true);
    }

    /**
     * Encrypt secret values before persisting them inside the JSON config column.
     * The prefix allows safe backwards compatibility with legacy plaintext values.
     */
    public static function secureConfigForStorage(array $config): array
    {
        foreach (self::SECRET_CONFIG_KEYS as $key) {
            $value = $config[$key] ?? null;

            if (is_string($value) && $value !== '' && !str_starts_with($value, 'encrypted:v1:')) {
                $config[$key] = 'encrypted:v1:' . Crypt::encryptString($value);
            }
        }

        return $config;
    }

    public function getConfigValue(string $key, $default = null)
    {
        $value = data_get($this->config ?? [], $key);

        if ($value !== null && $value !== '') {
            if (in_array($key, self::SECRET_CONFIG_KEYS, true) && is_string($value) && str_starts_with($value, 'encrypted:v1:')) {
                try {
                    return Crypt::decryptString(substr($value, strlen('encrypted:v1:')));
                } catch (Throwable $exception) {
                    report($exception);
                    return $default;
                }
            }

            return $value;
        }

        // Legacy environment fallback remains for existing installations during migration.
        if ($this->code === 'midtrans') {
            return match ($key) {
                'server_key' => config('midtrans.serverKey') ?: config('payment.midtrans.server_key', $default),
                'client_key' => config('midtrans.clientKey') ?: config('payment.midtrans.client_key', $default),
                // Legacy Midtrans accounts used Server Key to verify notifications before 28 Oct 2026.
                'signature_secret_key' => config('midtrans.signatureSecretKey') ?: config('payment.midtrans.signature_secret_key') ?: $this->getConfigValue('server_key', $default),
                'is_production' => config('midtrans.isProduction', config('payment.midtrans.is_production', $default)),
                default => $default,
            };
        }

        if ($this->code === 'flip') {
            return match ($key) {
                'api_key' => config('payment.flip.api_key', $default),
                'webhook_token' => config('payment.flip.webhook_token', $default),
                'is_production' => config('payment.flip.is_production', $default),
                default => $default,
            };
        }

        return $default;
    }

    public function hasConfigValue(string $key): bool
    {
        return filled($this->getConfigValue($key));
    }

    /**
     * Resolve a credential only for the requested environment.
     * Legacy unscoped credentials are accepted only for the mode they were configured for.
     */
    public function getCredentialForMode(string $key, string $mode, $default = null)
    {
        if (!in_array($mode, ['sandbox', 'production'], true)) {
            return $default;
        }

        $modeKey = $mode . '_' . $key;
        $modeValue = $this->getConfigValue($modeKey);
        if (filled($modeValue)) {
            return $modeValue;
        }

        $legacyMode = (bool) $this->getConfigValue('is_production', false)
            ? 'production'
            : 'sandbox';

        if ($legacyMode !== $mode) {
            return $default;
        }

        $legacyValue = $this->getConfigValue($key);
        if (filled($legacyValue)) {
            return $legacyValue;
        }

        // Midtrans accounts created before the 28 Oct 2026 key split used Server Key
        // as the notification-signature secret. Keep this fallback only for legacy config.
        if ($this->code === 'midtrans' && $key === 'signature_secret_key') {
            return $this->getConfigValue('server_key', $default);
        }

        return $default;
    }

    public function activeCredentialMode(): string
    {
        $wantsProduction = (bool) $this->getConfigValue('is_production', false);
        return $wantsProduction && app()->environment('production') ? 'production' : 'sandbox';
    }

    /**
     * Credential presence only; this is deliberately not a "ready" or connectivity check.
     */
    public function isConfigured(): bool
    {
        $mode = $this->activeCredentialMode();

        return match ($this->code) {
            'manual' => true,
            'midtrans' => filled($this->getCredentialForMode('server_key', $mode))
                && filled($this->getCredentialForMode('client_key', $mode))
                && filled($this->getCredentialForMode('signature_secret_key', $mode)),
            'flip' => filled($this->getCredentialForMode('api_key', $mode))
                && filled($this->getCredentialForMode('webhook_token', $mode)),
            default => false,
        };
    }
}
