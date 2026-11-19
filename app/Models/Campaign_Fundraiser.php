<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Campaign_Fundraiser extends Model
{
    protected $table = 'campaign_fundraiser';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'referral_code',
        'alias',
        'qr_path',
        'total_donasi',
        'status',
    ];

    protected $casts = [
        'total_donasi' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->referral_code)) {
                $model->referral_code = $model->generateReferralCode();
            }
            if (empty($model->status)) {
                $model->status = 'active';
            }
            if (empty($model->alias)) {
                $model->alias = $model->generateAlias();
            }
        });
    }

    public function generateAlias(): string
    {
        $base = Str::slug($this->user?->name ?: 'ajak-' . ($this->campaign_id ?: 'campaign')) ?: 'ajak';
        $alias = $base;
        $suffix = 1;
        while (self::where('alias', $alias)->where('id', '!=', $this->getKey() ?: 0)->exists()) {
            $alias = $base . '-' . (++$suffix);
        }
        return $alias;
    }

    public function getRouteKeyName(): string
    {
        return 'alias';
    }

    public function generateReferralCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function donasis()
    {
        return $this->hasMany(Donasi::class, 'fundraiser_id');
    }

    public function getTotalDonasiSettlementAttribute(): int
    {
        return $this->donasis
            ->filter(fn($donasi) => $donasi->pembayaran?->transaction_status === 'settlement')
            ->sum('nominal');
    }

    public function getReferralUrlAttribute()
    {
        return route('referral.track', ['alias' => $this->alias]);
    }
}
