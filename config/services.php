<?php

return [

    'mercadopago' => [
        // Aquí decidimos qué access_token usar basado en MERCADOPAGO_ENV
        'access_token' => env('MERCADOPAGO_ENV') === 'production'
            ? env('MERCADOPAGO_ACCESS_TOKEN_PROD')
            : env('MERCADOPAGO_ACCESS_TOKEN_SANDBOX'),

        // Aquí decidimos qué public_key usar basado en MERCADOPAGO_ENV
        'public_key' => env('MERCADOPAGO_ENV') === 'production'
            ? env('MERCADOPAGO_PUBLIC_KEY_PROD')
            : env('MERCADOPAGO_PUBLIC_KEY_SANDBOX'),

        'env' => env('MERCADOPAGO_ENV', 'sandbox'), // Esto aún se lee para otras lógicas
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];