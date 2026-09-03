<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
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
        if (isset($this->config[$key]) && $this->config[$key] !== '') {
            return $this->config[$key];
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

    /**
     * Cek apakah API Key / kredensial gateway sudah terkonfigurasi.
     */
    public function isConfigured(): bool
    {
        if ($this->code === 'manual') {
            return true;
        }

        if ($this->code === 'midtrans') {
            $key = $this->getConfigValue('server_key');
            return !empty($key);
        }

        if ($this->code === 'flip') {
            $key = $this->getConfigValue('api_key');
            return !empty($key);
        }

        // Untuk gateway custom
        $apiKey = $this->getConfigValue('api_key') ?? $this->getConfigValue('server_key');
        return !empty($apiKey);
    }
}
