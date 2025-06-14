<?php

// config/services.php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
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

    // =========================================================
    // CONFIGURACIÓN DE MERCADO PAGO - ¡CRÍTICO!
    // =========================================================
    'mercadopago' => [
        // Selecciona el token de acceso basado en la variable MERCADOPAGO_ENV
        'access_token' => env('MERCADOPAGO_ENV') === 'production'
            ? env('MERCADOPAGO_ACCESS_TOKEN_PRODUCTION')  // Usa el token de PRODUCCIÓN
            : env('MERCADOPAGO_ACCESS_TOKEN_SANDBOX'), // Usa el token de SANDBOX

        // Selecciona la clave pública basada en la variable MERCADOPAGO_ENV
        'public_key' => env('MERCADOPAGO_ENV') === 'production'
            ? env('MERCADOPAGO_PUBLIC_KEY_PRODUCTION')    // Usa la clave pública de PRODUCCIÓN
            : env('MERCADOPAGO_PUBLIC_KEY_SANDBOX'),   // Usa la clave pública de SANDBOX

        // Almacena el entorno actual (production/sandbox).
        // Esto es útil si necesitas acceder al entorno configurado desde otros lugares.
        'env' => env('MERCADOPAGO_ENV', 'sandbox'), // 'sandbox' como valor por defecto si no está definido en .env
    ],

];