<?php
/**
 * ============================================================
 * Nadics LectureHub — API Routes (v1)
 * ============================================================
 *
 * REST API route definitions.
 * All routes in this file are prefixed with /api/v1
 * and return JSON responses.
 *
 * @var \Core\Router $router
 * ============================================================
 */

// ============================================================
// PUBLIC API ENDPOINTS
// ============================================================

/** Health check */
$router->get('/health', function () {
    json_response([
        'status'  => 'ok',
        'service' => 'Nadics LectureHub API',
        'version' => env('API_VERSION', 'v1'),
        'time'    => now(),
    ]);
});

/** API Info */
$router->get('/', function () {
    json_response([
        'name'    => 'Nadics LectureHub API',
        'version' => env('API_VERSION', 'v1'),
        'docs'    => url('/api/docs'),
        'endpoints' => [
            'auth'         => url('/api/v1/auth'),
            'students'     => url('/api/v1/students'),
            'lecturers'    => url('/api/v1/lecturers'),
            'courses'      => url('/api/v1/courses'),
            'lectures'     => url('/api/v1/lectures'),
            'attendance'   => url('/api/v1/attendance'),
            'assignments'  => url('/api/v1/assignments'),
            'results'      => url('/api/v1/results'),
            'notifications'=> url('/api/v1/notifications'),
        ],
    ]);
});

// ============================================================
// AUTHENTICATED API ENDPOINTS (Phase 2+)
// ============================================================

// API authentication and resource endpoints will be added
// as each module is implemented.
