<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Campaign_Fundraiser extends Model
{
    protected $table = 'campaign_fundraiser';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'referral_code',
        'alias',
        'short_alias',
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
        $name = $this->user?->name ?: ($this->user_id ? $this->user()->value('name') : null);
        $base = Str::slug($name ?: 'ajak-' . ($this->campaign_id ?: 'campaign')) ?: 'ajak';
        $alias = $base;
        $suffix = 1;
        while (
            self::where('alias', $alias)->where('id', '!=', $this->getKey() ?: 0)->exists()
            || Campaign_Fundraiser_Alias::where('alias', $alias)->exists()
        ) {
            $alias = $base . '-' . (++$suffix);
        }
        return $alias;
    }

    public static function aliasRules(?int $ignoreId = null): array
    {
        $unique = Rule::unique('campaign_fundraiser', 'alias');
        if ($ignoreId !== null) {
            $unique->ignore($ignoreId);
        }

        return [
            'required', 'string', 'alpha_dash', 'min:3', 'max:120', $unique,
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (Campaign_Fundraiser_Alias::where('alias', $value)->exists()) {
                    $fail('Alias tersebut pernah digunakan dan tidak dapat dipakai ulang.');
                }
            },
        ];
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

    public static function shortAliasRules(?int $ignoreId = null): array
    {
        $unique = Rule::unique('campaign_fundraiser', 'short_alias');
        if ($ignoreId !== null) {
            $unique->ignore($ignoreId);
        }

        return [
            'nullable', 'string', 'alpha_dash', 'min:3', 'max:120', $unique,
            function (string $attribute, mixed $value, \\Closure $fail) use ($ignoreId): void {
                if (!$value) {
                    return;
                }

                $conflictsWithFundraiserAlias = self::where('alias', $value)
                    ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                    ->exists();
                $reserved = Campaign_Fundraiser_Alias::where('alias', $value)->exists();
                $campaignSlug = Campaign::where('slug', $value)->orWhere('custom_slug', $value)->exists();
                $reservedRoute = in_array(strtolower($value), Campaign::reservedCustomSlugs(), true);

                if ($conflictsWithFundraiserAlias || $reserved || $campaignSlug || $reservedRoute) {
                    $fail('Alias pendek sudah digunakan atau merupakan URL yang dicadangkan.');
                }
            },
        ];
    }

    public function getReferralUrlAttribute()
    {
        if (!$this->campaign) {
            return '';
        }

        $campaignPath = $this->campaign->custom_slug
            ? $this->campaign->custom_slug
            : 'campaign/' . $this->campaign->slug;

        return url('/' . $campaignPath . '/' . $this->alias);
    }

    public function getShortReferralUrlAttribute(): ?string
    {
        return $this->short_alias ? url('/' . $this->short_alias) : null;
    }

    public function donorCountSettlement(): int
    {
        return Campaign::countUniqueSettlementDonors(
            $this->donasis()->whereHas('pembayaran', fn ($q) => $q->where('transaction_status', 'settlement'))->get()
        );
    }
}
