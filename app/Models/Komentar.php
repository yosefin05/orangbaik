<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    protected $table = 'komentar';
    protected $fillable = [
        'isi_komentar',
        'berita_id',
        'user_id'
    ];
}
