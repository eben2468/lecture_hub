<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class CourseAllocationController extends Controller
{
    /**
     * Display all course allocations.
     */
    public function index(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $allocations = QueryBuilder::table('course_lecturers')
            ->join('courses', 'course_lecturers.course_id', '=', 'courses.id')
            ->join('users', 'course_lecturers.lecturer_id', '=', 'users.id')
            ->select([
                'course_lecturers.*',
                'courses.code as course_code',
                'courses.title as course_title',
                'courses.credit_unit as unit_load',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.matric_staff_id',
            ])
            ->orderBy('course_lecturers.id', 'DESC')
            ->get();

        // Get lecturers
        $lecturers = QueryBuilder::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.name', '=', 'lecturer')
            ->select(['users.id', 'users.first_name', 'users.last_name', 'users.matric_staff_id', 'users.email'])
            ->get();

        // Get active courses
        $courses = QueryBuilder::table('courses')
            ->where('status', '=', 'active')
            ->select(['id', 'code', 'title'])
            ->get();

        $sessions = QueryBuilder::table('academic_sessions')->get();
        if (empty($sessions)) {
            $sessions = [
                ['id' => 1, 'name' => '2025/2026 Academic Session', 'is_current' => 1]
            ];
        }

        $this->view('admin.course_allocations.index', [
            'page_title'       => 'Course Allocation Management',
            'page_description' => 'Assign lecturers to courses and manage course coordinators.',
            'allocations'      => $allocations,
            'lecturers'        => $lecturers,
            'courses'          => $courses,
            'sessions'         => $sessions,
        ]);
    }

    /**
     * Store a course allocation.
     */
    public function store(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $validated = $this->validate($request, [
            'course_id'           => 'required|integer',
            'lecturer_id'         => 'required|integer',
            'academic_session_id' => 'required|integer',
        ]);

        $isCoordinator = $request->has('is_coordinator') ? 1 : 0;
        $sessionId = (int)$validated['academic_session_id'];
        if ($sessionId <= 0) {
            $sessionRecord = QueryBuilder::table('academic_sessions')->where('is_current', '=', 1)->first()
                ?? QueryBuilder::table('academic_sessions')->first();
            $sessionId = $sessionRecord ? (int)$sessionRecord['id'] : 1;
        }

        // Check if allocation already exists
        $existing = QueryBuilder::table('course_lecturers')
            ->where('course_id', '=', $validated['course_id'])
            ->where('lecturer_id', '=', $validated['lecturer_id'])
            ->where('academic_session_id', '=', $sessionId)
            ->exists();

        if ($existing) {
            $this->redirectWithError(url('/admin/course-allocations'), 'Constraint Error: This lecturer is already allocated to this course for the selected session.');
            return;
        }

        QueryBuilder::table('course_lecturers')->insert([
            'course_id'           => $validated['course_id'],
            'lecturer_id'         => $validated['lecturer_id'],
            'academic_session_id' => $sessionId,
            'is_coordinator'      => $isCoordinator,
            'created_at'          => date('Y-m-d H:i:s'),
        ]);

        $this->redirectWithSuccess(url('/admin/course-allocations'), 'Course allocated to lecturer successfully.');
    }

    /**
     * Remove a course allocation.
     */
    public function destroy(Request $request, string $id): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        QueryBuilder::table('course_lecturers')->where('id', '=', $id)->delete();

        $this->redirectWithSuccess(url('/admin/course-allocations'), 'Course allocation removed successfully.');
    }
}
