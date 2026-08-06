<?php
/**
 * ============================================================
 * Nadics LectureHub — Application Configuration
 * ============================================================
 */

return [
    'name'     => env('APP_NAME', 'Nadics LectureHub'),
    'env'      => env('APP_ENV', 'local'),
    'debug'    => env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Africa/Lagos'),
    'locale'   => env('APP_LOCALE', 'en'),
    'version'  => env('APP_VERSION', '1.0.0'),
    'key'      => env('APP_KEY', ''),

    'company'  => [
        'name'    => 'Nadics Solutions',
        'tagline' => 'Every Student Hears. Every Lecture Lives.',
        'email'   => 'info@nadicssolutions.com',
        'website' => 'https://nadicssolutions.com',
    ],

    'pagination' => [
        'per_page' => 15,
    ],
];
