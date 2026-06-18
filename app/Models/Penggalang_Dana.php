<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penggalang_Dana extends Model
{
    public function penggalangDanaDokumen()
    {
        return $this->hasMany(Penggalang_Dana_Dokumen::class);
    }
    public function campaign()
    {
        return $this->hasMany(Campaign::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $table = 'penggalang_dana';
    protected $fillable = [
        'jenis_penggalang',
        'foto_profil',
        'nama_penggalang',
        'alamat',
        'deskripsi',
        'visi',
        'misi',
        'email',
        'no_telepon',
        'instagram',
        'facebook',
        'youtube',
        'tiktok'
    ];
}
