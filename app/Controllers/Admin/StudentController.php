<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class StudentController extends Controller
{
    /**
     * Display all student accounts.
     */
    public function index(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        // Find role_id for student
        $role = QueryBuilder::table('roles')->where('name', '=', 'student')->first();
        $studentRoleId = $role ? $role['id'] : 4;

        $students = QueryBuilder::table('users')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->where('users.role_id', '=', $studentRoleId)
            ->select([
                'users.*',
                'departments.name as department_name',
                'departments.code as department_code',
            ])
            ->orderBy('users.id', 'DESC')
            ->get();

        // Calculate course enrollment count for each student
        foreach ($students as &$st) {
            $st['enrollment_count'] = QueryBuilder::table('course_enrollments')
                ->where('student_id', '=', $st['id'])
                ->where('status', '=', 'enrolled')
                ->count();
        }

        $departments = QueryBuilder::table('departments')->get();
        $programmes = QueryBuilder::table('programmes')->get();

        $this->view('admin.students.index', [
            'page_title'       => 'Student Management',
            'page_description' => 'Register and manage student matriculation records and enrollments.',
            'students'         => $students,
            'departments'      => $departments,
            'programmes'       => $programmes,
        ]);
    }

    /**
     * Register a new student account.
     */
    public function store(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $validated = $this->validate($request, [
            'first_name'      => 'required|min:2|max:100',
            'last_name'       => 'required|min:2|max:100',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|min:6',
            'matric_staff_id' => 'required|max:50',
            'department_id'   => 'required|integer',
        ]);

        $role = QueryBuilder::table('roles')->where('name', '=', 'student')->first();
        $studentRoleId = $role ? $role['id'] : 4;

        $uni = QueryBuilder::table('universities')->first();
        $uniId = $uni ? $uni['id'] : 1;

        QueryBuilder::table('users')->insert([
            'university_id'   => $uniId,
            'department_id'   => $validated['department_id'],
            'role_id'         => $studentRoleId,
            'matric_staff_id' => trim($validated['matric_staff_id']),
            'first_name'      => trim($validated['first_name']),
            'last_name'       => trim($validated['last_name']),
            'email'           => strtolower(trim($validated['email'])),
            'password'        => password_hash($validated['password'], PASSWORD_DEFAULT),
            'is_active'       => 1,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        $this->redirectWithSuccess(url('/admin/students'), 'Student account registered successfully.');
    }

    /**
     * Toggle active status for a student account.
     */
    public function toggleStatus(Request $request, string $id): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $user = QueryBuilder::table('users')->where('id', '=', $id)->first();
        if ($user) {
            $newStatus = ((int)($user['is_active'] ?? 1) === 1) ? 0 : 1;
            QueryBuilder::table('users')->where('id', '=', $id)->update([
                'is_active' => $newStatus,
            ]);
        }

        $this->redirectWithSuccess(url('/admin/students'), 'Student account status updated.');
    }
}
