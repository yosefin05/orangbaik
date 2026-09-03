<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SyaratKetentuan extends Model
{
    protected $table = 'syarat_ketentuan';

    protected $fillable = [
        'judul',
        'isi',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('urutan')->orderBy('id');
    }

    public function paragraphs(): array
    {
        return collect(preg_split("/\r\n|\n|\r/", (string) $this->isi))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
