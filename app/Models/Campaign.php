<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'campaign';
    
    protected $fillable = [
        'thumbnail',
        'judul',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'target_dana',
    ];
}
