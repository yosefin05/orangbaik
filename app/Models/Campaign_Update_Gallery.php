<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Update_Gallery extends Model
{
    protected $table = 'campaign_update_gallery';
    protected $fillable = [
        'campaign_update_id',
        'foto'
    ];
}
