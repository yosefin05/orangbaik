<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign_Update extends Model
{
    protected $table = 'campaign_update';

    protected $fillable = [
        'judul_update',
        'isi_update',
        'campaign_id',
        'user_id'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}