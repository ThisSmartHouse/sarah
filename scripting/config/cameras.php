<?php

return [
    'backyard' => [
        'mjpeg_feed_url' => env('BACKYARD_LIVE_FEED_URL'),
        'still_feed_url' => env('BACKYARD_STILL_FEED_URL'),
        'user' => env('BACKYARD_CAMERA_USER'),
        'password' => env('BACKYARD_CAMERA_PASSWORD')
    ]
];
