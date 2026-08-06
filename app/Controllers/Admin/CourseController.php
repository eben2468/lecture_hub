<?php
/**
 * ============================================================
 * Nadics LectureHub — Administrative Course Controller
 * ============================================================
 */

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class CourseController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin', 'lecturer']);

        $courses = QueryBuilder::table('courses')
            ->join('departments', 'courses.department_id', '=', 'departments.id')
            ->select([
                'courses.*',
                'departments.name as department_name',
                'departments.code as department_code',
            ])
            ->orderBy('courses.code', 'ASC')
            ->get();

        $departments = QueryBuilder::table('departments')->get();
        $lecturers   = QueryBuilder::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.slug', '=', 'lecturer')
            ->select(['users.id', 'users.first_name', 'users.last_name', 'users.matric_staff_id'])
            ->get();

        $this->view('admin.courses.index', [
            'page_title'       => 'Course Registry',
            'page_description' => 'Manage academic course catalog and lecturer assignments.',
            'courses'          => $courses,
            'departments'      => $departments,
            'lecturers'        => $lecturers,
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $validated = $this->validate($request, [
            'department_id' => 'required|integer',
            'code'          => 'required|min:2|max:50',
            'title'         => 'required|min:3|max:255',
            'description'   => 'nullable',
            'credit_unit'   => 'required|integer|min:1|max:6',
            'level'         => 'required|integer|in:100,200,300,400,500,700,800',
            'semester'      => 'required|in:first,second',
        ]);

        // Check duplicate course code constraint
        $codeUpper = strtoupper(trim($validated['code']));
        $exists = QueryBuilder::table('courses')->where('code', '=', $codeUpper)->exists();
        if ($exists) {
            $this->redirectWithError(url('/admin/courses'), "Constraint Error: A course with code '{$codeUpper}' already exists.");
            return;
        }

        QueryBuilder::table('courses')->insert([
            'department_id' => $validated['department_id'],
            'code'          => $codeUpper,
            'title'         => trim($validated['title']),
            'description'   => $validated['description'] ?? null,
            'credit_unit'   => $validated['credit_unit'],
            'level'         => $validated['level'],
            'semester'      => $validated['semester'],
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->redirectWithSuccess(url('/admin/courses'), 'Course added to registry successfully.');
    }

    /**
     * Delete a course from the registry.
     */
    public function destroy(Request $request, string $id): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        // Clean up allocations and materials
        QueryBuilder::table('course_lecturers')->where('course_id', '=', $id)->delete();
        QueryBuilder::table('courses')->where('id', '=', $id)->delete();

        if ($request->ajax()) {
            $this->jsonSuccess('Course deleted from registry.');
            return;
        }

        $this->redirectWithSuccess(url('/admin/courses'), 'Course deleted from registry.');
    }

    /**
     * Allocate a course to a lecturer directly from course registry.
     */
    public function allocate(Request $request, string $id): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $lecturerIdInput = $request->input('lecturer_id') ?? $request->query('lecturer_id');
        if (empty($lecturerIdInput)) {
            $this->redirectWithError(url('/admin/courses'), 'Please select a lecturer to allocate.');
            return;
        }

        $request->merge(['lecturer_id' => $lecturerIdInput]);

        $validated = $this->validate($request, [
            'lecturer_id' => 'required|integer',
        ]);

        $lecturerId = (int)$validated['lecturer_id'];
        $isCoordinator = $request->has('is_coordinator') ? 1 : 0;

        // Check if lecturer exists and is active
        $lecturer = QueryBuilder::table('users')->where('id', '=', $lecturerId)->first();
        if (!$lecturer || (int)($lecturer['is_active'] ?? 1) !== 1) {
            if ($request->ajax()) {
                $this->jsonError('Constraint Error: Lecturer account is inactive or invalid.', 400);
                return;
            }
            $this->redirectWithError(url('/admin/courses'), 'Constraint Error: Lecturer account is inactive or invalid.');
            return;
        }

        $sessionRecord = QueryBuilder::table('academic_sessions')->where('is_current', '=', 1)->first()
            ?? QueryBuilder::table('academic_sessions')->first();
        $sessionId = $sessionRecord ? $sessionRecord['id'] : 1;

        // Check duplicate allocation constraint
        $existing = QueryBuilder::table('course_lecturers')
            ->where('course_id', '=', $id)
            ->where('lecturer_id', '=', $lecturerId)
            ->where('academic_session_id', '=', $sessionId)
            ->exists();

        if ($existing) {
            if ($request->ajax()) {
                $this->jsonError('Constraint Error: Lecturer is already allocated to this course for the active session.', 400);
                return;
            }
            $this->redirectWithError(url('/admin/courses'), 'Constraint Error: Lecturer is already allocated to this course for the active academic session.');
            return;
        }

        QueryBuilder::table('course_lecturers')->insert([
            'course_id'           => $id,
            'lecturer_id'         => $lecturerId,
            'academic_session_id' => $sessionId,
            'is_coordinator'      => $isCoordinator,
            'created_at'          => date('Y-m-d H:i:s'),
        ]);

        if ($request->ajax()) {
            $this->jsonSuccess('Course allocated to lecturer successfully.');
            return;
        }

        $this->redirectWithSuccess(url('/admin/courses'), 'Course allocated to lecturer successfully.');
    }
}
