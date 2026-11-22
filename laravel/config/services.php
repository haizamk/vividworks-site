<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'admin' => [
        'token' => env('ADMIN_TOKEN', ''),
    ],

    'vividai' => [
        'callback_token' => env('VIVIDAI_CALLBACK_TOKEN', ''),
        'callback_max_bytes' => (int) env('VIVIDAI_CALLBACK_MAX_BYTES', 20 * 1024),
        'design_agent_url' => env('VIVIDAI_DESIGN_AGENT_URL', ''),
        'design_agent_header' => env('VIVIDAI_DESIGN_AGENT_HEADER', ''),
        'design_agent_token' => env('VIVIDAI_DESIGN_AGENT_TOKEN', ''),
        'design_agent_timeout' => (int) env('VIVIDAI_DESIGN_AGENT_TIMEOUT', 120),
    ],

];
