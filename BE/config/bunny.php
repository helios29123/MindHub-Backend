<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bunny.net Stream Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and CDN endpoints for Bunny Stream video delivery.
    |
    */
    'stream' => [
        'library_id' => env('BUNNY_STREAM_LIBRARY_ID', '724015'),
        'cdn_hostname' => env('BUNNY_STREAM_CDN_HOSTNAME', 'vz-725f19ee-511.b-cdn.net'),
        'api_key' => env('BUNNY_STREAM_API_KEY', ''),
    ],
];
