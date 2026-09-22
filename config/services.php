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

    // Facebook Lead Ads → CRM webhook. Point the ad's "Connect your CRM"
    // integration at /webhooks/facebook/leads. The verify token is the string
    // you type into Facebook when it asks you to verify the callback URL. The
    // page access token (optional but recommended) lets the webhook pull the
    // full lead field data (name, phone, ...) from the Graph API.
    'facebook_leads' => [
        'verify_token' => env('FB_LEADS_VERIFY_TOKEN'),
        'page_access_token' => env('FB_LEADS_PAGE_TOKEN'),
        'app_secret' => env('FB_APP_SECRET'),
        'graph_version' => env('FB_GRAPH_VERSION', 'v21.0'),
    ],

    // CARTO basemap tiles (client-side map tiles). CARTO now gates their public
    // basemaps behind an API key; this key is appended to the tile URLs.
    'carto' => [
        'api_key' => env('CARTO_API_KEY'),
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
