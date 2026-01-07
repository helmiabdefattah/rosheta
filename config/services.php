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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'fcm' => [
        // Service account JSON file path (for FCM HTTP v1 API)
        'service_account_path' => env('FCM_SERVICE_ACCOUNT_PATH', storage_path('app/firebase-service-account.json')),
        
        // Web app configuration (for client-side Firebase SDK)
        'api_key' => env('FCM_API_KEY'),
        'auth_domain' => env('FCM_AUTH_DOMAIN'),
        'project_id' => env('FCM_PROJECT_ID'),
        'storage_bucket' => env('FCM_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FCM_MESSAGING_SENDER_ID'),
        'app_id' => env('FCM_APP_ID'),
        'vapid_key' => env('FCM_VAPID_KEY'),
    ],

];
