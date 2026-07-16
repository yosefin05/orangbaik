<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    public function campaign()
    {
        return $this->belongsToMany(
            Campaign::class,
            'campaign_filter',
            'filter_id',
            'campaign_id'
        );
    }
    public function campaign_filter()
    {
        return $this->hasMany(Campaign_Filter::class);
    }
    protected $table = 'filter';
    protected $fillable = [
        'nama_filter',
        'slug'
    ];
}
