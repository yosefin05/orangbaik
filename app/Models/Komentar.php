<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function berita()
    {
        return $this->belongsTo(Berita::class);
    }

    protected $table = 'komentar';
    protected $fillable = [
        'komentar',
        'user_id',
        'berita_id'
    ];
}
