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
        'webhook_token',
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
                'server_key' => config('payment.midtrans.server_key', $default),
                'client_key' => config('payment.midtrans.client_key', $default),
                'is_production' => config('payment.midtrans.is_production', $default),
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
     * Credential presence only; this is deliberately not a "ready" or connectivity check.
     */
    public function isConfigured(): bool
    {
        return match ($this->code) {
            'manual' => true,
            'midtrans' => $this->hasConfigValue('server_key') && $this->hasConfigValue('client_key'),
            'flip' => $this->hasConfigValue('api_key') && $this->hasConfigValue('webhook_token'),
            default => false,
        };
    }
}
