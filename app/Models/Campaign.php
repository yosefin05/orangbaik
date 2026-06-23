<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
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

    public function verifier()
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }
    protected $table = 'campaign';
    protected $fillable = [
        'thumbnail',
        'judul',
        'slug',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_berakhir',
        'target_donasi',
        'kategori_id',
        'penggalang_dana_id',
        'status',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];
}
