<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Gallery extends Model
{
    protected $table = 'campaign_gallery';
    protected $fillable = [
        'campaign_id',
        'foto'
    ];
}
