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
    
    protected $table = 'donasi';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'nama_donatur',
        'email',
        'no_hp',
        'nominal',
        'pesan_doa',
        'is_anonim',
        'status',
    ];

    protected $casts = [
        'is_anonim' => 'boolean',
    ];

}