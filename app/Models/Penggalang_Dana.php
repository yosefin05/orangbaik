<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penggalang_Dana extends Model
{
    protected $table = 'penggalang_dana';
    protected $fillable = [
        'user_id',
        'jenis_penggalang',
        'nama',
        'foto_profil',
        'deskripsi',
        'visi_misi',
        'dokumen_verifikasi',
        'status_verifikasi',
        'verified_by',
        'verified_at'
    ];
}
