<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    public function campaign()
    {
        return $this->hasMany(Campaign::class);
    }
    protected $table = 'kategori';
    protected $fillable = [
        'nama_kategori',
        'slug'
    ];
}
