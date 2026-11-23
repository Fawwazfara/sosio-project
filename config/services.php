<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
        // Jeda minimal (detik) antar pesan WhatsApp agar nomor tidak keblokir
        'delay' => (int) env('FONNTE_DELAY', 60),
    ],

    'payment' => [
        'driver' => env('PAYMENT_DRIVER', env('MIDTRANS_SERVER_KEY') ? 'midtrans' : 'sandbox'),
        'sandbox' => (bool) env('PAYMENT_SANDBOX', !env('MIDTRANS_IS_PRODUCTION', false)),
        'merchant_name' => env('PAYMENT_MERCHANT_NAME', 'MADRASAH AS-SAJJAAD'),
        'merchant_city' => env('PAYMENT_MERCHANT_CITY', 'INDONESIA'),
        'nmid' => env('PAYMENT_NMID', 'ID1026000881920'),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY', ''),
        'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'qris_acquirer' => env('MIDTRANS_QRIS_ACQUIRER', 'gopay'),
    ],

];
