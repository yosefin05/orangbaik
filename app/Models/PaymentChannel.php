<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentChannel extends Model
{
    protected $fillable = [
        'payment_gateway_id',
        'name',
        'channel_code',
        'account_name',
        'account_number',
        'payment_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Gateway yang menangani channel ini.
     */
    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    /**
     * Transaksi pembayaran yang menggunakan channel ini.
     */
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Scope: hanya channel aktif, diurutkan berdasarkan sort_order.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Cek apakah channel pernah digunakan dalam transaksi.
     * Digunakan untuk mencegah hard-delete channel yang sudah dipakai.
     */
    public function hasTransactions(): bool
    {
        return $this->pembayarans()->exists();
    }

    /**
     * Label tipe pembayaran untuk tampilan.
     */
    public function getPaymentTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            'instant'  => 'Instant',
            'va'       => 'Virtual Account',
            'transfer' => 'Transfer Manual',
            default    => ucfirst($this->payment_type),
        };
    }
}
