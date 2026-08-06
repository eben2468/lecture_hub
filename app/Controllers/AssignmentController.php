<?php
/**
 * ============================================================
 * Nadics LectureHub — Assignment Controller
 * ============================================================
 *
 * Manages assignment creation, student submissions, and grading.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Controllers
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-22
 * ============================================================
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;
use Core\Auth;
use Core\Logger;
use App\Services\NotificationService;

class AssignmentController extends Controller
{
    // ========================================================
    // LISTING
    // ========================================================

    /**
     * Display all assignments (scoped by role).
     */
    public function index(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin', 'lecturer', 'student']);

        $auth   = Auth::getInstance();
        $userId = $auth->id();
        $role   = $auth->role();

        $query = QueryBuilder::table('assignments')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->join('users', 'assignments.created_by', '=', 'users.id')
            ->select([
                'assignments.*',
                'courses.code as course_code',
                'courses.title as course_title',
                'users.first_name as creator_first_name',
                'users.last_name as creator_last_name',
            ]);

        if ($role === 'lecturer') {
            $query->where('assignments.created_by', '=', $userId);
        } elseif ($role === 'student') {
            $enrolledCourseIds = QueryBuilder::table('course_enrollments')
                ->where('student_id', '=', $userId)
                ->where('status', '=', 'enrolled')
                ->get();
            $ids = array_column($enrolledCourseIds, 'course_id');
            if (!empty($ids)) {
                $query->whereIn('assignments.course_id', $ids);
            } else {
                $query->where('assignments.course_id', '=', 0);
            }
            $query->where('assignments.status', '=', 'published');
        }

        // Support course query filter
        $courseFilter = $request->query('course');
        $selectedCourse = null;
        if ($courseFilter) {
            $query->where('assignments.course_id', '=', $courseFilter);
            $selectedCourse = QueryBuilder::table('courses')->where('id', '=', $courseFilter)->first();
        }

        $assignments = $query->orderBy('assignments.due_date', 'DESC')->get();

        // For students, check their submission status for each assignment
        $submissions = [];
        if ($role === 'student' && !empty($assignments)) {
            $assignmentIds = array_column($assignments, 'id');
            $subs = QueryBuilder::table('assignment_submissions')
                ->where('student_id', '=', $userId)
                ->get();
            foreach ($subs as $sub) {
                $submissions[$sub['assignment_id']] = $sub;
            }
        }

        // Get courses for creation modal
        $courses = [];
        if (in_array($role, ['lecturer', 'university_admin', 'super_admin'])) {
            if ($role === 'lecturer') {
                $courses = QueryBuilder::table('course_lecturers')
                    ->join('courses', 'course_lecturers.course_id', '=', 'courses.id')
                    ->where('course_lecturers.lecturer_id', '=', $userId)
                    ->select(['courses.id', 'courses.code', 'courses.title'])
                    ->get();
            } else {
                $courses = QueryBuilder::table('courses')
                    ->where('status', '=', 'active')
                    ->select(['id', 'code', 'title'])
                    ->get();
            }
        }

        $this->view('assignments.index', [
            'page_title'       => 'Assignments',
            'page_description' => 'Manage and submit academic assignments.',
            'assignments'      => $assignments,
            'submissions'      => $submissions,
            'courses'          => $courses,
            'userRole'         => $role,
            'selectedCourse'   => $selectedCourse,
        ]);
    }

    // ========================================================
    // CREATE & PUBLISH
    // ========================================================

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $validated = $this->validate($request, [
            'course_id'   => 'required|integer|exists:courses,id',
            'title'       => 'required|min:3|max:255',
            'description' => 'required|min:10',
            'max_score'   => 'required|integer|min:1|max:100',
            'due_date'    => 'required',
        ]);

        $status = $request->input('status', 'published');
        if (!in_array($status, ['draft', 'published'])) {
            $status = 'published';
        }

        $auth = Auth::getInstance();
        $fileAttachment = null;

        // Handle optional file attachment
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file      = $_FILES['attachment'];
            $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeName  = 'assign_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $uploadDir = BASE_PATH . '/public/uploads/assignments';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $safeName)) {
                $fileAttachment = 'uploads/assignments/' . $safeName;
            }
        }

        QueryBuilder::table('assignments')->insert([
            'course_id'       => $validated['course_id'],
            'created_by'      => $auth->id(),
            'title'           => $validated['title'],
            'description'     => $validated['description'],
            'max_score'       => $validated['max_score'],
            'due_date'        => $validated['due_date'],
            'file_attachment' => $fileAttachment,
            'status'          => $status,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        if ($status === 'published') {
            $notifService = new NotificationService();
            $notifService->notifyEnrolledStudents(
                (int)$validated['course_id'],
                'New Assignment: ' . $validated['title'],
                'A new assignment has been posted for your course. Due date: ' . date('M d, Y g:i A', strtotime($validated['due_date']))
            );
        }

        $msg = ($status === 'draft')
            ? 'Assignment saved as draft. Enrolled students cannot view it until published.'
            : 'Assignment created and published to enrolled students successfully.';

        $this->redirectWithSuccess(url('/assignments'), $msg);
    }

    /**
     * Publish or unpublish an assignment.
     */
    public function publish(Request $request, string $id): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $assignment = QueryBuilder::table('assignments')->where('id', '=', $id)->first();
        if (!$assignment) {
            $this->redirectWithError(url('/assignments'), 'Assignment not found.');
            return;
        }

        $newStatus = ($assignment['status'] === 'published') ? 'draft' : 'published';
        if ($request->has('status')) {
            $requestedStatus = $request->input('status');
            if (in_array($requestedStatus, ['draft', 'published', 'closed'])) {
                $newStatus = $requestedStatus;
            }
        }

        QueryBuilder::table('assignments')->where('id', '=', $id)->update([
            'status' => $newStatus,
        ]);

        if ($newStatus === 'published') {
            $notifService = new NotificationService();
            $notifService->notifyEnrolledStudents(
                (int)$assignment['course_id'],
                'New Assignment: ' . $assignment['title'],
                'A new assignment has been posted for your course. Due date: ' . date('M d, Y g:i A', strtotime($assignment['due_date']))
            );
        }

        $msg = ($newStatus === 'published')
            ? 'Assignment published to enrolled students successfully!'
            : 'Assignment status set to ' . ucfirst($newStatus) . '.';

        $referer = $_SERVER['HTTP_REFERER'] ?? url('/assignments');
        $this->redirectWithSuccess($referer, $msg);
    }

    // ========================================================
    // STUDENT SUBMISSION
    // ========================================================

    /**
     * Handle student assignment submission.
     */
    public function submit(Request $request, string $id): void
    {
        $this->authorize(['student']);

        $assignment = QueryBuilder::table('assignments')
            ->where('id', '=', $id)
            ->first();

        if (!$assignment) {
            abort(404, 'Assignment not found.');
        }

        if (($assignment['status'] ?? 'published') !== 'published') {
            $this->redirectWithError(url('/assignments'), 'This assignment is currently in draft mode and not available for submission.');
            return;
        }

        // Check deadline
        if (strtotime($assignment['due_date']) < time()) {
            $this->redirectWithError(url('/assignments'), 'Submission deadline has passed.');
            return;
        }

        // Handle file upload
        if (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirectWithError(url('/assignments'), 'Please attach your submission file.');
            return;
        }

        $file      = $_FILES['submission_file'];
        $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName  = 'sub_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = BASE_PATH . '/public/uploads/submissions';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $safeName)) {
            $this->redirectWithError(url('/assignments'), 'Failed to upload submission.');
            return;
        }

        $auth = Auth::getInstance();

        // Upsert: replace previous submission if exists
        $existing = QueryBuilder::table('assignment_submissions')
            ->where('assignment_id', '=', $id)
            ->where('student_id', '=', $auth->id())
            ->first();

        if ($existing) {
            // Remove old file
            $oldPath = BASE_PATH . '/public/' . $existing['file_path'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }

            QueryBuilder::table('assignment_submissions')
                ->where('id', '=', $existing['id'])
                ->update([
                    'file_path'    => 'uploads/submissions/' . $safeName,
                    'submitted_at' => date('Y-m-d H:i:s'),
                    'score'        => null,
                    'feedback'     => null,
                    'graded_at'    => null,
                ]);
        } else {
            QueryBuilder::table('assignment_submissions')->insert([
                'assignment_id' => $id,
                'student_id'    => $auth->id(),
                'file_path'     => 'uploads/submissions/' . $safeName,
                'submitted_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $this->redirectWithSuccess(url('/assignments'), 'Assignment submitted successfully.');
    }

    // ========================================================
    // GRADING (Lecturer)
    // ========================================================

    /**
     * View submissions for an assignment.
     */
    public function submissions(Request $request, string $id): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $assignment = QueryBuilder::table('assignments')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->where('assignments.id', '=', $id)
            ->select([
                'assignments.*',
                'courses.code as course_code',
                'courses.title as course_title',
            ])
            ->first();

        if (!$assignment) {
            abort(404, 'Assignment not found.');
        }

        $subs = QueryBuilder::table('assignment_submissions')
            ->join('users', 'assignment_submissions.student_id', '=', 'users.id')
            ->where('assignment_submissions.assignment_id', '=', $id)
            ->select([
                'assignment_submissions.*',
                'users.first_name',
                'users.last_name',
                'users.matric_staff_id',
            ])
            ->orderBy('assignment_submissions.submitted_at', 'DESC')
            ->get();

        $this->view('assignments.submissions', [
            'page_title'       => 'Submissions — ' . $assignment['title'],
            'page_description' => $assignment['course_code'] . ' Assignment Submissions',
            'assignment'       => $assignment,
            'submissions'      => $subs,
        ]);
    }

    /**
     * Grade a single submission.
     */
    public function grade(Request $request, string $submissionId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $auth   = Auth::getInstance();
        $userId = $auth->id();
        $role   = $auth->role();

        $sub = QueryBuilder::table('assignment_submissions')
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->where('assignment_submissions.id', '=', $submissionId)
            ->select(['assignment_submissions.id', 'assignments.created_by', 'assignments.course_id'])
            ->first();

        if (!$sub) {
            abort(404, 'Submission not found.');
        }

        if ($role === 'lecturer' && (int)$sub['created_by'] !== $userId) {
            $isAssigned = QueryBuilder::table('course_lecturers')
                ->where('course_id', '=', $sub['course_id'])
                ->where('lecturer_id', '=', $userId)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'You are not authorized to grade submissions for this course.');
            }
        }

        $validated = $this->validate($request, [
            'score'    => 'required|numeric|min:0',
            'feedback' => 'nullable',
        ]);

        QueryBuilder::table('assignment_submissions')
            ->where('id', '=', $submissionId)
            ->update([
                'score'     => $validated['score'],
                'feedback'  => $validated['feedback'] ?? null,
                'graded_by' => $userId,
                'graded_at' => date('Y-m-d H:i:s'),
            ]);

        $this->backWithSuccess('Submission graded successfully.');
    }
}
