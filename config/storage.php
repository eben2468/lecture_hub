<?php
/**
 * ============================================================
 * Nadics LectureHub — Storage Configuration
 * ============================================================
 */

return [
    'driver'     => env('STORAGE_DRIVER', 'local'),
    'max_upload' => env('STORAGE_MAX_UPLOAD', 104857600), // 100MB
    'upload_path'=> env('UPLOAD_PATH', 'uploads'),

    'directories' => [
        'profiles'  => 'uploads/profiles',
        'lectures'  => 'uploads/lectures',
        'materials' => 'uploads/materials',
        'temp'      => 'uploads/temp',
    ],

    'allowed_mimes' => [
        'documents' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv'],
        'images'    => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'audio'     => ['mp3', 'wav', 'ogg', 'aac', 'm4a'],
        'video'     => ['mp4', 'webm', 'avi', 'mov', 'mkv'],
        'archives'  => ['zip', 'rar', '7z', 'tar', 'gz'],
    ],

    'max_sizes' => [
        'profile_photo' => 5242880,   // 5MB
        'document'      => 52428800,  // 50MB
        'audio'         => 209715200, // 200MB
        'video'         => 524288000, // 500MB
    ],
];
