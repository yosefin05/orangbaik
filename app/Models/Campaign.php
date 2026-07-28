<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'campaign';

    protected $fillable = [
        'thumbnail',
        'judul',
        'slug',
        'deskripsi',
        'is_active',
        'tanggal_mulai',
        'tanggal_berakhir',
        'target_donasi',
        'minimal_donasi',
        'kategori_id',
        'penggalang_dana_id',
        'verified_by',
        'verified_at',
        'enable_quantity',
        'enable_nama_donatur',
        'enable_custom_nominal',
        'campaign_type',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejection_reason'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // ============================================================
    // RELASI
    // ============================================================

    public function filter()
    {
        return $this->belongsToMany(
            Filter::class,
            'campaign_filter',
            'campaign_id',
            'filter_id'
        );
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function penggalangDana()
    {
        return $this->belongsTo(Penggalang_Dana::class, 'penggalang_dana_id');
    }

    public function campaignGambar()
    {
        return $this->hasMany(Campaign_Gambar::class);
    }

    public function campaignFilter()
    {
        return $this->hasMany(Campaign_Filter::class);
    }

    public function campaignUpdates()
    {
        return $this->hasMany(Campaign_Update::class)->latest();
    }

    public function campaignFundraisers()
    {
        return $this->hasMany(Campaign_Fundraiser::class);
    }

    public function donasi()
    {
        return $this->hasMany(Donasi::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function packages()
    {
        return $this->hasMany(Campaign_Package::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeEmergency($query)
    {
        return $query->where('campaign_type', 'emergency')
            ->where('approval_status', 'approved');
    }

    public function scopeSustainable($query)
    {
        return $query->where('campaign_type', 'sustainable')
            ->where('approval_status', 'approved');
    }

    public function scopeRegular($query)
    {
        return $query->where('campaign_type', 'regular');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereIn('campaign_type', ['emergency', 'sustainable'])
            ->where('approval_status', 'pending');
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    public function needsApproval()
    {
        return in_array($this->campaign_type, ['emergency', 'sustainable'])
            && $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        if (in_array($this->campaign_type, ['emergency', 'sustainable'])) {
            return $this->approval_status === 'approved';
        }
        return true;
    }

    public function isOwner($userId)
    {
        return $this->penggalangDana && $this->penggalangDana->user_id == $userId;
    }

    /**
     * Cek apakah user adalah fundraiser aktif untuk campaign ini
     * (HANYA 1 DEKLARASI!)
     */
    public function isFundraiser($userId)
    {
        return $this->campaignFundraisers()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Ambil data fundraiser untuk user tertentu di campaign ini
     */
    public function getFundraiserByUser($userId)
    {
        return $this->campaignFundraisers()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Cek apakah campaign aktif secara waktu (belum berakhir)
     */
    public function isTimeActive(): bool
    {
        return now()->between($this->tanggal_mulai, $this->tanggal_berakhir);
    }

    /**
     * Update status is_active berdasarkan tanggal
     */
    public function updateActiveStatus(): void
    {
        $this->is_active = $this->isTimeActive() && $this->isApproved();
        $this->save();
    }
}