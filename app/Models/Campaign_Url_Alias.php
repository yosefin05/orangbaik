<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Url_Alias extends Model
{
    protected $table = 'campaign_url_alias';
    protected $fillable = ['campaign_id', 'custom_slug'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
