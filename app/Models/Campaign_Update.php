<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Update extends Model
{
    protected $table = 'campaign_update';
    protected $fillable = [
        'campaign_id',
        'isi_update'
    ];
}
