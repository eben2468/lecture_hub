<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class LecturerController extends Controller
{
    /**
     * Display all lecturer accounts.
     */
    public function index(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        // Find role_id for lecturer
        $role = QueryBuilder::table('roles')->where('name', '=', 'lecturer')->first();
        $lecturerRoleId = $role ? $role['id'] : 2;

        $lecturers = QueryBuilder::table('users')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->where('users.role_id', '=', $lecturerRoleId)
            ->select([
                'users.*',
                'departments.name as department_name',
                'departments.code as department_code',
            ])
            ->orderBy('users.id', 'DESC')
            ->get();

        // Calculate allocated course counts for each lecturer
        foreach ($lecturers as &$lec) {
            $lec['course_count'] = QueryBuilder::table('course_lecturers')
                ->where('lecturer_id', '=', $lec['id'])
                ->count();
        }

        $departments = QueryBuilder::table('departments')->get();

        $this->view('admin.lecturers.index', [
            'page_title'       => 'Lecturer Management',
            'page_description' => 'Register and manage academic faculty members and workload assignments.',
            'lecturers'        => $lecturers,
            'departments'      => $departments,
        ]);
    }

    /**
     * Register a new lecturer account.
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

        $role = QueryBuilder::table('roles')->where('name', '=', 'lecturer')->first();
        $lecturerRoleId = $role ? $role['id'] : 2;

        $uni = QueryBuilder::table('universities')->first();
        $uniId = $uni ? $uni['id'] : 1;

        QueryBuilder::table('users')->insert([
            'university_id'   => $uniId,
            'department_id'   => $validated['department_id'],
            'role_id'         => $lecturerRoleId,
            'matric_staff_id' => trim($validated['matric_staff_id']),
            'first_name'      => trim($validated['first_name']),
            'last_name'       => trim($validated['last_name']),
            'email'           => strtolower(trim($validated['email'])),
            'password'        => password_hash($validated['password'], PASSWORD_DEFAULT),
            'is_active'       => 1,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        $this->redirectWithSuccess(url('/admin/lecturers'), 'Lecturer account registered successfully.');
    }

    /**
     * Toggle active status for a lecturer account.
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

        $this->redirectWithSuccess(url('/admin/lecturers'), 'Lecturer account status updated.');
    }
}
