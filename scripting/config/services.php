<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mperks' => [
        'username' => env('MPERKS_USERNAME'),
        'password' => env('MPERKS_PASSWORD')
    ],
    
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'smappee' => [
        'client_id' => env('SMAPPEE_CLIENT_ID'),
        'client_secret' => env('SMAPPEE_CLIENT_SECRET'),
        'username' => env('SMAPPEE_USERNAME'),
        'password' => env('SMAPPEE_PASSWORD'),
        'host' => env('SMAPPEE_HOST'),
        'local_password' => env('SMAPPEE_LOCAL_PASSWORD')
    ],
    'mqtt' => [
        'host' => 'localhost',
        'port' => 1883
    ],
    'catbot' => [
        'host' => env('CATBOT_HOST')
    ],
    'octoprint' => [
        'api_key' => env('OCTOPRINT_API_KEY')
    ],
    'lyric' => [
        'client_id' => env('LYRIC_CLIENT_ID'),
        'client_secret' => env('LYRIC_CLIENT_SECRET')
    ],
    'sparkpost' => [
        'secret' => env('SPARKPOST_API_KEY')
    ],
    'whistle' => [
        'username' => env('WHISTLE_USERNAME'),
        'password' => env('WHISTLE_PASSWORD')
    ],
    'rewardjet' => [
        'username' => env('REWARDJET_USERNAME'),
        'password' => env('REWARDJET_PASSWORD'),
        'base_url' => env('REWARDJET_BASEURL')
    ]
];
