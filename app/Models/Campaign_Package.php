<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Package extends Model
{
    
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    protected $table = 'campaign_package';

    protected $fillable = [
        'campaign_id',
        'judul',
        'deskripsi',
        'nominal',
        'gambar',
    ];
}