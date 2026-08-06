<?php
/**
 * Nadics LectureHub — Dashboard Button & Navigation Functional Test
 * Verifies all sidebar routes resolve and controllers exist.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

$passed = 0;
$failed = 0;

function check(string $label, bool $result): void {
    global $passed, $failed;
    if ($result) {
        echo "  [PASS] $label\n";
        $passed++;
    } else {
        echo "  [FAIL] $label\n";
        $failed++;
    }
}

echo "==================================================\n";
echo "Nadics LectureHub — Dashboard & Navigation Test\n";
echo "==================================================\n\n";

// 1. Check all controller classes exist and are auto-loadable
$controllers = [
    'App\\Controllers\\CourseController'       => 'CourseController',
    'App\\Controllers\\AttendanceController'   => 'AttendanceController',
    'App\\Controllers\\NotificationController' => 'NotificationController',
    'App\\Controllers\\AnalyticsController'    => 'AnalyticsController',
    'App\\Controllers\\LectureController'      => 'LectureController',
    'App\\Controllers\\MaterialController'     => 'MaterialController',
    'App\\Controllers\\AssignmentController'   => 'AssignmentController',
    'App\\Controllers\\StreamController'       => 'StreamController',
    'App\\Controllers\\DashboardController'    => 'DashboardController',
];

foreach ($controllers as $class => $label) {
    check("Controller exists: {$label}", class_exists($class));
}

echo "\n";

// 2. Check all new view files exist
$views = [
    'courses/index.php'       => 'Courses index view',
    'attendance/index.php'    => 'Attendance index view',
    'notifications/index.php' => 'Notifications index view',
    'analytics/index.php'     => 'Analytics index view',
    'dashboard/index.php'     => 'Dashboard index view (updated)',
    'components/sidebar.php'  => 'Sidebar component (updated)',
    'admin/audit_logs/index.php' => 'Audit logs index view',
    'admin/settings/index.php'   => 'Platform settings view',
];

foreach ($views as $path => $label) {
    $fullPath = BASE_PATH . '/resources/views/' . $path;
    check("View file exists: {$label}", file_exists($fullPath));
}

echo "\n";

// 3. Check routes file contains all required route paths
$routesContent = file_get_contents(BASE_PATH . '/routes/web.php');
$requiredRoutes = [
    '/courses'       => 'GET /courses route',
    '/attendance'    => 'GET /attendance route',
    '/notifications' => 'GET /notifications route',
    '/analytics'     => 'GET /analytics route',
    '/reports'       => 'GET /reports route',
    '/lectures'      => 'GET /lectures route',
    '/materials'     => 'GET /materials route',
    '/admin/users'   => 'GET /admin/users route',
    '/admin/audit-logs' => 'GET /admin/audit-logs route',
    '/admin/faculties' => 'GET /admin/faculties route',
    '/admin/departments' => 'GET /admin/departments route',
    '/admin/courses' => 'GET /admin/courses route',
    '/admin/settings' => 'GET /admin/settings route',
];

foreach ($requiredRoutes as $path => $label) {
    check("Route registered: {$label}", str_contains($routesContent, "'{$path}'"));
}

echo "\n";

// 4. Check sidebar has NO remaining href="#" for nav links
$sidebar = file_get_contents(BASE_PATH . '/resources/views/components/sidebar.php');
$hrefHashInLinks = preg_match_all('/href="#"/', $sidebar, $m);
check("Sidebar has 0 dead href=\"#\" links", $hrefHashInLinks === 0);

// 5. Check dashboard has minimal dead links
$dashboard = file_get_contents(BASE_PATH . '/resources/views/dashboard/index.php');
$deadLinks = preg_match_all('/href="#"/', $dashboard, $m2);
check("Dashboard has 0 dead href=\"#\" links", $deadLinks === 0);

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n\n";
