<?php
/**
 * Nadics LectureHub — Phase 3 Auth & Security Verification Test
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

use Core\Auth;
use Core\QueryBuilder;

echo "==================================================\n";
echo "Nadics LectureHub — Phase 3 Auth Verification\n";
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

$auth = Auth::getInstance();

// 1. Attempt login with correct credentials
$loginSuccess = $auth->attempt('student@unilag.edu.ng', 'Password123!');
assertTest($loginSuccess === true, "Auth::attempt() with valid credentials");

// 2. Check Auth state
assertTest($auth->check() === true, "Auth::check() returns true when authenticated");
assertTest($auth->id() !== null, "Auth::id() returns valid user ID");

// 3. User details & role check
$user = $auth->user();
assertTest($user['email'] === 'student@unilag.edu.ng', "Auth::user() retrieves student data");
assertTest($auth->role() === 'student', "Auth::role() identifies student role");
assertTest($auth->hasRole('student') === true, "Auth::hasRole('student') returns true");
assertTest($auth->hasRole('lecturer') === false, "Auth::hasRole('lecturer') returns false");

// 4. Attempt login with wrong password
$invalidLogin = $auth->attempt('student@unilag.edu.ng', 'WrongPassword!');
assertTest($invalidLogin === false, "Auth::attempt() rejects invalid password");

// 5. Test Logout
$auth->logout();
assertTest($auth->check() === false, "Auth::logout() clears authentication state");

// 6. Test Admin login
$adminLogin = $auth->attempt('admin@nadics.com', 'Password123!');
assertTest($adminLogin === true, "Super Admin login authentication");
assertTest($auth->role() === 'super_admin', "Super Admin role identified correctly");
$auth->logout();

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n";

exit($failed > 0 ? 1 : 0);
