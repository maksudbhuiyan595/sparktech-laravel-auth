<?php

declare(strict_types=1);

return [
    'prefix' => env('SPARKTECH_AUTH_PREFIX', 'auth'),

    'middleware' => ['api'],

    'user_model' => App\Models\User::class,

    'guard' => 'sanctum',

    'token_expires_in' => env('SPARKTECH_AUTH_TOKEN_EXPIRATION', null),

    'password' => [
        'min_length' => 8,
        'require_mixed_case' => false,
        'require_number' => false,
        'require_symbol' => false,
    ],

    'otp' => [
        'expires_in' => 5,
        'max_attempts' => 5,
        'resend_cooldown' => 60,
        'length' => 6,
    ],

    'social' => [
        'enabled' => true,
        'providers' => [
            'google' => [
                'enabled' => (bool) env('SPARKTECH_AUTH_GOOGLE_ENABLED', false),
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/social/google/callback'),
            ],
            'apple' => [
                'enabled' => (bool) env('SPARKTECH_AUTH_APPLE_ENABLED', false),
                'client_id' => env('APPLE_CLIENT_ID'),
                'client_secret' => env('APPLE_CLIENT_SECRET'),
                'redirect' => env('APPLE_REDIRECT_URI', '/auth/social/apple/callback'),
            ],
            'facebook' => ['enabled' => false],
            'github' => ['enabled' => false],
            'gitlab' => ['enabled' => false],
            'linkedin' => ['enabled' => false],
            'bitbucket' => ['enabled' => false],
        ],
    ],

    'notifications' => [
        'login_alerts' => true,
        'password_changed' => true,
        'new_device' => true,
    ],
];
