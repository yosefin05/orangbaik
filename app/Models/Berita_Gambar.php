<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita_Gambar extends Model
{
    public function berita()
    {
        return $this->belongsTo(Berita::class);
    }
    protected $table = 'berita_gambar';
    protected $fillable = [
        'berita_id',
        'gambar'
    ];
}
