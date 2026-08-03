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
        'rejection_reason',
        'custom_slug',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'tanggal_mulai' => 'datetime',
        'tanggal_berakhir' => 'datetime',
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

    public function fundraisers()
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where(function ($q) {
            $q->where('campaign_type', 'regular')
                ->orWhere('approval_status', 'approved');
        });
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

    public function isFundraiser($userId)
    {
        return $this->fundraisers()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }

    public function getFundraiserByUser($userId)
    {
        return $this->fundraisers()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    public function getActiveFundraisers()
    {
        return $this->fundraisers()
            ->where('status', 'active')
            ->get();
    }

    public function getFundraisersCount()
    {
        return $this->fundraisers()
            ->where('status', 'active')
            ->count();
    }

    public function isTimeActive(): bool
    {
        return now()->between($this->tanggal_mulai, $this->tanggal_berakhir);
    }

    public function updateActiveStatus(): void
    {
        $this->is_active = $this->isTimeActive() && $this->isApproved();
        $this->save();
    }

    public function getRouteSlug(): string
    {
        return $this->custom_slug ?? $this->slug;
    }

    public function getTotalDonasi()
    {
        return $this->donasi()->sum('nominal');
    }

    public function getTotalDonasiSuccess()
    {
        return $this->donasi()->where('status', 'success')->sum('nominal');
    }

    public function getDonaturCount()
    {
        return $this->donasi()->where('status', 'success')->count();
    }

    public function getProgressPercentage(): float
    {
        if ($this->target_donasi <= 0) {
            return 0;
        }
        $total = $this->getTotalDonasiSuccess();
        return min(100, ($total / $this->target_donasi) * 100);
    }

    public function isDonatable(): bool
    {
        return $this->is_active 
            && $this->isApproved() 
            && $this->isTimeActive()
            && $this->target_donasi > 0;
    }

    public function getRemainingDays(): int
    {
        $now = now();
        $end = $this->tanggal_berakhir;
        
        if ($now->gt($end)) {
            return 0;
        }
        
        return (int) $now->diffInDays($end);
    }

    public function getStatusText(): string
    {
        if (!$this->is_active) {
            return 'Tidak Aktif';
        }
        
        if (!$this->isApproved()) {
            return 'Menunggu Persetujuan';
        }
        
        if ($this->tanggal_mulai->isFuture()) {
            return 'Akan Datang';
        }
        
        if ($this->tanggal_berakhir->isPast()) {
            return 'Berakhir';
        }
        
        return 'Aktif';
    }

    public function getStatusColor(): string
    {
        $status = $this->getStatusText();
        
        return match($status) {
            'Aktif' => 'success',
            'Menunggu Persetujuan' => 'warning',
            'Akan Datang' => 'info',
            'Berakhir' => 'secondary',
            'Tidak Aktif' => 'danger',
            default => 'secondary'
        };
    }
}