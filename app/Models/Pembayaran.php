<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'donasi_id',
        'payment_channel_id',
        'payment_token',
        'order_id',
        'snap_token',
        'payment_type',
        'transaction_id',
        'transaction_status',
        'gateway_response',
        'bukti_transfer',
        'rejection_reason',
        'paid_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
    ];

    /**
     * Donasi yang terkait dengan pembayaran ini.
     */
    public function donasi()
    {
        return $this->belongsTo(Donasi::class);
    }

    /**
     * Payment channel yang digunakan donatur.
     */
    public function paymentChannel()
    {
        return $this->belongsTo(PaymentChannel::class);
    }

    /**
     * Label status pembayaran untuk tampilan ke donatur.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->transaction_status) {
            'settlement' => 'Berhasil',
            'pending'    => 'Menunggu Pembayaran',
            'failed'     => 'Gagal',
            'expired'    => 'Kedaluwarsa',
            'expire'     => 'Kedaluwarsa',
            default      => ucfirst($this->transaction_status ?? 'Tidak Diketahui'),
        };
    }

    /**
     * CSS class untuk badge status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->transaction_status) {
            'settlement' => 'badge-success',
            'pending'    => 'badge-warning',
            'failed'     => 'badge-danger',
            'expired',
            'expire'     => 'badge-secondary',
            default      => 'badge-secondary',
        };
    }

    /**
     * Apakah pembayaran ini adalah transfer manual.
     */
    public function isManualTransfer(): bool
    {
        return $this->paymentChannel?->gateway?->code === 'manual';
    }

    /**
     * Apakah pembayaran sudah lunas (settlement).
     */
    public function isSettled(): bool
    {
        return $this->transaction_status === 'settlement';
    }

    /**
     * Apakah pembayaran masih pending.
     */
    public function isPending(): bool
    {
        return $this->transaction_status === 'pending';
    }

    /**
     * Apakah pembayaran bisa dilanjutkan (masih pending, belum terminal).
     */
    public function isResumable(): bool
    {
        return $this->transaction_status === 'pending';
    }

    /**
     * Cek apakah request guest memiliki token yang valid untuk mengakses pembayaran ini.
     * Digunakan di instruksi dan upload bukti untuk mencegah IDOR pada guest donation.
     *
     * @param  string|null  $token  Token dari query string
     * @return bool
     */
    public function isValidGuestToken(?string $token): bool
    {
        if (empty($token) || empty($this->payment_token)) {
            return false;
        }
        return hash_equals($this->payment_token, $token);
    }
}