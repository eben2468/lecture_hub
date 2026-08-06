<?php
/**
 * Nadics LectureHub — Student Course Enrollment Verification Test
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

use Core\QueryBuilder;
use Core\Auth;

echo "==================================================\n";
echo "Nadics LectureHub — Course Enrollment Test\n";
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

// 1. Setup sample student, course, and academic session
$student = QueryBuilder::table('users')
    ->join('roles', 'users.role_id', '=', 'roles.id')
    ->where('roles.slug', '=', 'student')
    ->select(['users.id', 'users.email'])
    ->first();

if (!$student) {
    echo "No student user found. Skipping tests.\n";
    exit;
}

$course = QueryBuilder::table('courses')->first();
$session = QueryBuilder::table('academic_sessions')->first();
if (!$session) {
    $univ = QueryBuilder::table('universities')->first();
    $univId = $univ ? $univ['id'] : 1;
    $sessionId = QueryBuilder::table('academic_sessions')->insertGetId([
        'university_id' => $univId,
        'name'          => '2026/2027 Academic Session',
        'is_current'    => 1,
    ]);
} else {
    $sessionId = $session['id'];
}

// Clear any existing enrollments for clean state
QueryBuilder::table('course_enrollments')
    ->where('student_id', '=', $student['id'])
    ->where('course_id', '=', $course['id'])
    ->delete();

// 2. Perform enrollment insertion (simulate lecturer submitting form)
$enrollmentId = QueryBuilder::table('course_enrollments')->insertGetId([
    'course_id'           => $course['id'],
    'student_id'          => $student['id'],
    'academic_session_id' => $sessionId,
    'status'              => 'enrolled',
]);

assertTest($enrollmentId > 0, "Lecturer enrollment action: Student enrolled in course (Enrollment ID: {$enrollmentId})");

// Verify enrollment state in DB
$enrollObj = QueryBuilder::table('course_enrollments')
    ->where('id', '=', $enrollmentId)
    ->first();
assertTest($enrollObj && $enrollObj['status'] === 'enrolled', "Enrollment state verification: Record exists in database with status 'enrolled'");

// 3. Drop student enrollment (simulate lecturer dropping student)
QueryBuilder::table('course_enrollments')
    ->where('id', '=', $enrollmentId)
    ->delete();

$droppedObj = QueryBuilder::table('course_enrollments')
    ->where('id', '=', $enrollmentId)
    ->first();
assertTest($droppedObj === null, "Lecturer drop action: Student enrollment removed successfully");

// 4. Bulk enrollment CSV parsing simulation
$tempCsv = tempnam(sys_get_temp_dir(), 'csv');
$fileHandle = fopen($tempCsv, 'w');
fputcsv($fileHandle, ['Identifier']); // header
fputcsv($fileHandle, [$student['email']]); // email row
fclose($fileHandle);

// Simulate bulkEnroll controller parsing logic
$enrollmentCount = 0;
if (($handle = fopen($tempCsv, 'r')) !== false) {
    $header = fgetcsv($handle);
    while (($row = fgetcsv($handle)) !== false) {
        if (empty($row) || !isset($row[0])) continue;
        $identifier = trim($row[0]);
        
        $foundStudent = QueryBuilder::table('users')
            ->where('email', '=', $identifier)
            ->first();
            
        if ($foundStudent) {
            $alreadyEnrolled = QueryBuilder::table('course_enrollments')
                ->where('course_id', '=', $course['id'])
                ->where('student_id', '=', $foundStudent['id'])
                ->exists();
                
            if (!$alreadyEnrolled) {
                QueryBuilder::table('course_enrollments')->insert([
                    'course_id'           => $course['id'],
                    'student_id'          => $foundStudent['id'],
                    'academic_session_id' => $sessionId,
                    'status'              => 'enrolled',
                ]);
                $enrollmentCount++;
            }
        }
    }
    fclose($handle);
}
unlink($tempCsv);

assertTest($enrollmentCount === 1, "Bulk CSV enrollment simulation: Successfully parsed CSV and enrolled 1 student");

$bulkObj = QueryBuilder::table('course_enrollments')
    ->where('course_id', '=', $course['id'])
    ->where('student_id', '=', $student['id'])
    ->first();
assertTest($bulkObj && $bulkObj['status'] === 'enrolled', "Bulk CSV enrollment database state: Student record persisted successfully");

// Cleanup
QueryBuilder::table('course_enrollments')
    ->where('course_id', '=', $course['id'])
    ->where('student_id', '=', $student['id'])
    ->delete();

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n";
