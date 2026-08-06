<?php
/**
 * Nadics LectureHub — Phase 4 Administrative Engine Test
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

use Core\Auth;
use Core\QueryBuilder;

echo "==================================================\n";
echo "Nadics LectureHub — Phase 4 Admin Engine Verification\n";
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

// 1. Authenticate Super Admin
$auth = Auth::getInstance();
$auth->attempt('admin@nadics.com', 'Password123!');
assertTest($auth->check() && $auth->role() === 'super_admin', "Super Admin authenticated for admin tasks");

// 2. University Onboarding
$testUnivCode = 'OAU_TEST';
QueryBuilder::table('universities')->where('code', '=', $testUnivCode)->delete();

$univId = QueryBuilder::table('universities')->insert([
    'name'       => 'Obafemi Awolowo University',
    'code'       => $testUnivCode,
    'domain'     => 'oauife.edu.ng',
    'city'       => 'Ile-Ife',
    'state'      => 'Osun',
    'country'    => 'Nigeria',
    'status'     => 'active',
    'created_at' => date('Y-m-d H:i:s'),
]);
assertTest($univId > 0, "University onboarding record created (ID: {$univId})");

// 3. Faculty Creation
$facId = QueryBuilder::table('faculties')->insert([
    'university_id' => $univId,
    'name'          => 'Faculty of Technology',
    'code'          => 'TECH',
    'dean_name'     => 'Prof. A. S. Olarinmoye',
    'created_at'    => date('Y-m-d H:i:s'),
]);
assertTest($facId > 0, "Faculty record created (ID: {$facId})");

// 4. Department Creation
$deptId = QueryBuilder::table('departments')->insert([
    'faculty_id' => $facId,
    'name'       => 'Department of Electronic & Electrical Engineering',
    'code'       => 'EEE',
    'hod_name'   => 'Dr. B. K. Falaye',
    'created_at' => date('Y-m-d H:i:s'),
]);
assertTest($deptId > 0, "Department record created (ID: {$deptId})");

// 5. Course Registration
$courseId = QueryBuilder::table('courses')->insert([
    'department_id' => $deptId,
    'code'          => 'EEE 301',
    'title'         => 'Circuit Theory & Signals',
    'description'   => 'Analysis of linear time-invariant circuits and signals.',
    'credit_unit'   => 4,
    'level'         => 300,
    'semester'      => 'first',
    'status'        => 'active',
    'created_at'    => date('Y-m-d H:i:s'),
]);
assertTest($courseId > 0, "Course registered (ID: {$courseId})");

// Clean up test records
QueryBuilder::table('courses')->where('id', '=', $courseId)->delete();
QueryBuilder::table('departments')->where('id', '=', $deptId)->delete();
QueryBuilder::table('faculties')->where('id', '=', $facId)->delete();
QueryBuilder::table('universities')->where('id', '=', $univId)->delete();

$auth->logout();

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n";

exit($failed > 0 ? 1 : 0);
