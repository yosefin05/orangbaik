<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Gambar extends Model
{
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    protected $table = 'campaign_gambar';
    protected $fillable = [
        'foto',
        'campaign_id'
    ];
}
