<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $fillable = [
        'donasi_id',
        'order_id',
        'snap_token',
        'payment_type',
        'transaction_id',
        'transaction_status',
        'paid_at',
    ];


    public function donasi()
    {
        return $this->belongsTo(Donasi::class);
    }
}