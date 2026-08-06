<?php
/**
 * Nadics LectureHub — Attendance Controller
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;
use Core\QueryBuilder;

class AttendanceController extends Controller
{
    public function index(Request $request): void
    {
        $user = Auth::getInstance()->user();
        $role = Auth::getInstance()->role();

        // Fetch attendance records for the logged-in user
        $records = [];
        try {
            if (in_array($role, ['lecturer', 'admin', 'university_admin', 'super_admin'])) {
                // Lecturers see all attendance records for their lectures
                $records = QueryBuilder::table('attendance_records')
                    ->join('attendance_sessions', 'attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                    ->join('users', 'attendance_records.student_id', '=', 'users.id')
                    ->join('lectures', 'attendance_sessions.lecture_id', '=', 'lectures.id')
                    ->join('courses', 'lectures.course_id', '=', 'courses.id')
                    ->select([
                        'attendance_records.*',
                        'users.first_name',
                        'users.last_name',
                        'users.matric_staff_id',
                        'lectures.title as lecture_title',
                        'courses.code as course_code',
                    ])
                    ->orderBy('attendance_records.verified_at', 'DESC')
                    ->limit(50)
                    ->get();
            } else {
                // Students see their own attendance
                $records = QueryBuilder::table('attendance_records')
                    ->join('attendance_sessions', 'attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                    ->join('lectures', 'attendance_sessions.lecture_id', '=', 'lectures.id')
                    ->join('courses', 'lectures.course_id', '=', 'courses.id')
                    ->select([
                        'attendance_records.*',
                        'lectures.title as lecture_title',
                        'courses.code as course_code',
                    ])
                    ->where('attendance_records.student_id', '=', $user['id'])
                    ->orderBy('attendance_records.verified_at', 'DESC')
                    ->get();
            }
        } catch (\Exception $e) {
            // Log warning if query fails
            \Core\Logger::getInstance()->warning('Attendance fetch failed', ['error' => $e->getMessage()]);
        }

        // Fetch available lectures for QR generation (for staff)
        $lectures = [];
        if (in_array($role, ['lecturer', 'admin', 'university_admin', 'super_admin'])) {
            if ($role === 'lecturer') {
                $lectures = QueryBuilder::table('lectures')
                    ->join('courses', 'lectures.course_id', '=', 'courses.id')
                    ->where('lectures.lecturer_id', '=', $user['id'])
                    ->select(['lectures.id', 'lectures.title', 'courses.code as course_code'])
                    ->orderBy('lectures.id', 'DESC')
                    ->get();
            } else {
                $lectures = QueryBuilder::table('lectures')
                    ->join('courses', 'lectures.course_id', '=', 'courses.id')
                    ->select(['lectures.id', 'lectures.title', 'courses.code as course_code'])
                    ->orderBy('lectures.id', 'DESC')
                    ->get();
            }
        }

        $this->view('attendance.index', [
            'page_title'       => 'Attendance Management',
            'page_description' => 'Track and manage lecture attendance records and QR verification.',
            'user'             => $user,
            'user_role'        => $role,
            'records'          => $records,
            'lectures'         => $lectures,
        ]);
    }

    /**
     * Generate dynamic attendance QR code for a lecture.
     */
    public function generateQr(Request $request): void
    {
        $this->authorize(['lecturer', 'admin', 'university_admin', 'super_admin']);

        $validated = $this->validate($request, [
            'lecture_id'       => 'required|integer',
            'duration_minutes' => 'required|integer|min:1|max:180',
        ]);

        $lectureId = (int)$validated['lecture_id'];
        $durationMinutes = (int)$validated['duration_minutes'];

        $lecture = QueryBuilder::table('lectures')
            ->join('courses', 'lectures.course_id', '=', 'courses.id')
            ->where('lectures.id', '=', $lectureId)
            ->select(['lectures.*', 'courses.code as course_code', 'courses.title as course_title'])
            ->first();

        if (!$lecture) {
            if ($request->ajax()) {
                $this->jsonError('Lecture not found.', 404);
                return;
            }
            $this->redirectWithError(url('/attendance'), 'Selected lecture session not found.');
            return;
        }

        // Deactivate previous QR sessions for this lecture
        QueryBuilder::table('attendance_sessions')
            ->where('lecture_id', '=', $lectureId)
            ->update(['is_active' => 0]);

        // Generate unique QR verification token hash
        $qrCodeHash = 'QR_ATT_' . $lectureId . '_' . bin2hex(random_bytes(6));
        $expiresAt = date('Y-m-d H:i:s', time() + ($durationMinutes * 60));

        $sessionId = QueryBuilder::table('attendance_sessions')->insertGetId([
            'lecture_id'   => $lectureId,
            'qr_code_hash' => $qrCodeHash,
            'expires_at'   => $expiresAt,
            'is_active'    => 1,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $verifyUrl = url('/attendance/verify?code=' . $qrCodeHash);

        if ($request->ajax()) {
            $this->jsonSuccess('QR Code generated successfully.', [
                'session_id'    => $sessionId,
                'qr_code_hash'  => $qrCodeHash,
                'verify_url'    => $verifyUrl,
                'expires_at'    => $expiresAt,
                'duration_mins' => $durationMinutes,
                'lecture_title' => $lecture['title'],
                'course_code'   => $lecture['course_code'],
            ]);
            return;
        }

        $this->redirectWithSuccess(url('/attendance'), 'QR Code generated for ' . $lecture['title']);
    }

    /**
     * Student verification of scanned QR code.
     */
    public function verify(Request $request): void
    {
        $auth = Auth::getInstance();
        $user = $auth->user();
        $userId = $auth->id();

        $code = trim($request->input('code', ''));
        if (empty($code)) {
            if ($request->ajax()) {
                $this->jsonError('Verification code is required.', 400);
                return;
            }
            $this->redirectWithError(url('/attendance'), 'Please scan or provide a valid QR attendance code.');
            return;
        }

        // Find active session
        $session = QueryBuilder::table('attendance_sessions')
            ->where('qr_code_hash', '=', $code)
            ->where('is_active', '=', 1)
            ->first();

        if (!$session) {
            if ($request->ajax()) {
                $this->jsonError('Invalid or expired QR code session.', 404);
                return;
            }
            $this->redirectWithError(url('/attendance'), 'Invalid or expired QR attendance code.');
            return;
        }

        if (strtotime($session['expires_at']) < time()) {
            if ($request->ajax()) {
                $this->jsonError('This QR code attendance window has expired.', 400);
                return;
            }
            $this->redirectWithError(url('/attendance'), 'This QR attendance session has expired.');
            return;
        }

        // Check for existing record
        $alreadyMarked = QueryBuilder::table('attendance_records')
            ->where('attendance_session_id', '=', $session['id'])
            ->where('student_id', '=', $userId)
            ->exists();

        if ($alreadyMarked) {
            if ($request->ajax()) {
                $this->jsonSuccess('You have already submitted attendance for this session.');
                return;
            }
            $this->redirectWithSuccess(url('/attendance'), 'Your attendance was already verified and marked Present.');
            return;
        }

        // Mark Present
        QueryBuilder::table('attendance_records')->insert([
            'attendance_session_id' => $session['id'],
            'student_id'            => $userId,
            'verification_method'   => 'qr_scan',
            'status'                => 'present',
            'verified_at'           => date('Y-m-d H:i:s'),
        ]);

        $lecture = QueryBuilder::table('lectures')->where('id', '=', $session['lecture_id'])->first();
        $lectureTitle = $lecture ? $lecture['title'] : 'Lecture';

        // Dispatch in-app notification
        try {
            $notifService = new \App\Services\NotificationService();
            $notifService->send(
                $userId,
                'Attendance Verified',
                "Marked Present for lecture session: {$lectureTitle}",
                'attendance'
            );
        } catch (\Throwable $e) {}

        if ($request->ajax()) {
            $this->jsonSuccess("Attendance verified! Marked Present for {$lectureTitle}.");
            return;
        }

        $this->redirectWithSuccess(url('/attendance'), "Attendance verified! Marked Present for {$lectureTitle}.");
    }

    /**
     * Handle GET redirect for scanned QR URL link.
     */
    public function verifyPage(Request $request): void
    {
        $code = $request->query('code');
        if ($code) {
            $request->setMethod('POST');
            $request->merge(['code' => $code]);
            $this->verify($request);
            return;
        }
        $this->redirectWithError(url('/attendance'), 'Invalid QR verification link.');
    }
}
