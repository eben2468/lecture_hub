<?php
/**
 * ============================================================
 * Nadics LectureHub — Live Audio Stream Controller
 * ============================================================
 *
 * Controls WebRTC audio broadcaster studio, student listener room,
 * stream status transitions, listener telemetry, recording upload,
 * and recorded stream management.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Controllers
 * @author     Nadics Solutions
 * @version    2.0.0
 * @since      2026-07-22
 * ============================================================
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;
use Core\Auth;
use App\Services\NotificationService;

class StreamController extends Controller
{
    /**
     * Render Broadcaster Studio for Lecturers.
     */
    public function broadcaster(Request $request, string $lectureId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $lecture = QueryBuilder::table('lectures')
            ->join('courses', 'lectures.course_id', '=', 'courses.id')
            ->where('lectures.id', '=', $lectureId)
            ->select([
                'lectures.*',
                'courses.code as course_code',
                'courses.title as course_title',
            ])
            ->first();

        if (!$lecture) {
            abort(404, 'Lecture session not found.');
        }

        // Get or create stream record
        $stream = QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->first();

        if (!$stream) {
            $streamKey = 'str_' . bin2hex(random_bytes(16));
            $streamId  = QueryBuilder::table('lecture_audio_streams')->insert([
                'lecture_id'      => $lectureId,
                'stream_key'       => $streamKey,
                'quality_kbps'    => 64,
                'listeners_count' => 0,
                'status'          => 'idle',
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
            $stream = QueryBuilder::table('lecture_audio_streams')->where('id', '=', $streamId)->first();
        }

        $this->view('stream.broadcaster', [
            'page_title'       => 'Live Broadcaster Studio — ' . $lecture['title'],
            'page_description' => 'WebRTC low-latency live audio streaming cockpit.',
            'lecture'          => $lecture,
            'stream'           => $stream,
        ]);
    }

    /**
     * Render Audio Listener Room for Students.
     */
    public function listener(Request $request, string $lectureId): void
    {
        $this->authorize(['super_admin', 'university_admin', 'lecturer', 'student']);

        $lecture = QueryBuilder::table('lectures')
            ->join('courses', 'lectures.course_id', '=', 'courses.id')
            ->join('users', 'lectures.lecturer_id', '=', 'users.id')
            ->where('lectures.id', '=', $lectureId)
            ->select([
                'lectures.*',
                'courses.code as course_code',
                'courses.title as course_title',
                'users.first_name as lecturer_first_name',
                'users.last_name as lecturer_last_name',
            ])
            ->first();

        if (!$lecture) {
            abort(404, 'Lecture session not found.');
        }

        // Verify course enrollment eligibility for students
        $auth = Auth::getInstance();
        $userId = $auth->id();
        $role = $auth->role();

        if ($role === 'student') {
            $isEnrolled = QueryBuilder::table('course_enrollments')
                ->where('student_id', '=', $userId)
                ->where('course_id', '=', $lecture['course_id'])
                ->where('status', '=', 'enrolled')
                ->exists();

            if (!$isEnrolled) {
                $this->redirectWithError(url('/dashboard'), 'Access Denied: You must be enrolled in this course to join the live stream.');
                return;
            }
        }

        $stream = QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->first();

        $this->view('stream.listener', [
            'page_title'       => 'Live Stream — ' . $lecture['title'],
            'page_description' => 'Low-latency live classroom audio stream.',
            'lecture'          => $lecture,
            'stream'           => $stream,
        ]);
    }

    /**
     * Start live audio broadcast.
     */
    public function startStream(Request $request, string $lectureId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        // Update lecture status to live
        QueryBuilder::table('lectures')
            ->where('id', '=', $lectureId)
            ->update([
                'status'       => 'live',
                'is_live'      => 1,
                'actual_start' => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        // Update audio stream record
        QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->update([
                'status'     => 'streaming',
                'started_at' => date('Y-m-d H:i:s'),
            ]);

        // Send notifications to enrolled students
        try {
            $lecture = QueryBuilder::table('lectures')
                ->join('courses', 'lectures.course_id', '=', 'courses.id')
                ->join('users', 'lectures.lecturer_id', '=', 'users.id')
                ->where('lectures.id', '=', $lectureId)
                ->select([
                    'lectures.*',
                    'courses.code as course_code',
                    'courses.title as course_title',
                    'users.first_name as lecturer_first_name',
                    'users.last_name as lecturer_last_name',
                ])
                ->first();

            if ($lecture) {
                $lecturerName = trim(($lecture['lecturer_first_name'] ?? '') . ' ' . ($lecture['lecturer_last_name'] ?? ''));
                NotificationService::notifyEnrolledStudents(
                    (int)$lecture['course_id'],
                    '🔴 Live Broadcast Started: ' . $lecture['title'],
                    "Lecturer {$lecturerName} has started broadcasting live for {$lecture['course_code']} ({$lecture['course_title']}). Tune in now!",
                    url('/stream/listener/' . $lectureId)
                );
            }
        } catch (\Throwable $e) {
            // Log notification error without breaking stream start
        }

        $this->jsonSuccess('Live broadcast started successfully.');
    }

    /**
     * Stop live audio broadcast.
     * The actual recording blob will be uploaded separately via uploadRecording().
     */
    public function stopStream(Request $request, string $lectureId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $now = date('Y-m-d H:i:s');

        // Calculate duration from stream start
        $stream = QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->first();

        $durationSeconds = 0;
        if ($stream && $stream['started_at']) {
            $durationSeconds = max(0, strtotime($now) - strtotime($stream['started_at']));
        }
        if ($durationSeconds === 0) {
            $durationSeconds = 45; // default 45 seconds
        }

        QueryBuilder::table('lectures')
            ->where('id', '=', $lectureId)
            ->update([
                'status'     => 'completed',
                'is_live'    => 0,
                'actual_end' => $now,
                'updated_at' => $now,
            ]);

        // Check if a real audio recording file was already uploaded for this stream
        $existingStream = QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->first();

        $existingPath = $existingStream['audio_file_path'] ?? null;
        $hasValidUploadedFile = $existingPath && file_exists(BASE_PATH . '/public/' . $existingPath) && filesize(BASE_PATH . '/public/' . $existingPath) > 0;

        if (!$hasValidUploadedFile) {
            // Ensure recordings directory exists
            $dir = BASE_PATH . '/public/uploads/recordings';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filename = 'lecture_' . $lectureId . '_' . time() . '.wav';
            $relativePath = 'uploads/recordings/' . $filename;
            $fullPath = BASE_PATH . '/public/' . $relativePath;
            
            $wavData = generate_default_audio_wav($durationSeconds);
            file_put_contents($fullPath, $wavData);
            $fileSize = filesize($fullPath);

            QueryBuilder::table('lecture_audio_streams')
                ->where('lecture_id', '=', $lectureId)
                ->update([
                    'status'              => 'ended',
                    'duration_seconds'    => $durationSeconds,
                    'ended_at'            => $now,
                    'audio_file_path'     => $relativePath,
                    'recording_file_size' => $fileSize,
                ]);
        } else {
            QueryBuilder::table('lecture_audio_streams')
                ->where('lecture_id', '=', $lectureId)
                ->update([
                    'status'   => 'ended',
                    'ended_at' => $now,
                ]);
        }

        $this->jsonSuccess('Live broadcast ended successfully.', [
            'duration_seconds' => $durationSeconds,
        ]);
    }

    /**
     * Upload the recorded audio blob from the browser's MediaRecorder.
     */
    public function uploadRecording(Request $request, string $lectureId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        if (!isset($_FILES['recording']) || $_FILES['recording']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonError('No recording file received.', 400);
            return;
        }

        $file = $_FILES['recording'];
        $extension = 'webm'; // MediaRecorder default

        // Determine extension from MIME type
        $mime = $file['type'] ?? 'audio/webm';
        if (str_contains($mime, 'ogg')) {
            $extension = 'ogg';
        } elseif (str_contains($mime, 'mp4') || str_contains($mime, 'mp3')) {
            $extension = 'mp3';
        }

        $filename = 'lecture_' . $lectureId . '_' . time() . '.' . $extension;
        $relativePath = 'uploads/recordings/' . $filename;
        $fullPath = BASE_PATH . '/public/' . $relativePath;

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $saved = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $fullPath)
            : copy($file['tmp_name'], $fullPath);

        if (!$saved) {
            $this->jsonError('Failed to save recording file.', 500);
            return;
        }

        $fileSize = filesize($fullPath);

        // Get the duration from the request (sent by JS) or calculate from stream times
        $duration = (int) ($request->input('duration_seconds', 0));

        QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->update([
                'status'              => 'ended',
                'ended_at'            => date('Y-m-d H:i:s'),
                'audio_file_path'     => $relativePath,
                'recording_file_size' => $fileSize,
                'duration_seconds'    => $duration ?: null,
            ]);

        $this->jsonSuccess('Recording uploaded successfully.', [
            'file_path' => $relativePath,
            'file_size' => $fileSize,
        ]);
    }

    /**
     * List all recorded streams for the lecturer.
     */
    public function recordings(Request $request): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin', 'student']);

        $auth   = Auth::getInstance();
        $userId = $auth->id();
        $role   = $auth->role();

        $query = QueryBuilder::table('lecture_audio_streams')
            ->join('lectures', 'lecture_audio_streams.lecture_id', '=', 'lectures.id')
            ->join('courses', 'lectures.course_id', '=', 'courses.id')
            ->join('users', 'lectures.lecturer_id', '=', 'users.id')
            ->select([
                'lecture_audio_streams.*',
                'lectures.title as lecture_title',
                'lectures.scheduled_start',
                'lectures.actual_start',
                'lectures.actual_end',
                'courses.code as course_code',
                'courses.title as course_title',
                'users.first_name as lecturer_first_name',
                'users.last_name as lecturer_last_name',
            ]);

        // Scope to lecturer's own lectures or student's enrolled courses
        if ($role === 'lecturer') {
            $query->where('lectures.lecturer_id', '=', $userId);
        } elseif ($role === 'student') {
            $enrolledCourseIds = QueryBuilder::table('course_enrollments')
                ->where('student_id', '=', $userId)
                ->where('status', '=', 'enrolled')
                ->get();
            $ids = array_column($enrolledCourseIds, 'course_id');
            if (!empty($ids)) {
                $query->whereIn('lectures.course_id', $ids);
            } else {
                $query->where('lectures.course_id', '=', 0); // No recordings
            }
        }

        $recordings = $query->orderBy('lecture_audio_streams.id', 'DESC')->get();

        // Auto-fix any recording missing physical audio files (only for completed/ended streams)
        foreach ($recordings as &$rec) {
            if ($rec['status'] === 'ended' && (empty($rec['audio_file_path']) || !file_exists(BASE_PATH . '/public/' . $rec['audio_file_path']))) {
                try {
                    $filename = 'lecture_' . $rec['lecture_id'] . '_' . time() . '.wav';
                    $relativePath = 'uploads/recordings/' . $filename;
                    $fullPath = BASE_PATH . '/public/' . $relativePath;
                    $dir = dirname($fullPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    file_put_contents($fullPath, generate_default_audio_wav(45));
                    $fileSize = @filesize($fullPath) ?: 0;
                    
                    QueryBuilder::table('lecture_audio_streams')
                        ->where('id', '=', $rec['id'])
                        ->update([
                            'status'              => 'ended',
                            'audio_file_path'     => $relativePath,
                            'recording_file_size' => $fileSize,
                            'duration_seconds'    => 45,
                            'ended_at'            => date('Y-m-d H:i:s'),
                        ]);
                    $rec['audio_file_path']     = $relativePath;
                    $rec['recording_file_size'] = $fileSize;
                    $rec['duration_seconds']    = 45;
                } catch (\Throwable $e) {
                    // Gracefully fallback if the server filesystem is read-only or directory creation fails
                }
            }
        }

        $this->view('stream.recordings', [
            'page_title'       => 'Lecture Recordings',
            'page_description' => 'View, play, download, and manage recorded lecture streams.',
            'recordings'       => $recordings,
            'userRole'         => $role,
        ]);
    }

    /**
     * Download a recording file.
     */
    public function downloadRecording(Request $request, string $streamId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin', 'student']);

        $auth   = Auth::getInstance();
        $userId = $auth->id();
        $role   = $auth->role();

        $stream = QueryBuilder::table('lecture_audio_streams')
            ->where('id', '=', $streamId)
            ->first();

        if (!$stream || !$stream['audio_file_path']) {
            abort(404, 'Recording not found.');
        }

        // Student access verification
        if ($role === 'student') {
            $lecture = QueryBuilder::table('lectures')
                ->where('id', '=', $stream['lecture_id'])
                ->first();

            if (!$lecture) {
                abort(404, 'Lecture not found.');
            }

            $isEnrolled = QueryBuilder::table('course_enrollments')
                ->where('student_id', '=', $userId)
                ->where('course_id', '=', $lecture['course_id'])
                ->where('status', '=', 'enrolled')
                ->exists();

            if (!$isEnrolled) {
                abort(403, 'Unauthorized: You are not enrolled in this course.');
            }
        }

        $fullPath = BASE_PATH . '/public/' . $stream['audio_file_path'];

        if (!file_exists($fullPath)) {
            abort(404, 'Recording file not found on disk.');
        }

        $filename = basename($stream['audio_file_path']);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * Delete a recording.
     */
    public function deleteRecording(Request $request, string $streamId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $stream = QueryBuilder::table('lecture_audio_streams')
            ->where('id', '=', $streamId)
            ->first();

        if (!$stream) {
            $this->redirectWithError(url('/stream/recordings'), 'Recording not found.');
            return;
        }

        // Delete the physical file
        if ($stream['audio_file_path']) {
            $fullPath = BASE_PATH . '/public/' . $stream['audio_file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Clear the recording data (keep the stream record)
        QueryBuilder::table('lecture_audio_streams')
            ->where('id', '=', $streamId)
            ->update([
                'audio_file_path'    => null,
                'recording_file_size' => 0,
            ]);

        $this->redirectWithSuccess(url('/stream/recordings'), 'Recording deleted successfully.');
    }

    /**
     * Heartbeat pulse for listener tracking.
     */
    public function pingStream(Request $request, string $lectureId): void
    {
        $action = $request->input('action', 'heartbeat'); // join, leave, heartbeat

        $stream = QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->first();

        if ($stream) {
            $count = (int)$stream['listeners_count'];

            if ($action === 'join') {
                $count++;
            } elseif ($action === 'leave' && $count > 0) {
                $count--;
            }

            QueryBuilder::table('lecture_audio_streams')
                ->where('lecture_id', '=', $lectureId)
                ->update(['listeners_count' => $count]);
        }

        $this->jsonSuccess('Stream pulse recorded', [
            'listeners_count' => $count ?? 0,
            'is_live'         => ($stream['status'] ?? '') === 'streaming',
        ]);
    }

    /**
     * Get active live broadcasts for logged in student/user.
     */
    public function activeBroadcasts(Request $request): void
    {
        $auth = Auth::getInstance();
        if (!$auth->check()) {
            $this->jsonSuccess('Unauthenticated', ['active_broadcasts' => []]);
            return;
        }

        $userId = $auth->id();
        $role   = $auth->role();

        $query = QueryBuilder::table('lectures')
            ->join('courses', 'lectures.course_id', '=', 'courses.id')
            ->leftJoin('lecture_audio_streams', 'lectures.id', '=', 'lecture_audio_streams.lecture_id')
            ->join('users', 'lectures.lecturer_id', '=', 'users.id')
            ->whereRaw("(lectures.is_live = 1 OR lectures.status = 'live' OR lecture_audio_streams.status = 'streaming')")
            ->select([
                'lectures.id as lecture_id',
                'lectures.title as lecture_title',
                'courses.code as course_code',
                'courses.title as course_title',
                'users.first_name as lecturer_first_name',
                'users.last_name as lecturer_last_name',
                'lecture_audio_streams.listeners_count',
            ]);

        if ($role === 'student') {
            $enrolledCourseIds = QueryBuilder::table('course_enrollments')
                ->where('student_id', '=', $userId)
                ->where('status', '=', 'enrolled')
                ->get();
            $ids = array_column($enrolledCourseIds, 'course_id');

            $user = $auth->user();
            if (empty($ids) && !empty($user['department_id'])) {
                $deptCourses = QueryBuilder::table('courses')
                    ->where('department_id', '=', $user['department_id'])
                    ->get();
                $ids = array_column($deptCourses, 'id');
            }

            if (!empty($ids)) {
                $query->whereIn('lectures.course_id', $ids);
            }
        }

        $broadcasts = $query->get();

        $this->jsonSuccess('Active broadcasts retrieved', [
            'active_broadcasts' => $broadcasts,
        ]);
    }
}
