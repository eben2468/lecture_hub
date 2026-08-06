<?php
/**
 * ============================================================
 * Nadics LectureHub — Security Configuration
 * ============================================================
 */

return [
    'csrf' => [
        'token_lifetime' => env('CSRF_TOKEN_LIFETIME', 3600),
        'excluded_uris'  => [
            '/api/*',     // API uses token auth
            '/webhooks/*', // Webhooks use signatures
        ],
    ],

    'rate_limit' => [
        'max_attempts' => env('RATE_LIMIT_MAX', 60),
        'window'       => env('RATE_LIMIT_WINDOW', 60), // seconds
        'login'        => [
            'max_attempts' => 5,
            'window'       => 300, // 5 minutes
        ],
        'api'          => [
            'max_attempts' => env('API_RATE_LIMIT', 120),
            'window'       => 60,
        ],
    ],

    'password' => [
        'min_length'  => env('PASSWORD_MIN_LENGTH', 8),
        'bcrypt_cost' => env('BCRYPT_COST', 12),
        'require'     => [
            'uppercase'  => true,
            'lowercase'  => true,
            'number'     => true,
            'special'    => false,
        ],
    ],

    'session' => [
        'lifetime'  => env('SESSION_LIFETIME', 120),
        'secure'    => env('SESSION_SECURE', false),
        'http_only' => env('SESSION_HTTPONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'Lax'),
    ],

    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options'        => 'SAMEORIGIN',
        'X-XSS-Protection'       => '1; mode=block',
        'Referrer-Policy'         => 'strict-origin-when-cross-origin',
    ],
];
