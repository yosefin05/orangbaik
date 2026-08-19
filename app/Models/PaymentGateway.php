<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}
