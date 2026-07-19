<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
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
        return $this->belongsTo(
            Kategori::class,
            'kategori_id'
        );
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
        return $this->hasMany(Campaign_Update::class);
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
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function packages()
    {
        return $this->hasMany(Campaign_Package::class);
    }

    public function emergencyApprovedBy()
    {
        return $this->belongsTo(User::class, 'emergency_approved_by');
    }

    // Scopes untuk filter di landing page
    public function scopeEmergency($query)
    {
        return $query->where('campaign_type', 'emergency')
            ->where('emergency_approval', 'approved');
    }

    public function scopeSustainable($query)
    {
        return $query->where('campaign_type', 'sustainable')
            ->where('emergency_approval', 'approved');
    }

    public function scopeRegular($query)
    {
        return $query->where('campaign_type', 'regular');
    }

    // Scope untuk admin approval
    public function scopePendingEmergencyApproval($query)
    {
        return $query->whereIn('campaign_type', ['emergency', 'sustainable'])
            ->where('emergency_approval', 'pending');
    }

    // Helper methods
    public function needsEmergencyApproval()
    {
        return in_array($this->campaign_type, ['emergency', 'sustainable'])
            && $this->emergency_approval === 'pending';
    }

    public function isEmergencyApproved()
    {
        if (in_array($this->campaign_type, ['emergency', 'sustainable'])) {
            return $this->emergency_approval === 'approved';
        }
        return true; // Regular campaign selalu approved
    }

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
        'emergency_approval',
        'emergency_approved_at',
        'emergency_approved_by',
        'emergency_rejection_reason'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'emergency_approved_at' => 'datetime',
    ];
}
