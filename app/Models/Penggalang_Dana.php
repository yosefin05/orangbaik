<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penggalang_Dana extends Model
{
    public function penggalangDanaDokumen()
    {
        return $this->hasMany(
            Penggalang_Dana_Dokumen::class,
            'penggalang_dana_id',
            'id'
        );
    }

    public function campaign()
    {
        return $this->hasMany(
            Campaign::class,
            'penggalang_dana_id', // FK di tabel campaign
            'id'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    protected $table = 'penggalang_dana';

    protected $fillable = [
        'jenis_penggalang',
        'thumbnail',
        'foto_profil',
        'nama_penggalang',
        'tahun_berdiri',
        'alamat',
        'deskripsi',
        'visi',
        'misi',
        'email',
        'no_telepon',
        'instagram',
        'facebook',
        'youtube',
        'tiktok',
        'status',
        'user_id',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];
}