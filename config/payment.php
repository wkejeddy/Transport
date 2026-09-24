<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway Mode
    |--------------------------------------------------------------------------
    | Supported: 'sandbox', 'orange_money', 'mtn_momo', 'campay', 'notchpay'
    */
    'default_mode' => env('PAYMENT_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Orange Money Cameroon API Credentials
    | https://developer.orange.com
    |--------------------------------------------------------------------------
    */
    'orange_money' => [
        'client_id' => env('ORANGE_MONEY_CLIENT_ID', ''),
        'client_secret' => env('ORANGE_MONEY_CLIENT_SECRET', ''),
        'merchant_key' => env('ORANGE_MONEY_MERCHANT_KEY', ''),
        'base_url' => env('ORANGE_MONEY_BASE_URL', 'https://api.orange.com/orange-money-webpay/cm/v1'),
        'return_url' => env('APP_URL') . '/payment/callback/orange',
        'cancel_url' => env('APP_URL') . '/payment/cancel',
    ],

    /*
    |--------------------------------------------------------------------------
    | MTN Mobile Money (MoMo) Cameroon API
    | https://momodeveloper.mtn.com
    |--------------------------------------------------------------------------
    */
    'mtn_momo' => [
        'primary_key' => env('MTN_MOMO_PRIMARY_KEY', ''),
        'user_id' => env('MTN_MOMO_USER_ID', ''),
        'api_key' => env('MTN_MOMO_API_KEY', ''),
        'target_environment' => env('MTN_MOMO_TARGET_ENV', 'sandbox'), // 'sandbox' or 'live'
        'base_url' => env('MTN_MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com'),
        'callback_url' => env('APP_URL') . '/api/v1/payments/webhook/mtn',
    ],

    /*
    |--------------------------------------------------------------------------
    | Campay Aggregator (Handles both OM + MTN MoMo in Cameroon)
    | https://www.campay.net
    |--------------------------------------------------------------------------
    */
    'campay' => [
        'app_username' => env('CAMPAY_APP_USERNAME', ''),
        'app_password' => env('CAMPAY_APP_PASSWORD', ''),
        'environment' => env('CAMPAY_ENV', 'demo'), // 'demo' or 'live'
        'webhook_key' => env('CAMPAY_WEBHOOK_KEY', ''),
    ],

];
