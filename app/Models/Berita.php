<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function gambar()
    {
        return $this->hasMany(Berita_Gambar::class);
    }
    public function komentar()
    {
        return $this->hasMany(Komentar::class);
    }
    protected $table = 'berita';
    protected $fillable = [
        'thumbnail',
        'judul',
        'isi',
        'slug'
    ];
}
