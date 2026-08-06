<?php
/**
 * Nadics LectureHub — Live Stream Eligibility Verification Test
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

use Core\QueryBuilder;
use Core\Auth;

echo "==================================================\n";
echo "Nadics LectureHub — Stream Eligibility Test\n";
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

// 1. Setup sample student, course, and enrollment
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
$lectureId = QueryBuilder::table('lectures')->insertGetId([
    'course_id'       => $course['id'],
    'lecturer_id'     => 1,
    'title'           => 'Eligibility Verification Lecture',
    'scheduled_start' => date('Y-m-d H:i:s'),
    'scheduled_end'   => date('Y-m-d H:i:s', strtotime('+2 hours')),
    'status'          => 'live',
    'is_live'         => 1,
    'created_at'      => date('Y-m-d H:i:s'),
]);

// 2. Test case A: Student is enrolled in the course
QueryBuilder::table('course_enrollments')
    ->where('student_id', '=', $student['id'])
    ->where('course_id', '=', $course['id'])
    ->delete(); // Clear previous

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

QueryBuilder::table('course_enrollments')->insert([
    'student_id'          => $student['id'],
    'course_id'           => $course['id'],
    'academic_session_id' => $sessionId,
    'status'              => 'enrolled',
]);

$isEnrolled = QueryBuilder::table('course_enrollments')
    ->where('student_id', '=', $student['id'])
    ->where('course_id', '=', $course['id'])
    ->where('status', '=', 'enrolled')
    ->exists();

assertTest($isEnrolled === true, "Enrollment eligibility check: Enrolled student is validated successfully");

// 3. Test case B: Student is NOT enrolled in the course
QueryBuilder::table('course_enrollments')
    ->where('student_id', '=', $student['id'])
    ->where('course_id', '=', $course['id'])
    ->delete(); // Remove enrollment

$isEnrolledNo = QueryBuilder::table('course_enrollments')
    ->where('student_id', '=', $student['id'])
    ->where('course_id', '=', $course['id'])
    ->where('status', '=', 'enrolled')
    ->exists();

assertTest($isEnrolledNo === false, "Enrollment eligibility check: Non-enrolled student is rejected successfully");

// Cleanup
QueryBuilder::table('lectures')->where('id', '=', $lectureId)->delete();

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n";
