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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'containerd' => [
        'socket' => env('CONTAINERD_SOCKET', '/run/containerd/containerd.sock'),
        'namespace' => env('CONTAINERD_NAMESPACE', 'firecracker'),
        'kernel_path' => env('CONTAINERD_KERNEL_PATH', '/opt/firecracker/vmlinux'),
        'rootfs_path' => env('CONTAINERD_ROOTFS_PATH', '/opt/firecracker/rootfs'),
    ],

    'firecracker' => [
        'api_url' => env('API_FIRECRACKER_URL', 'http://127.0.0.1:5000'),
        'timeout' => env('API_FIRECRACKER_TIMEOUT', 5),
    ],
];
