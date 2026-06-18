<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
    public function penggalangDana()
    {
        return $this->belongsTo(Penggalang_Dana::class);
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
    protected $table = 'campaign';
    protected $fillable = [
        'thumbnail',
        'judul',
        'slug',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_berakhir',
        'sedekah rutin?',
        'target_donasi',
        'kategori_id',
        'penggalang_dana_id'
    ];
}
