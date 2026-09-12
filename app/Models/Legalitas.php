<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legalitas extends Model
{
    protected $table = 'legalitas';

    protected $fillable = [
        'judul',
        'nomor_legalitas',
        'deskripsi',
        'file_path',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_active', true)->orderBy('urutan', 'asc');
    }

    public function isImage(): bool
    {
        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp']);
    }

    public function isPdf(): bool
    {
        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return $extension === 'pdf';
    }
}
