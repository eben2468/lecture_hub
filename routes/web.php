<?php
/**
 * ============================================================
 * Nadics LectureHub — Web Routes
 * ============================================================
 *
 * All web route definitions for the application.
 * Routes are registered on the $router instance
 * (an instance of Core\Router).
 *
 * @var \Core\Router $router
 * ============================================================
 */

use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RateLimitMiddleware;

// ============================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================

/** Landing Page */
$router->get('/', 'HomeController@index')->name('home');

/** About & Info Pages */
$router->get('/about', 'HomeController@about')->name('about');
$router->get('/contact', 'HomeController@contact')->name('contact');
$router->get('/features', 'HomeController@features')->name('features');
$router->get('/pricing', 'HomeController@pricing')->name('pricing');
$router->get('/demo', 'HomeController@demo')->name('demo');
$router->get('/api-docs', 'HomeController@apiDocs')->name('api.docs');
$router->get('/careers', 'HomeController@careers')->name('careers');
$router->get('/blog', 'HomeController@blog')->name('blog');
$router->get('/help', 'HomeController@help')->name('help');
$router->get('/docs', 'HomeController@docs')->name('docs');
$router->get('/status', 'HomeController@status')->name('status');
$router->get('/community', 'HomeController@community')->name('community');
$router->get('/privacy', 'HomeController@privacy')->name('privacy');
$router->get('/terms', 'HomeController@terms')->name('terms');
$router->get('/data-policy', 'HomeController@dataPolicy')->name('data_policy');

// ============================================================
// AUTHENTICATION ROUTES (Guest only)
// ============================================================

$router->group(['middleware' => [GuestMiddleware::class, RateLimitMiddleware::class, CsrfMiddleware::class]], function ($router) {
    $router->get('/login', 'AuthController@showLogin')->name('login');
    $router->post('/login', 'AuthController@login')->name('login.post');
    $router->get('/register', 'AuthController@showRegister')->name('register');
    $router->post('/register', 'AuthController@register')->name('register.post');
    $router->get('/forgot-password', 'AuthController@showForgotPassword')->name('password.forgot');
    $router->post('/forgot-password', 'AuthController@forgotPassword')->name('password.forgot.post');
    $router->get('/reset-password/{token}', 'AuthController@showResetPassword')->name('password.reset');
    $router->post('/reset-password', 'AuthController@resetPassword')->name('password.reset.post');
});

// ============================================================
// AUTHENTICATED ROUTES (Require login)
// ============================================================

