<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKeuangan extends Model
{
    protected $table = 'laporan_keuangan';

    protected $fillable = [
        'tahun',
        'judul',
        'deskripsi',
        'file_path',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'tahun'     => 'integer',
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_active', true)->orderBy('tahun', 'desc');
    }
}
