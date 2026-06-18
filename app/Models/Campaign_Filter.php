<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Filter extends Model
{
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    public function filter()
    {
        return $this->belongsTo(Filter::class);
    }
    protected $table = 'campaign_filter';
    protected $fillable = [
        'campaign_id',
        'filter_id'
    ];
}
