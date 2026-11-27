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
     * Dokumen verifikasi tidak pernah ditampilkan kepada pengunjung umum.
     * Dokumen hanya dapat diakses melalui endpoint terproteksi oleh pemilik
     * dokumen dan admin yang berwenang.
     */
    public function scopePublicOnly(Builder $query): Builder
    {
        return $query->whereRaw('1 = 0');
    }

    /**
     * Dokumen verifikasi selalu privat, termasuk dokumen legalitas organisasi.
     */
    public function isPublicForVisitor(): bool
    {
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

        // Dokumen verifikasi hanya boleh dibuka admin. Pemilik tetap dapat
        // melihat metadata/status melalui dashboard, bukan file identitas/legalitas.
        return false;
    }

    /**
     * URL aman untuk melihat/mengunduh dokumen melalui secure proxy endpoint
     */
    public function getSecureViewUrlAttribute(): string
    {
        return route('dokumen.show', $this->id);
    }
}
