<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Update_Gambar extends Model
{
    protected $table = 'campaign_update_gambar';

    protected $fillable = [
        'gambar_update',
        'campaign_update_id'
    ];

    public function campaign_update()
    {
        return $this->belongsTo(Campaign_Update::class, 'campaign_update_id');
    }
}