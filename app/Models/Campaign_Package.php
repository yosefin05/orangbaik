<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Package extends Model
{

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function getEmojiAttribute()
    {
        // Coba ambil karakter pertama yang merupakan emoji (range Unicode)
        // Cara sederhana: ambil 1 karakter pertama, jika bukan huruf/angka, anggap emoji
        $first = mb_substr($this->judul, 0, 1);
        if (preg_match('/[^\p{L}\p{N}]/u', $first)) {
            return $first;
        }
        // Fallback berdasarkan nominal
        $map = [
            10000 => '💰',
            25000 => '💎',
            50000 => '🎁',
            100000 => '🌟',
            250000 => '🏆',
            500000 => '👑',
        ];
        return $map[$this->nominal] ?? '💳';
    }

    protected $table = 'campaign_package';

    protected $fillable = [
        'campaign_id',
        'judul',
        'deskripsi',
        'nominal',
        'gambar',
    ];
}