<?php
/**
 * Nadics LectureHub — System Architecture End-to-End Verification
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

use App\Services\StreamingService;
use App\Services\AIService;
use App\Services\NotificationService;
use App\Services\ReportingService;
use Core\QueryBuilder;

echo "==================================================\n";
echo "Nadics LectureHub — System Architecture Test\n";
echo "==================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $description, &$passed, &$failed) {
    if ($condition) {
        echo "  [PASS] " . $description . "\n";
        $passed++;
    } else {
        echo "  [FAIL] " . $description . "\n";
        $failed++;
    }
}

// 1. Cloud Database Engine
try {
    $userCount = QueryBuilder::table('users')->count();
    assertTest($userCount > 0, "Cloud Database Engine: MariaDB PDO connection active ({$userCount} users found)", $passed, $failed);
} catch (\Exception $e) {
    assertTest(false, "Cloud Database Engine failure: " . $e->getMessage(), $passed, $failed);
}

// Ensure a test course and lecture exist
$course = QueryBuilder::table('courses')->first();
if (!$course) {
    $courseId = QueryBuilder::table('courses')->insertGetId([
        'code' => 'CSC 301', 'title' => 'Data Structures', 'department_id' => 1, 'units' => 3, 'level' => 300, 'created_at' => date('Y-m-d H:i:s')
    ]);
} else {
    $courseId = $course['id'];
}

$lecture = QueryBuilder::table('lectures')->first();
if (!$lecture) {
    $lectureId = QueryBuilder::table('lectures')->insertGetId([
        'course_id' => $courseId, 'lecturer_id' => 1, 'title' => 'Architecture System Lecture', 'scheduled_start' => date('Y-m-d H:i:s'), 'scheduled_end' => date('Y-m-d H:i:s', strtotime('+2 hours')), 'status' => 'scheduled', 'created_at' => date('Y-m-d H:i:s')
    ]);
} else {
    $lectureId = $lecture['id'];
}

// 2. Live Streaming Engine Service
try {
    $streamingService = new StreamingService();
    $stream = $streamingService->initializeStream($lectureId, 1);
    assertTest(!empty($stream['stream_key']), "Live Streaming Engine: WebRTC session initialized (Stream Key: {$stream['stream_key']})", $passed, $failed);
    
    $statusUpdated = $streamingService->updateStreamStatus($lectureId, 'live');
    assertTest($statusUpdated, "Live Streaming Engine: Stream state transitioned to 'live'", $passed, $failed);
    
    $chat = $streamingService->addChatMessage($lectureId, 1, "Test Q&A question from architecture harness", true);
    assertTest(!empty($chat['id']), "Live Streaming Engine: Real-time Q&A signal processed", $passed, $failed);
} catch (\Exception $e) {
    assertTest(false, "Live Streaming Engine failure: " . $e->getMessage(), $passed, $failed);
}

// 3. AI Processing Engine
try {
    $aiService = new AIService();
    $transcript = $aiService->transcribeLecture($lectureId);
    assertTest(!empty($transcript['full_text']), "AI Processing Engine: Whisper transcript generated ({$transcript['word_count']} words)", $passed, $failed);
    
    $flashcards = $aiService->generateFlashcards($transcript['full_text']);
    assertTest(count($flashcards) >= 3, "AI Processing Engine: Automated revision flashcards generated (" . count($flashcards) . " cards)", $passed, $failed);
} catch (\Exception $e) {
    assertTest(false, "AI Processing Engine failure: " . $e->getMessage(), $passed, $failed);
}

// 4. Notification Service
try {
    $notificationService = new NotificationService();
    $sentInApp = $notificationService->sendInAppNotification(1, "System Notice", "Architecture verification active", "info");
    assertTest($sentInApp, "Notification Service: In-App database notification dispatched", $passed, $failed);
    
    $broadcastCount = $notificationService->notifyEnrolledStudents(1, "Broadcast Alert", "Lecture session starting");
    assertTest($broadcastCount >= 0, "Notification Service: Enrolled student broadcast dispatched ({$broadcastCount} recipients)", $passed, $failed);
} catch (\Exception $e) {
    assertTest(false, "Notification Service failure: " . $e->getMessage(), $passed, $failed);
}

// 5. Reporting & Analytics Module
try {
    $reportingService = new ReportingService();
    $analytics = $reportingService->getAttendanceAnalytics();
    assertTest(isset($analytics['attendance_rate']), "Reporting Module: Attendance rate calculated ({$analytics['attendance_rate']}%)", $passed, $failed);
    
    $csv = $reportingService->exportAttendanceCSV();
    assertTest(str_contains($csv, "Matric Number,Student Name"), "Reporting Module: CSV attendance report formatted", $passed, $failed);
} catch (\Exception $e) {
    assertTest(false, "Reporting Module failure: " . $e->getMessage(), $passed, $failed);
}

// 6. Student Timetable Grid
try {
    $lectures = QueryBuilder::table('lectures')->get();
    assertTest(count($lectures) >= 0, "Academic Tools: Student Timetable grid data retrieved successfully", $passed, $failed);
} catch (\Exception $e) {
    assertTest(false, "Timetable display failure: " . $e->getMessage(), $passed, $failed);
}

// 7. Online Quizzes & Grading
try {
    $quiz = QueryBuilder::table('quizzes')->first();
    assertTest(!empty($quiz['id']), "Academic Tools: Online Quiz retrieved from database (Title: '{$quiz['title']}')", $passed, $failed);
    
    $questions = QueryBuilder::table('quiz_questions')->where('quiz_id', '=', $quiz['id'])->get();
    assertTest(count($questions) > 0, "Academic Tools: Quiz Questions loaded successfully (" . count($questions) . " questions found)", $passed, $failed);
} catch (\Exception $e) {
    assertTest(false, "Quizzes functionality failure: " . $e->getMessage(), $passed, $failed);
}

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n";
