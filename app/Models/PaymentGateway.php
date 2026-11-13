<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use App\Services\PaymentGateways\PublicGatewayEndpoint;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\MissingAppKeyException;

class PaymentGateway extends Model
{
    private const ENCRYPTED_CONFIG_PREFIX = 'encrypted:v1:';

    private const SENSITIVE_CONFIG_KEYS = [
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
        'config'    => 'array',
    ];

    protected $hidden = [
        'config',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $gateway): void {
            if (!$gateway->isDirty('config')) {
                return;
            }

            $config = $gateway->config;

            if (is_array($config)) {
                $originalConfig = $gateway->getRawOriginal('config');
                if (is_string($originalConfig)) {
                    $originalConfig = json_decode($originalConfig, true) ?: [];
                }
                $originalConfig = is_array($originalConfig) ? $originalConfig : [];

                foreach ([
                    'api_key',
                    'server_key',
                    'client_key',
                    'webhook_token',
                    'endpoint_url',
                    'verification_url',
                    'is_production',
                ] as $key) {
                    if (($config[$key] ?? null) !== ($originalConfig[$key] ?? null)) {
                        unset($config['connectivity_verified_at']);
                        $config['connectivity_verification_reason'] = 'Konfigurasi berubah; verifikasi konektivitas ulang diperlukan.';
                        $gateway->is_active = false;
                        break;
                    }
                }

                $gateway->config = self::encryptSensitiveConfig($config);
            }
        });
    }

    /**
     * Payment channels yang menggunakan gateway ini.
     */
    public function channels()
    {
        return $this->hasMany(PaymentChannel::class);
    }

    /**
     * Payment channels yang aktif.
     */
    public function activeChannels()
    {
        return $this->hasMany(PaymentChannel::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function hasUnresolvedTransactions(): bool
    {
        return $this->channels()
            ->whereHas('pembayarans', fn ($query) => $query->where(function ($statusQuery): void {
                $statusQuery->whereNull('transaction_status')
                    ->orWhereNotIn('transaction_status', [
                        'settlement',
                        'failed',
                        'expired',
                        'expire',
                    ]);
            }))
            ->exists();
    }

    /**
     * Scope: hanya gateway aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ambil nilai konfigurasi tertentu dari database atau fallback ke .env/config.
     */
    public function getConfigValue(string $key, $default = null)
    {
        $value = $this->config[$key] ?? null;

        if (is_string($value) && str_starts_with($value, self::ENCRYPTED_CONFIG_PREFIX)) {
            try {
                return Crypt::decryptString(substr($value, strlen(self::ENCRYPTED_CONFIG_PREFIX)));
            } catch (DecryptException|MissingAppKeyException) {
                return null;
            }
        }

        if ($value !== null && $value !== '') {
            return $value;
        }

        // Fallback ke config file
        if ($this->code === 'midtrans') {
            return match ($key) {
                'server_key'    => config('payment.midtrans.server_key', $default),
                'client_key'    => config('payment.midtrans.client_key', $default),
                'is_production' => config('payment.midtrans.is_production', $default),
                default         => $default,
            };
        }

        if ($this->code === 'flip') {
            return match ($key) {
                'api_key'       => config('payment.flip.api_key', $default),
                'webhook_token' => config('payment.flip.webhook_token', $default),
                'is_production' => config('payment.flip.is_production', $default),
                default         => $default,
            };
        }

        return $default;
    }

    public function hasConfigValue(string $key): bool
    {
        $value = $this->config[$key] ?? null;
        if ($value !== null && $value !== '') {
            return true;
        }

        return filled($this->fallbackConfigValue($key));
    }

    public static function encryptionKeyAvailable(): bool
    {
        try {
            app('encrypter')->encryptString('payment-gateway-key-check');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function canEncryptSensitiveConfig(array $config): bool
    {
        foreach (self::SENSITIVE_CONFIG_KEYS as $key) {
            $value = $config[$key] ?? null;
            if (
                is_string($value) &&
                $value !== '' &&
                !str_starts_with($value, self::ENCRYPTED_CONFIG_PREFIX) &&
                !self::encryptionKeyAvailable()
            ) {
                return false;
            }
        }

        return true;
    }

    public static function hasPlaintextSensitiveConfig(array $config): bool
    {
        foreach (self::SENSITIVE_CONFIG_KEYS as $key) {
            $value = $config[$key] ?? null;
            if (
                is_string($value) &&
                $value !== '' &&
                !str_starts_with($value, self::ENCRYPTED_CONFIG_PREFIX)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Encrypt gateway credentials before persisting them in the existing JSON config column.
     * Legacy plaintext values remain readable until the configuration is next saved.
     */
    public static function encryptSensitiveConfig(array $config): array
    {
        foreach (self::SENSITIVE_CONFIG_KEYS as $key) {
            $value = $config[$key] ?? null;

            if (
                is_string($value) &&
                $value !== '' &&
                !str_starts_with($value, self::ENCRYPTED_CONFIG_PREFIX)
            ) {
                if (!self::encryptionKeyAvailable()) {
                    throw new \RuntimeException('APP_KEY belum tersedia atau tidak valid; kredensial gateway tidak dapat dienkripsi.');
                }

                $config[$key] = self::ENCRYPTED_CONFIG_PREFIX . Crypt::encryptString($value);
            }
        }

        return $config;
    }

    public function channelInstructions(PaymentChannel $channel): ?string
    {
        $instructions = $this->config['channel_instructions'][(string) $channel->id] ?? null;

        return is_string($instructions) && trim($instructions) !== '' ? $instructions : null;
    }

    public function setChannelInstructions(PaymentChannel $channel, ?string $instructions): void
    {
        $config = $this->config ?? [];
        $channelInstructions = $config['channel_instructions'] ?? [];
        $key = (string) $channel->id;

        if (is_string($instructions) && trim($instructions) !== '') {
            $channelInstructions[$key] = trim($instructions);
        } else {
            unset($channelInstructions[$key]);
        }

        if ($channelInstructions) {
            $config['channel_instructions'] = $channelInstructions;
        } else {
            unset($config['channel_instructions']);
        }

        $this->update(['config' => $config]);
    }

    /**
     * Cek apakah API Key / kredensial gateway sudah terkonfigurasi.
     */
    public function isConfigured(): bool
    {
        if ($this->code === 'manual') {
            return $this->channels()
                ->where('payment_type', 'transfer')
                ->whereNotNull('name')
                ->where('name', '<>', '')
                ->whereNotNull('account_name')
                ->where('account_name', '<>', '')
                ->whereNotNull('account_number')
                ->where('account_number', '<>', '')
                ->exists();
        }

        if ($this->code === 'midtrans') {
            return filled($this->getConfigValue('server_key'))
                && filled($this->getConfigValue('client_key'));
        }

        if ($this->code === 'flip') {
            return filled($this->getConfigValue('api_key'))
                && filled($this->getConfigValue('webhook_token'));
        }

        if ($this->driver !== 'generic') {
            return false;
        }

        $apiKey = $this->getConfigValue('api_key') ?: $this->getConfigValue('server_key');

        return filled($apiKey)
            && filled($this->getConfigValue('endpoint_url'))
            && PublicGatewayEndpoint::isSafeUrl((string) $this->getConfigValue('endpoint_url'))
            && filled($this->getConfigValue('verification_url'))
            && PublicGatewayEndpoint::isSafeUrl((string) $this->getConfigValue('verification_url'))
            && $this->sameGenericEndpointOrigin()
            && filled($this->getConfigValue('webhook_token'));
    }

    public function isOperationallyVerified(): bool
    {
        if ($this->code === 'manual') {
            return $this->isConfigured();
        }

        return self::encryptionKeyAvailable()
            && $this->isConfigured()
            && filled($this->config['connectivity_verified_at'] ?? null);
    }

    public function connectivityVerificationReason(): string
    {
        if (!self::encryptionKeyAvailable()) {
            return 'APP_KEY tidak tersedia atau tidak valid; kredensial gateway tidak dapat dibaca atau disimpan dengan aman.';
        }

        $reason = $this->config['connectivity_verification_reason'] ?? null;
        if (is_string($reason) && $reason !== '') {
            return $reason;
        }

        if (!$this->isConfigured()) {
            return 'Konfigurasi wajib belum lengkap atau kredensial tidak dapat dibaca.';
        }

        return filled($this->config['connectivity_verified_at'] ?? null)
            ? 'Verifikasi konektivitas tersimpan.'
            : 'Konektivitas provider belum diverifikasi.';
    }

    private function fallbackConfigValue(string $key)
    {
        if ($this->code === 'midtrans') {
            return match ($key) {
                'server_key'    => config('payment.midtrans.server_key'),
                'client_key'    => config('payment.midtrans.client_key'),
                'is_production' => config('payment.midtrans.is_production'),
                default         => null,
            };
        }

        if ($this->code === 'flip') {
            return match ($key) {
                'api_key'       => config('payment.flip.api_key'),
                'webhook_token' => config('payment.flip.webhook_token'),
                'is_production' => config('payment.flip.is_production'),
                default         => null,
            };
        }

        return null;
    }

    private function sameGenericEndpointOrigin(): bool
    {
        $endpoint = parse_url((string) $this->getConfigValue('endpoint_url'));
        $verification = parse_url((string) $this->getConfigValue('verification_url'));

        return is_array($endpoint)
            && is_array($verification)
            && strtolower((string) ($endpoint['host'] ?? '')) === strtolower((string) ($verification['host'] ?? ''))
            && ($endpoint['port'] ?? 443) === ($verification['port'] ?? 443);
    }
}
