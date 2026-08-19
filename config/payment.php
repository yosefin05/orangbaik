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
    | Midtrans Configuration
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
    | Flip Configuration
    |--------------------------------------------------------------------------
    */
    'flip' => [
        'api_key'             => env('FLIP_API_KEY'),
        'is_production'       => env('FLIP_IS_PRODUCTION', false),
        'base_url_sandbox'    => 'https://bigflip.id/big_sandbox_api',
        'base_url_production' => 'https://bigflip.id/api',
        'webhook_token'       => env('FLIP_WEBHOOK_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tripay Configuration
    |--------------------------------------------------------------------------
    */
    'tripay' => [
        'api_key'             => env('TRIPAY_API_KEY'),
        'private_key'         => env('TRIPAY_PRIVATE_KEY'),
        'merchant_code'       => env('TRIPAY_MERCHANT_CODE'),
        'is_production'       => env('TRIPAY_IS_PRODUCTION', false),
        'base_url_sandbox'    => 'https://tripay.co.id/api-sandbox',
        'base_url_production' => 'https://tripay.co.id/api',
    ],

    /*
    |--------------------------------------------------------------------------
    | iPaymu Configuration
    |--------------------------------------------------------------------------
    */
    'ipaymu' => [
        'api_key'             => env('IPAYMU_API_KEY'),
        'va'                  => env('IPAYMU_VA'),
        'is_production'       => env('IPAYMU_IS_PRODUCTION', false),
        'base_url_sandbox'    => 'https://sandbox.ipaymu.com/api/v2',
        'base_url_production' => 'https://my.ipaymu.com/api/v2',
    ],

];
