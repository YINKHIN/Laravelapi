<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
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
        ],
        'r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => 'auto',
            'bucket' => env('R2_BUCKET', 'ims'),
            'url' => env('R2_URL'),  // ឧ. https://pub-9c6a5c173845a74ae4b278f19.r2.dev/
            'endpoint' => env('R2_ENDPOINT', 'https://10a85e4740c137ac7e5c340a17fb5341.r2.cloudflared.com'),  // Account ID របស់អ្នក
            'use_path_style_endpoint' => true,
            'options' => [
                'ServerSideEncryption' => 'AES256',
            ],
        ],
    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];