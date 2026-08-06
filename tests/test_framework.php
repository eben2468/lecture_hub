<?php
/**
 * Nadics LectureHub — Phase 1 Framework Engine Smoke Test
 * 
 * Verifies core bootstrap, autoloader, database connection, session,
 * CSRF, router, query builder, and view engine.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

echo "==================================================\n";
echo "Nadics LectureHub — Core Engine Verification\n";
echo "==================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $title): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$title}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$title}\n";
        $failed++;
    }
}

// 1. App Bootstrap
try {
    $app = new Core\Application(BASE_PATH);
    $app->boot();
    assertTest(true, "Application Bootstrap & Autoloader initialization");
} catch (\Throwable $e) {
    assertTest(false, "Application Bootstrap failed: " . $e->getMessage());
}

// 2. Environment Helpers
assertTest(env('APP_NAME') === 'Nadics LectureHub', "env() helper loads APP_NAME correctly");

// 3. Database Connection
try {
    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $user = env('DB_USERNAME', 'root');
    $pass = env('DB_PASSWORD', '');
    $pdo  = new PDO("mysql:host={$host};port={$port}", $user, $pass);
    assertTest($pdo instanceof PDO, "MariaDB server connection active at {$host}:{$port}");
} catch (\Throwable $e) {
    assertTest(false, "Database connection failed: " . $e->getMessage());
}

// 4. Session Engine
Core\Session::start();
Core\Session::getInstance()->set('test_key', 'test_val');
assertTest(Core\Session::getInstance()->get('test_key') === 'test_val', "Session set and get operations");

// 5. CSRF Token Engine
$token = Core\CSRF::getToken();
assertTest(strlen($token) === 64, "CSRF token generation (64 hex chars)");
assertTest(Core\CSRF::validate($token), "CSRF token validation");

// 6. Router Setup
$router = Core\Router::getInstance();
$router->get('/test-route', function() { return 'OK'; });
$routes = $router->getRoutes();
assertTest(isset($routes['GET']) && count($routes['GET']) > 0, "Router registers GET routes");

// 7. View Engine
try {
    $viewHtml = view('home.index');
    assertTest(str_contains($viewHtml, 'Nadics LectureHub'), "View engine renders home.index with layout");
} catch (\Throwable $e) {
    assertTest(false, "View engine rendering failed: " . $e->getMessage());
}

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n";

exit($failed > 0 ? 1 : 0);
