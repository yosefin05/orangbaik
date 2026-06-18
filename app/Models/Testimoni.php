<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $table = 'testimoni';
    protected $fillable = [
        'foto_profil',
        'nama',
        'jabatan',
        'isi_testimoni'
    ];
}
