<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita_Gallery extends Model
{
    protected $table = 'berita_gallery';
    protected $fillable = [
        'berita_id',
        'foto'
    ];
}
