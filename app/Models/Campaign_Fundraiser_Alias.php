<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Fundraiser_Alias extends Model
{
    protected $table = 'campaign_fundraiser_alias';

    protected $fillable = ['campaign_fundraiser_id', 'alias'];

    public function fundraiser()
    {
        return $this->belongsTo(Campaign_Fundraiser::class, 'campaign_fundraiser_id');
    }
}
