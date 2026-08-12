<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    'video_disk' => env('VIDEO_DISK', 'private_media'),
    
    'video_stream_ttl_seconds' => (int) env('VIDEO_STREAM_TTL_SECONDS', 600),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/') . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Private Media Disk
        |--------------------------------------------------------------------------
        |
        | Used for protected course media such as lesson videos.
        | Files in this disk must not be exposed through public/storage.
        | The database should store only relative paths, for example:
        | videos/react-elearning/react-elearning-01-example.mp4
        |
        */

        'private_media' => [
            'driver' => 'local',
            'root' => env('PRIVATE_MEDIA_ROOT', storage_path('app/private')),
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Only public storage should be linked to public/storage.
    | Do not create symbolic links for private_media.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
