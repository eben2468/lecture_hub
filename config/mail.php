<?php
/**
 * ============================================================
 * Nadics LectureHub — Mail Configuration
 * ============================================================
 */

return [
    'driver'     => env('MAIL_DRIVER', 'smtp'),
    'host'       => env('MAIL_HOST', 'smtp.mailtrap.io'),
    'port'       => env('MAIL_PORT', 587),
    'username'   => env('MAIL_USERNAME', ''),
    'password'   => env('MAIL_PASSWORD', ''),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'from'       => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@nadicslecturehub.com'),
        'name'    => env('MAIL_FROM_NAME', 'Nadics LectureHub'),
    ],
];
