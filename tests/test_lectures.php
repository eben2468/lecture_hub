<?php
/**
 * Nadics LectureHub — Phase 5 Lecture & Material Engine Test
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

use Core\Auth;
use Core\QueryBuilder;

echo "==================================================\n";
echo "Nadics LectureHub — Phase 5 Verification\n";
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

// 1. Authenticate as lecturer
$auth = Auth::getInstance();
$result = $auth->attempt('lecturer@unilag.edu.ng', 'Password123!');
assertTest($result && $auth->role() === 'lecturer', "Lecturer authenticated successfully");

$lecturerId = $auth->id();

// 2. Get a course to schedule a lecture
$courses = QueryBuilder::table('courses')->where('status', '=', 'active')->get();
assertTest(!empty($courses), "Active courses exist in database (" . count($courses) . " found)");

$courseId = $courses[0]['id'] ?? 0;

// 3. Schedule a lecture
$lectureId = QueryBuilder::table('lectures')->insert([
    'course_id'       => $courseId,
    'lecturer_id'     => $lecturerId,
    'title'           => 'Test Lecture: Introduction to Algorithms',
    'description'     => 'Covering sorting, searching, and complexity analysis.',
    'scheduled_start' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'scheduled_end'   => date('Y-m-d H:i:s', strtotime('+1 day 2 hours')),
    'status'          => 'scheduled',
    'is_live'         => 0,
    'created_at'      => date('Y-m-d H:i:s'),
]);
assertTest($lectureId > 0, "Lecture scheduled successfully (ID: {$lectureId})");

// 4. Verify lecture retrieval with join
$lecture = QueryBuilder::table('lectures')
    ->join('courses', 'lectures.course_id', '=', 'courses.id')
    ->join('users', 'lectures.lecturer_id', '=', 'users.id')
    ->where('lectures.id', '=', $lectureId)
    ->select([
        'lectures.*',
        'courses.code as course_code',
        'users.first_name as lecturer_first_name',
    ])
    ->first();
assertTest($lecture && $lecture['course_code'] !== null, "Lecture retrieved with JOIN (Course: {$lecture['course_code']})");

// 5. Create a course material record
$materialId = QueryBuilder::table('course_materials')->insert([
    'course_id'      => $courseId,
    'lecture_id'     => $lectureId,
    'uploaded_by'    => $lecturerId,
    'title'          => 'Sorting Algorithms Lecture Slides',
    'description'    => 'Comprehensive slides covering bubble, merge, and quick sort.',
    'file_path'      => 'uploads/materials/test_slides.pdf',
    'file_size'      => 2048576,
    'mime_type'      => 'application/pdf',
    'download_count' => 0,
    'created_at'     => date('Y-m-d H:i:s'),
]);
assertTest($materialId > 0, "Course material record created (ID: {$materialId})");

// 6. Create an assignment
$dueDate = date('Y-m-d H:i:s', strtotime('+7 days'));
$assignmentId = QueryBuilder::table('assignments')->insert([
    'course_id'   => $courseId,
    'created_by'  => $lecturerId,
    'title'       => 'Assignment 1: Algorithm Complexity Analysis',
    'description' => 'Analyze the time complexity of 5 given algorithms. Show all work.',
    'max_score'   => 20,
    'due_date'    => $dueDate,
    'created_at'  => date('Y-m-d H:i:s'),
]);
assertTest($assignmentId > 0, "Assignment created (ID: {$assignmentId})");

// 7. Simulate student submission
$auth->logout();
$auth->attempt('student@unilag.edu.ng', 'Password123!');
$studentId = $auth->id();
assertTest($auth->role() === 'student', "Student authenticated for submission test");

$submissionId = QueryBuilder::table('assignment_submissions')->insert([
    'assignment_id' => $assignmentId,
    'student_id'    => $studentId,
    'file_path'     => 'uploads/submissions/test_submission.pdf',
    'submitted_at'  => date('Y-m-d H:i:s'),
]);
assertTest($submissionId > 0, "Student assignment submission recorded (ID: {$submissionId})");

// 8. Simulate lecturer grading
$auth->logout();
$auth->attempt('lecturer@unilag.edu.ng', 'Password123!');

QueryBuilder::table('assignment_submissions')
    ->where('id', '=', $submissionId)
    ->update([
        'score'     => 18.5,
        'feedback'  => 'Excellent work! Minor formatting issues.',
        'graded_by' => $auth->id(),
        'graded_at' => date('Y-m-d H:i:s'),
    ]);

$gradedSub = QueryBuilder::table('assignment_submissions')
    ->where('id', '=', $submissionId)
    ->first();
assertTest(
    $gradedSub && (float)$gradedSub['score'] === 18.5,
    "Assignment graded successfully (Score: {$gradedSub['score']}/20)"
);

// Clean up
QueryBuilder::table('assignment_submissions')->where('id', '=', $submissionId)->delete();
QueryBuilder::table('assignments')->where('id', '=', $assignmentId)->delete();
QueryBuilder::table('course_materials')->where('id', '=', $materialId)->delete();
QueryBuilder::table('lectures')->where('id', '=', $lectureId)->delete();

$auth->logout();

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n";

exit($failed > 0 ? 1 : 0);
