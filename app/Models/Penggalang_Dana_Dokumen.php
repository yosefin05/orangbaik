<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Penggalang_Dana_Dokumen extends Model
{
    protected $table = 'penggalang_dana_dokumen';

    protected $fillable = [
        'nama_dokumen',
        'file_dokumen',
        'penggalang_dana_id',
        'is_public',
        'storage_disk',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function penggalangDana()
    {
        return $this->belongsTo(Penggalang_Dana::class, 'penggalang_dana_id');
    }

    /**
     * Scope untuk dokumen yang diizinkan diakses publik:
     * - Hanya dokumen organisasi yang explicitly public
     * - Dokumen identitas pribadi (KTP/NPWP/Individu) TIDAK BOLEH muncul di scope ini.
     */
    public function scopePublicOnly(Builder $query): Builder
    {
        return $query->whereHas('penggalangDana', function ($q) {
            $q->where('jenis_penggalang', 'organisasi');
        })->where(function ($q) {
            // Jika kolom is_public ada dan bernilai true
            $q->where('is_public', true)
              // Atau untuk backward-compatibility pada data organisasi non-identitas
              ->orWhere(function ($sub) {
                  $sub->whereNull('is_public')
                      ->whereRaw("LOWER(nama_dokumen) NOT LIKE '%ktp%'")
                      ->whereRaw("LOWER(nama_dokumen) NOT LIKE '%nik%'")
                      ->whereRaw("LOWER(nama_dokumen) NOT LIKE '%identitas%'");
              });
        });
    }

    /**
     * Periksa apakah dokumen ini bersifat publik untuk pengunjung umum (Guest).
     */
    public function isPublicForVisitor(): bool
    {
        // Dokumen individu WAJIB 100% PRIVATE
        if ($this->penggalangDana && $this->penggalangDana->jenis_penggalang === 'individu') {
            return false;
        }

        // Cek nama dokumen terhadap data identitas sensitif
        $namaLower = strtolower($this->nama_dokumen ?? '');
        foreach (['ktp', 'nik', 'identitas', 'paspor', 'sim', 'kk'] as $sensitiveWord) {
            if (str_contains($namaLower, $sensitiveWord)) {
                return false;
            }
        }

        // Jika eksplisit is_public = true dan organisasi
        if ($this->is_public === true) {
            return true;
        }

        return false;
    }

    /**
     * Cek otorisasi akses:
     * - Admin: boleh akses semua
     * - Pemilik: boleh akses dokumen miliknya sendiri
     * - Pengunjung/Publik: hanya jika dokumen berstatus publik
     */
    public function canBeAccessedBy(?User $user): bool
    {
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if ($user && $this->penggalangDana && (int) $this->penggalangDana->user_id === (int) $user->id) {
            return true;
        }

        return $this->isPublicForVisitor();
    }

    /**
     * URL aman untuk melihat/mengunduh dokumen melalui secure proxy endpoint
     */
    public function getSecureViewUrlAttribute(): string
    {
        return route('dokumen.show', $this->id);
    }
}
