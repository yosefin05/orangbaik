<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'email',
        'no_hp',
        'nominal',
        'pesan_doa',
        'is_anonim',
    ];

    protected $casts = [
        'is_anonim' => 'boolean',
    ];

}