$router->group(['middleware' => [AuthMiddleware::class, CsrfMiddleware::class]], function ($router) {
    /** Logout */
    $router->post('/logout', 'AuthController@logout')->name('logout');

    /** Dashboard */
    $router->get('/dashboard', 'DashboardController@index')->name('dashboard');
    $router->get('/timetable', 'TimetableController@index')->name('timetable');

    /** Profile */
    $router->get('/profile', 'ProfileController@show')->name('profile');
    $router->post('/profile', 'ProfileController@update')->name('profile.update');
    $router->post('/profile/photo', 'ProfileController@updatePhoto')->name('profile.photo');
    $router->post('/profile/password', 'ProfileController@updatePassword')->name('profile.password');

    // ========================================================
    // COURSES & ENROLLMENTS
    // ========================================================
    $router->get('/courses', 'CourseController@index')->name('courses');
    $router->get('/courses/{id}/enrollments', 'CourseController@enrollments')->name('courses.enrollments');
    $router->post('/courses/{id}/enroll', 'CourseController@enroll')->name('courses.enroll');
    $router->post('/courses/{id}/enroll/bulk', 'CourseController@bulkEnroll')->name('courses.enroll.bulk');
    $router->post('/enrollments/{id}/drop', 'CourseController@drop')->name('courses.drop');

    // ========================================================
    // ATTENDANCE
    // ========================================================
    $router->get('/attendance', 'AttendanceController@index')->name('attendance');

    // ========================================================
    // NOTIFICATIONS & ATTENDANCE
    // ========================================================
    $router->get('/notifications', 'NotificationController@index')->name('notifications');
    $router->post('/notifications/{id}/read', 'NotificationController@markRead')->name('notifications.mark_read');
    $router->post('/notifications/mark-all-read', 'NotificationController@markAllRead')->name('notifications.mark_all_read');
    $router->post('/notifications/{id}/delete', 'NotificationController@delete')->name('notifications.delete');
    $router->get('/attendance', 'AttendanceController@index')->name('attendance');
    $router->post('/attendance/generate-qr', 'AttendanceController@generateQr')->name('attendance.generate_qr');
    $router->post('/attendance/verify', 'AttendanceController@verify')->name('attendance.verify');
    $router->get('/attendance/verify', 'AttendanceController@verifyPage')->name('attendance.verify_page');

    // ========================================================
    // ANALYTICS & REPORTS
    // ========================================================
    $router->get('/analytics', 'AnalyticsController@index')->name('analytics');
    $router->get('/reports', 'AnalyticsController@index')->name('reports');
    $router->get('/reports/attendance/csv', 'AnalyticsController@exportAttendanceCSV')->name('reports.attendance.csv');

    // ========================================================
    // LECTURE MANAGEMENT ROUTES
    // ========================================================
    $router->get('/lectures', 'LectureController@index')->name('lectures');
    $router->get('/lectures/create', 'LectureController@create')->name('lectures.create');
    $router->post('/lectures', 'LectureController@store')->name('lectures.store');
    $router->get('/lectures/{id}', 'LectureController@show')->name('lectures.show');
    $router->get('/lectures/{id}/transcript', 'AiController@showTranscript')->name('lectures.transcript');
    $router->post('/lectures/{id}/status', 'LectureController@updateStatus')->name('lectures.status');
    $router->get('/ai-assistant', 'AiController@assistant')->name('ai.assistant');
    $router->post('/ai-assistant/chat', 'AiController@chat')->name('ai.chat');

    // ========================================================
    // COURSE MATERIALS ROUTES
    // ========================================================
    $router->get('/materials', 'MaterialController@index')->name('materials');
    $router->post('/materials', 'MaterialController@store')->name('materials.store');
    $router->get('/materials/{id}/download', 'MaterialController@download')->name('materials.download');
    $router->get('/materials/{id}/preview', 'MaterialController@preview')->name('materials.preview');
    $router->post('/materials/{id}/delete', 'MaterialController@destroy')->name('materials.delete');
    $router->delete('/materials/{id}/delete', 'MaterialController@destroy');
    $router->delete('/materials/{id}', 'MaterialController@destroy');

    // ========================================================
    // ASSIGNMENT ROUTES
    // ========================================================
    $router->get('/assignments', 'AssignmentController@index')->name('assignments');
    $router->post('/assignments', 'AssignmentController@store')->name('assignments.store');
    $router->post('/assignments/{id}/submit', 'AssignmentController@submit')->name('assignments.submit');
    $router->get('/assignments/{id}/submissions', 'AssignmentController@submissions')->name('assignments.submissions');
    $router->post('/assignments/{id}/publish', 'AssignmentController@publish')->name('assignments.publish');
    $router->post('/assignments/grade/{id}', 'AssignmentController@grade')->name('assignments.grade');

    // ========================================================
    // QUIZZES ROUTES
    // ========================================================
    $router->get('/quizzes', 'QuizController@index')->name('quizzes');
    $router->post('/quizzes', 'QuizController@store')->name('quizzes.store');
    $router->get('/quizzes/{id}', 'QuizController@show')->name('quizzes.show');
    $router->post('/quizzes/{id}/submit', 'QuizController@submit')->name('quizzes.submit');
    $router->post('/quizzes/{id}/questions', 'QuizController@addQuestion')->name('quizzes.add_question');
    $router->post('/quizzes/{id}/questions/{questionId}/delete', 'QuizController@deleteQuestion')->name('quizzes.questions.delete');
    $router->post('/quizzes/{id}/publish', 'QuizController@publish')->name('quizzes.publish');
    $router->post('/quizzes/{id}/delete', 'QuizController@destroy')->name('quizzes.delete');
    $router->delete('/quizzes/{id}', 'QuizController@destroy');

    // ========================================================
    // LIVE AUDIO STREAMING & BROADCASTING ROUTES
    // ========================================================
    $router->get('/stream/broadcaster/{id}', 'StreamController@broadcaster')->name('stream.broadcaster');
    $router->get('/stream/listener/{id}', 'StreamController@listener')->name('stream.listener');
    $router->get('/stream/recordings', 'StreamController@recordings')->name('stream.recordings');
    $router->get('/stream/active-broadcasts', 'StreamController@activeBroadcasts')->name('stream.active_broadcasts');
    $router->get('/stream/recordings/{id}/download', 'StreamController@downloadRecording')->name('stream.recordings.download');
    $router->post('/stream/{id}/start', 'StreamController@startStream')->name('stream.start');
    $router->post('/stream/{id}/stop', 'StreamController@stopStream')->name('stream.stop');
    $router->post('/stream/{id}/ping', 'StreamController@pingStream')->name('stream.ping');
    $router->post('/stream/{id}/upload-recording', 'StreamController@uploadRecording')->name('stream.upload_recording');
    $router->post('/stream/recordings/{id}/delete', 'StreamController@deleteRecording')->name('stream.recordings.delete');

    // ========================================================
    // REAL-TIME LIVE CHAT & Q&A ROUTES
    // ========================================================
    $router->get('/chat/{id}', 'ChatController@index')->name('chat.index');
    $router->post('/chat/{id}/send', 'ChatController@send')->name('chat.send');
    $router->post('/chat/{id}/answered', 'ChatController@markAnswered')->name('chat.answered');

    // ========================================================
    // ADMINISTRATIVE ROUTES (Super Admin & University Admin)
    // ========================================================
    $router->get('/admin/universities', 'Admin\UniversityController@index')->name('admin.universities');
    $router->post('/admin/universities', 'Admin\UniversityController@store')->name('admin.universities.store');
    $router->post('/admin/universities/{id}', 'Admin\UniversityController@update')->name('admin.universities.update');

    $router->get('/admin/faculties', 'Admin\FacultyController@index')->name('admin.faculties');
    $router->post('/admin/faculties', 'Admin\FacultyController@store')->name('admin.faculties.store');

    $router->get('/admin/departments', 'Admin\DepartmentController@index')->name('admin.departments');
    $router->post('/admin/departments', 'Admin\DepartmentController@store')->name('admin.departments.store');

    $router->get('/admin/programmes', 'Admin\ProgrammeController@index')->name('admin.programmes');
    $router->post('/admin/programmes', 'Admin\ProgrammeController@store')->name('admin.programmes.store');
    $router->post('/admin/programmes/{id}', 'Admin\ProgrammeController@update')->name('admin.programmes.update');
    $router->post('/admin/programmes/{id}/delete', 'Admin\ProgrammeController@destroy')->name('admin.programmes.delete');

    $router->get('/admin/courses', 'Admin\CourseController@index')->name('admin.courses');
    $router->post('/admin/courses', 'Admin\CourseController@store')->name('admin.courses.store');
    $router->post('/admin/courses/{id}/allocate', 'Admin\CourseController@allocate')->name('admin.courses.allocate');
    $router->get('/admin/courses/{id}/allocate', 'Admin\CourseController@allocate');
    $router->post('/admin/courses/{id}/delete', 'Admin\CourseController@destroy')->name('admin.courses.delete');

    $router->get('/admin/course-allocations', 'Admin\CourseAllocationController@index')->name('admin.course-allocations');
    $router->post('/admin/course-allocations', 'Admin\CourseAllocationController@store')->name('admin.course-allocations.store');
    $router->post('/admin/course-allocations/{id}/delete', 'Admin\CourseAllocationController@destroy')->name('admin.course-allocations.delete');

    $router->get('/admin/lecturers', 'Admin\LecturerController@index')->name('admin.lecturers');
    $router->post('/admin/lecturers', 'Admin\LecturerController@store')->name('admin.lecturers.store');
    $router->post('/admin/lecturers/{id}/toggle', 'Admin\LecturerController@toggleStatus')->name('admin.lecturers.toggle');

    $router->get('/admin/students', 'Admin\StudentController@index')->name('admin.students');
    $router->post('/admin/students', 'Admin\StudentController@store')->name('admin.students.store');
    $router->post('/admin/students/{id}/toggle', 'Admin\StudentController@toggleStatus')->name('admin.students.toggle');

    $router->get('/admin/users', 'Admin\UserController@index')->name('admin.users');

    $router->get('/admin/audit-logs', 'Admin\AuditLogController@index')->name('admin.audit-logs');
    $router->get('/admin/settings', 'Admin\SettingsController@index')->name('admin.settings');
    $router->post('/admin/settings', 'Admin\SettingsController@store')->name('admin.settings.store');
});
