<?php
/**
 * Nadics LectureHub — Course Controller
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;
use Core\QueryBuilder;

class CourseController extends Controller
{
    public function index(Request $request): void
    {
        $user = Auth::getInstance()->user();
        $role = Auth::getInstance()->role();

        $search = trim($request->input('search', ''));

        $builder = QueryBuilder::table('courses')
            ->join('departments', 'courses.department_id', '=', 'departments.id')
            ->join('faculties', 'departments.faculty_id', '=', 'faculties.id')
            ->join('universities', 'faculties.university_id', '=', 'universities.id')
            ->select([
                'courses.*',
                'departments.name as department_name',
                'universities.name as university_name',
            ]);

        // Filter by user's university
        if ($user['university_id']) {
            $builder->where('universities.id', '=', $user['university_id']);
        }

        if ($search) {
            $builder->where('courses.title', 'LIKE', "%{$search}%");
        }

        $courses = $builder->orderBy('courses.code', 'ASC')->get();

        $this->view('courses.index', [
            'page_title'   => 'Courses',
            'page_description' => 'Browse all available courses in your institution.',
            'user'         => $user,
            'user_role'    => $role,
            'courses'      => $courses,
            'search'       => $search,
        ]);
    }

    /**
     * View currently enrolled students and list of eligible students for enrollment.
     */
    public function enrollments(Request $request, string $courseId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $course = QueryBuilder::table('courses')
            ->where('id', '=', $courseId)
            ->first();

        if (!$course) {
            abort(404, 'Course not found.');
        }

        // Get currently enrolled students
        $enrollments = QueryBuilder::table('course_enrollments')
            ->join('users', 'course_enrollments.student_id', '=', 'users.id')
            ->where('course_enrollments.course_id', '=', $courseId)
            ->select([
                'course_enrollments.id as enrollment_id',
                'course_enrollments.status',
                'course_enrollments.enrolled_at',
                'users.id as student_id',
                'users.first_name',
                'users.last_name',
                'users.email',
            ])
            ->get();

        $auth = Auth::getInstance();
        $user = $auth->user();

        // Find students roles
        $studentRole = QueryBuilder::table('roles')->where('slug', '=', 'student')->first();
        $studentRoleId = $studentRole ? $studentRole['id'] : 0;

        // Query students not in course_enrollments
        $enrolledStudentIds = array_column($enrollments, 'student_id');

        $availableQuery = QueryBuilder::table('users')
            ->where('role_id', '=', $studentRoleId);

        if ($user['university_id']) {
            $availableQuery->where('university_id', '=', $user['university_id']);
        }

        if (!empty($enrolledStudentIds)) {
            $availableQuery->whereNotIn('id', $enrolledStudentIds);
        }

        $availableStudents = $availableQuery->get();

        $this->view('courses.enrollments', [
            'page_title'        => 'Enrollments — ' . $course['code'],
            'page_description'  => 'Manage student enrollments for ' . $course['title'],
            'course'            => $course,
            'enrollments'       => $enrollments,
            'availableStudents' => $availableStudents,
        ]);
    }

    /**
     * Enroll a student in the specified course.
     */
    public function enroll(Request $request, string $courseId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $studentId = $request->input('student_id');
        if (!$studentId) {
            $this->redirectWithError(url('/courses/' . $courseId . '/enrollments'), 'Please select a student.');
            return;
        }

        // Find active academic session for this institution
        $auth = Auth::getInstance();
        $user = $auth->user();

        $session = QueryBuilder::table('academic_sessions')
            ->where('university_id', '=', $user['university_id'])
            ->where('is_current', '=', 1)
            ->first();

        if (!$session) {
            $session = QueryBuilder::table('academic_sessions')->first();
        }

        $sessionId = $session ? $session['id'] : null;
        if (!$sessionId) {
            $sessionId = QueryBuilder::table('academic_sessions')->insertGetId([
                'university_id' => $user['university_id'] ?: 1,
                'name'          => '2026/2027 Academic Session',
                'is_current'    => 1,
            ]);
        }

        // Insert enrollment
        QueryBuilder::table('course_enrollments')->insert([
            'course_id'           => $courseId,
            'student_id'          => $studentId,
            'academic_session_id' => $sessionId,
            'status'              => 'enrolled',
        ]);

        $this->redirectWithSuccess(url('/courses/' . $courseId . '/enrollments'), 'Student enrolled successfully.');
    }

    /**
     * Drop a student from a course.
     */
    public function drop(Request $request, string $enrollmentId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $enrollment = QueryBuilder::table('course_enrollments')
            ->where('id', '=', $enrollmentId)
            ->first();

        if ($enrollment) {
            QueryBuilder::table('course_enrollments')
                ->where('id', '=', $enrollmentId)
                ->delete();
            $this->redirectWithSuccess(url('/courses/' . $enrollment['course_id'] . '/enrollments'), 'Student enrollment removed.');
        } else {
            $this->redirectWithError(url('/courses'), 'Enrollment record not found.');
        }
    }

    /**
     * Bulk enroll students using a CSV file.
     */
    public function bulkEnroll(Request $request, string $courseId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirectWithError(url('/courses/' . $courseId . '/enrollments'), 'Please upload a valid CSV file.');
            return;
        }

        $fileTmpPath = $_FILES['csv_file']['tmp_name'];

        $auth = Auth::getInstance();
        $user = $auth->user();

        $session = QueryBuilder::table('academic_sessions')
            ->where('university_id', '=', $user['university_id'])
            ->where('is_current', '=', 1)
            ->first();

        if (!$session) {
            $session = QueryBuilder::table('academic_sessions')->first();
        }

        $sessionId = $session ? $session['id'] : null;
        if (!$sessionId) {
            $sessionId = QueryBuilder::table('academic_sessions')->insertGetId([
                'university_id' => $user['university_id'] ?: 1,
                'name'          => '2026/2027 Academic Session',
                'is_current'    => 1,
            ]);
        }

        $enrollmentCount = 0;
        $failedCount = 0;

        if (($handle = fopen($fileTmpPath, 'r')) !== false) {
            // Read header
            $header = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row) || !isset($row[0])) continue;

                $identifier = trim($row[0]);
                if (empty($identifier)) continue;

                $student = QueryBuilder::table('users')
                    ->where('email', '=', $identifier)
                    ->orWhere('matric_staff_id', '=', $identifier)
                    ->first();

                if ($student) {
                    $alreadyEnrolled = QueryBuilder::table('course_enrollments')
                        ->where('course_id', '=', $courseId)
                        ->where('student_id', '=', $student['id'])
                        ->exists();

                    if (!$alreadyEnrolled) {
                        QueryBuilder::table('course_enrollments')->insert([
                            'course_id'           => $courseId,
                            'student_id'          => $student['id'],
                            'academic_session_id' => $sessionId,
                            'status'              => 'enrolled',
                        ]);
                        $enrollmentCount++;
                    }
                } else {
                    $failedCount++;
                }
            }
            fclose($handle);
        }

        if ($enrollmentCount > 0) {
            $msg = "Bulk enrollment successful: {$enrollmentCount} students enrolled.";
            if ($failedCount > 0) {
                $msg .= " ({$failedCount} student identifier(s) not found in system).";
            }
            $this->redirectWithSuccess(url('/courses/' . $courseId . '/enrollments'), $msg);
        } else {
            $this->redirectWithError(url('/courses/' . $courseId . '/enrollments'), 'No new students were enrolled. Make sure the CSV contains valid student emails or Matric IDs.');
        }
    }
}
