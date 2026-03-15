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

    'gateway1' => [
        'base_url' => env('GATEWAY1_BASE_URL', 'http://localhost:3001'),
        'email' => env('GATEWAY1_EMAIL', 'dev@betalent.tech'),
        'token' => env('GATEWAY1_TOKEN', 'FEC9BB078BF338F464F96B48089EB498'),
    ],

    'gateway2' => [
        'base_url' => env('GATEWAY2_BASE_URL', 'http://localhost:3002'),
        'token' => env('GATEWAY2_TOKEN'),
        'secret' => env('GATEWAY2_SECRET'),
    ],

];
