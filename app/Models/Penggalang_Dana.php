<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Penggalang_Dana extends Model
{
    const CATEGORY_ORGANISASI = 'organisasi';
    const CATEGORY_YAYASAN = 'yayasan_lembaga';
    const CATEGORY_KOMUNITAS = 'komunitas';
    const CATEGORY_INDIVIDU = 'individu';

    public static function getCategoryLabels(): array
    {
        return [
            self::CATEGORY_ORGANISASI => 'Organisasi',
            self::CATEGORY_YAYASAN => 'Yayasan/Lembaga',
            self::CATEGORY_KOMUNITAS => 'Komunitas',
            self::CATEGORY_INDIVIDU => 'Individu',
        ];
    }

    public static function getValidCategories(): array
    {
        return array_keys(self::getCategoryLabels());
    }

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
            'penggalang_dana_id',
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
        'nama_penggalang',
        'slug',
        'thumbnail',
        'foto_profil',
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
        'verified',
        'user_id',
        'verified_by',
        'verified_at',
        'catatan_verifikasi',
        'status_read',
        'revision_count',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'verified' => 'boolean',
        'status_read' => 'boolean',
        'revision_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->slug) && !empty($model->nama_penggalang)) {
                $base = Str::slug($model->nama_penggalang) ?: 'penggalang';
                $slug = $base;
                $count = 1;
                while (static::where('slug', $slug)->where('id', '!=', $model->id ?? 0)->exists()) {
                    $slug = $base . '-' . (++$count);
                }
                $model->slug = $slug;
            }
        });
    }

    public function getJenisPenggalangLabelAttribute(): string
    {
        return self::getCategoryLabels()[$this->jenis_penggalang] ?? ucfirst($this->jenis_penggalang ?? 'Individu');
    }

    public function isIndividu(): bool
    {
        return $this->jenis_penggalang === self::CATEGORY_INDIVIDU;
    }

    public function isOrganisasi(): bool
    {
        return $this->jenis_penggalang === self::CATEGORY_ORGANISASI;
    }

    public function isYayasan(): bool
    {
        return $this->jenis_penggalang === self::CATEGORY_YAYASAN;
    }

    public function isKomunitas(): bool
    {
        return $this->jenis_penggalang === self::CATEGORY_KOMUNITAS;
    }

    public function isEntity(): bool
    {
        return in_array($this->jenis_penggalang, [
            self::CATEGORY_ORGANISASI,
            self::CATEGORY_YAYASAN,
            self::CATEGORY_KOMUNITAS,
        ], true);
    }
}
