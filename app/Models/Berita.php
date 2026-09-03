<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
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
        'slug',
        'custom_slug',
        'user_id'
    ];
}
