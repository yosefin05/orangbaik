<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Fundraiser extends Model
{
    protected $table = 'campaign_fundraiser';

    protected $fillable = [
        'user_id',
        'campaign_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}