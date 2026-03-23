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

    /*
    | Hugging Face Space: ibrahim444/bike (FastAPI — Bike Pothole Detector)
    | Docs in app: POST /predict with { "data": [[8 floats], ...] }
    | Public URL pattern: https://{user}-{space}.hf.space
    */
    'huggingface_bike' => [
        'base_url' => rtrim(env('HUGGINGFACE_BIKE_SPACE_URL', 'https://ibrahim444-bike.hf.space'), '/'),
        'token' => env('HUGGINGFACE_API_TOKEN'),
        'timeout' => (int) env('HUGGINGFACE_BIKE_TIMEOUT', 120),
    ],

];
