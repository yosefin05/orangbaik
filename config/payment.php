<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Minimum Donation Amount
    |--------------------------------------------------------------------------
    | Minimum nominal donasi yang diterima sistem (dalam Rupiah).
    | Berlaku untuk semua metode pembayaran.
    */
    'minimum_amount' => (int) env('PAYMENT_MINIMUM_AMOUNT', 5000),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('PAYMENT_CURRENCY', 'IDR'),

    /*
    |--------------------------------------------------------------------------
    | Order ID Prefix
    |--------------------------------------------------------------------------
    | Prefix yang digunakan untuk membentuk order_id unik.
    */
    'order_id_prefix' => env('PAYMENT_ORDER_PREFIX', 'OB'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration (Gateway 1)
    |--------------------------------------------------------------------------
    */
    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY'),
        'client_key'    => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized'  => env('MIDTRANS_IS_SANITIZED', true),
        'is_3ds'        => env('MIDTRANS_IS_3DS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Flip Configuration (Gateway 2)
    |--------------------------------------------------------------------------
    */
    'flip' => [
        'api_key'             => env('FLIP_API_KEY'),
        'is_production'       => env('FLIP_IS_PRODUCTION', false),
        'base_url_sandbox'    => 'https://bigflip.id/big_sandbox_api',
        'base_url_production' => 'https://bigflip.id/api',
        'webhook_token'       => env('FLIP_WEBHOOK_TOKEN'),
    ],

];
