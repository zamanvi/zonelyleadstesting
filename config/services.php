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

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

    'telnyx' => [
        'api_key' => env('TELNYX_API_KEY'),
        'from'    => env('TELNYX_FROM'),
    ],

    // ── Marketing Analytics ────────────────────────────────────────
    // Set these environment variables on Railway to activate tracking.
    'analytics' => [
        'ga4_id'      => env('GOOGLE_ANALYTICS_ID'),   // e.g. G-XXXXXXXXXX
        'fb_pixel_id' => env('FACEBOOK_PIXEL_ID'),     // e.g. 123456789012345
        'clarity_id'  => env('MICROSOFT_CLARITY_ID'),  // e.g. abcdefghij
    ],

];
