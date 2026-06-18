<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penggalang_Dana_Dokumen extends Model
{
    public function penggalangDana()
    {
        return $this->belongsTo(Penggalang_Dana::class);
    }
    protected $table = 'penggalang_dana_dokumen';
    protected $fillable = [
        'nama_dokumen',
        'file_dokumen',
        'penggalang_dana_id'
    ];
}
