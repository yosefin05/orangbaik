<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\DonorWhatsAppMessage;

class Donasi extends Model
{
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fundraiser()
    {
        return $this->belongsTo(Campaign_Fundraiser::class, 'fundraiser_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }

    protected $table = 'donasi';

    protected $fillable = [
        'campaign_id',
        'fundraiser_id',
        'user_id',
        'nama_donatur',
        'sapaan',
        'email',
        'no_hp',
        'nominal',
        'pesan_doa',
        'is_anonim',
    ];

    protected $casts = [
        'is_anonim' => 'boolean',
    ];

    public function getSapaanAttribute($value): string
    {
        return in_array($value, ['Kak', 'Pak', 'Bu'], true) ? $value : 'Kak';
    }

    /**
     * Backward-compatibility for is_anonymous
     */
    public function getIsAnonymousAttribute(): bool
    {
        return (bool) ($this->attributes['is_anonim'] ?? false);
    }

    public function setIsAnonymousAttribute($value): void
    {
        $this->attributes['is_anonim'] = (bool) $value;
    }

    /**
     * Ambil nama donatur yang aman untuk tampilan publik
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_anonim || $this->is_anonymous) {
            return 'Hamba Allah';
        }

        return $this->nama_donatur ?: ($this->user?->name ?? 'Donatur');
    }

    /** Stable identity for unique-donor statistics; never used for public display. */
    public function getDonorIdentityKeyAttribute(): string
    {
        if ($this->user_id !== null) {
            return 'user:' . $this->user_id;
        }

        $email = strtolower(trim((string) $this->email));
        if ($email !== '') {
            return 'email:' . $email;
        }

        $phone = preg_replace('/\D+/', '', (string) $this->no_hp);
        if ($phone !== '') {
            return 'phone:' . $phone;
        }

        return 'donation:' . $this->getKey();
    }

    /**
     * Ambil nomor telepon donatur (dari donasi atau akun user)
     */
    public function getDonorPhoneAttribute(): ?string
    {
        $phone = $this->no_hp ?: $this->user?->nomor;
        return !empty($phone) ? trim($phone) : null;
    }

    /**
     * Normalisasi nomor telepon ke format internasional WhatsApp Indonesia (+62 / 628...)
     */
    public function getNormalizedWhatsappNumberAttribute(): ?string
    {
        $phone = $this->donor_phone;
        if (empty($phone)) {
            return null;
        }

        return DonorWhatsAppMessage::normalizeIndonesianPhone($phone);
    }

    /**
     * Bangun tautan WhatsApp resmi dengan pesan awal otomatis
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        $message = app(DonorWhatsAppMessage::class)->forDonation($this);
        return $message ? 'https://wa.me/' . $this->normalized_whatsapp_number . '?text=' . rawurlencode($message) : null;
    }
}
