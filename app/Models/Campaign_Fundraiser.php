<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Fundraiser extends Model
{
    protected $table = 'campaign_fundraiser';
    protected $fillable = [
        'campaign_id',
        'user_id',
    ];  
}
