<?php
/**
 * ============================================================
 * Nadics LectureHub — Third-Party Services Configuration
 * ============================================================
 */

return [
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'model'   => 'gpt-4',
    ],

    'webrtc' => [
        'stun_server'     => env('WEBRTC_STUN_SERVER', 'stun:stun.l.google.com:19302'),
        'turn_server'     => env('WEBRTC_TURN_SERVER', ''),
        'turn_username'   => env('WEBRTC_TURN_USERNAME', ''),
        'turn_credential' => env('WEBRTC_TURN_CREDENTIAL', ''),
    ],

    'sms' => [
        'driver'    => env('SMS_DRIVER', ''),
        'api_key'   => env('SMS_API_KEY', ''),
        'sender_id' => env('SMS_SENDER_ID', 'SLMS'),
    ],
];
