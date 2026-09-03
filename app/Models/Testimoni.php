<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    use HasFactory;

    protected $table = 'testimoni';

    protected $fillable = [
        'user_id',
        'nama',
        'jabatan',
        'foto_profil',
        'isi_testimoni',
    ];

    /**
     * Relasi ke User pembuat testimoni (opsional / penggalang dana)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
