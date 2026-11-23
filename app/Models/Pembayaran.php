<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
        'expired_at',
        'expired_by',
        'expiration_reason',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
        'expired_at'       => 'datetime',
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
     * Waktu kedaluwarsa pembayaran yang dikirim oleh payment gateway.
     *
     * Flip menyertakan expired_date saat membuat bill, sedangkan Midtrans
     * menyertakan expiry_time pada notifikasi transaksi. Metadata ini
     * disimpan di gateway_response agar tidak memerlukan perubahan skema.
     */
    public function getPaymentExpiresAtAttribute(): ?Carbon
    {
        $response = $this->gateway_response ?? [];

        $expiresAt = data_get($response, 'payment_expires_at')
            ?? data_get($response, 'expiry_time')
            ?? data_get($response, 'expired_date')
            ?? data_get($response, 'expired_at')
            ?? data_get($response, 'bill_payment.expired_date');

        if (blank($expiresAt)) {
            return null;
        }

        try {
            return Carbon::parse($expiresAt);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Follow-up pembayaran hanya tersedia selama status masih pending
     * dan batas waktu gateway (jika tersedia) belum terlewati.
     *
     * Campaign yang telah berakhir tidak membatalkan transaksi gateway
     * yang masih berlaku.
     */
    public function isFollowUpAvailable(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $expiresAt = $this->payment_expires_at;

        return $expiresAt === null || now()->lt($expiresAt);
    }

    /**
     * Apakah pembayaran masih dapat dilanjutkan oleh donatur.
     */
    public function isResumable(): bool
    {
        return $this->isFollowUpAvailable();
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