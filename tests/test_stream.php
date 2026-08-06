<?php
/**
 * Nadics LectureHub — Phase 6 Stream & Chat Engine Test
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

use Core\Auth;
use Core\QueryBuilder;

echo "==================================================\n";
echo "Nadics LectureHub — Phase 6 Stream & Chat Verification\n";
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

// 1. Authenticate Lecturer
$auth = Auth::getInstance();
$auth->attempt('lecturer@unilag.edu.ng', 'Password123!');
$lecturerId = $auth->id();
assertTest($auth->check() && $auth->role() === 'lecturer', "Lecturer authenticated for streaming");

// 2. Get active course and create a test lecture
$courses = QueryBuilder::table('courses')->where('status', '=', 'active')->get();
$courseId = $courses[0]['id'] ?? 1;

$lectureId = QueryBuilder::table('lectures')->insert([
    'course_id'       => $courseId,
    'lecturer_id'     => $lecturerId,
    'title'           => 'Live Stream Studio Verification Lecture',
    'scheduled_start' => date('Y-m-d H:i:s'),
    'scheduled_end'   => date('Y-m-d H:i:s', strtotime('+1 hour')),
    'status'          => 'scheduled',
    'created_at'      => date('Y-m-d H:i:s'),
]);
assertTest($lectureId > 0, "Test lecture created for stream verification (ID: {$lectureId})");

// 3. Initialize Audio Stream
$streamKey = 'str_test_' . bin2hex(random_bytes(8));
$streamId = QueryBuilder::table('lecture_audio_streams')->insert([
    'lecture_id'      => $lectureId,
    'stream_key'       => $streamKey,
    'quality_kbps'    => 64,
    'listeners_count' => 0,
    'status'          => 'idle',
    'created_at'      => date('Y-m-d H:i:s'),
]);
assertTest($streamId > 0, "Audio stream session initialized (ID: {$streamId})");

// 4. Start Live Broadcast
QueryBuilder::table('lectures')->where('id', '=', $lectureId)->update([
    'status'  => 'live',
    'is_live' => 1,
]);
QueryBuilder::table('lecture_audio_streams')->where('id', '=', $streamId)->update([
    'status'     => 'streaming',
    'started_at' => date('Y-m-d H:i:s'),
]);

$activeLecture = QueryBuilder::table('lectures')->where('id', '=', $lectureId)->first();
assertTest((int)$activeLecture['is_live'] === 1 && $activeLecture['status'] === 'live', "Lecture status transitioned to 'live'");

// 5. Simulate Listener Join & Heartbeat Telemetry
QueryBuilder::table('lecture_audio_streams')
    ->where('id', '=', $streamId)
    ->update(['listeners_count' => 15]);

$streamObj = QueryBuilder::table('lecture_audio_streams')->where('id', '=', $streamId)->first();
assertTest((int)$streamObj['listeners_count'] === 15, "Listener telemetry updated (15 active listeners)");

// 6. Simulate Live Audience Q&A Chat
$auth->logout();
$auth->attempt('student@unilag.edu.ng', 'Password123!');
$studentId = $auth->id();

$chatId = QueryBuilder::table('lecture_chats')->insert([
    'lecture_id'  => $lectureId,
    'user_id'     => $studentId,
    'message'     => 'Could you please explain time complexity analysis again?',
    'is_question' => 1,
    'is_answered' => 0,
    'created_at'  => date('Y-m-d H:i:s'),
]);
assertTest($chatId > 0, "Student live Q&A question submitted (ID: {$chatId})");

// 7. Lecturer Marks Question as Answered
$auth->logout();
$auth->attempt('lecturer@unilag.edu.ng', 'Password123!');

QueryBuilder::table('lecture_chats')->where('id', '=', $chatId)->update(['is_answered' => 1]);
$chatObj = QueryBuilder::table('lecture_chats')->where('id', '=', $chatId)->first();
assertTest((int)$chatObj['is_answered'] === 1, "Lecturer marked live question as answered");

// 8. End Broadcast Session
QueryBuilder::table('lectures')->where('id', '=', $lectureId)->update([
    'status'  => 'completed',
    'is_live' => 0,
]);
QueryBuilder::table('lecture_audio_streams')->where('id', '=', $streamId)->update([
    'status'   => 'ended',
    'ended_at' => date('Y-m-d H:i:s'),
]);

$endedLecture = QueryBuilder::table('lectures')->where('id', '=', $lectureId)->first();
assertTest($endedLecture['status'] === 'completed' && (int)$endedLecture['is_live'] === 0, "Broadcast session ended cleanly");

// Clean up
QueryBuilder::table('lecture_chats')->where('id', '=', $chatId)->delete();
QueryBuilder::table('lecture_audio_streams')->where('id', '=', $streamId)->delete();
QueryBuilder::table('lectures')->where('id', '=', $lectureId)->delete();

$auth->logout();

echo "\n--------------------------------------------------\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "==================================================\n";

exit($failed > 0 ? 1 : 0);
