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
        });
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
        return $this->hasMany(Donasi::class);
    }

    public function getReferralUrlAttribute()
    {
        return route('campaign.show', $this->campaign->slug) . '?ref=' . $this->referral_code;
    }
}