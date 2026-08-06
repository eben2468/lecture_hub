<?php
/**
 * ============================================================
 * Nadics LectureHub — Administrative User Controller
 * ============================================================
 */

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $roleSlug = $request->query('role', '');
        $search   = $request->query('search', '');

        $query = QueryBuilder::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select([
                'users.*',
                'roles.name as role_name',
                'roles.slug as role_slug',
            ]);

        if ($roleSlug) {
            $query->where('roles.slug', '=', $roleSlug);
        }

        if ($search) {
            $query->where('users.first_name', 'LIKE', "%{$search}%")
                  ->orWhere('users.last_name', 'LIKE', "%{$search}%")
                  ->orWhere('users.email', 'LIKE', "%{$search}%")
                  ->orWhere('users.matric_staff_id', 'LIKE', "%{$search}%");
        }

        $users = $query->orderBy('users.id', 'DESC')->get();
        $roles = QueryBuilder::table('roles')->get();

        $this->view('admin.users.index', [
            'page_title'       => 'User Directory',
            'page_description' => 'Manage system users, roles, and access status.',
            'users'            => $users,
            'roles'            => $roles,
            'selectedRole'     => $roleSlug,
            'search'           => $search,
        ]);
    }

    public function toggleStatus(Request $request, string $id): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $user = QueryBuilder::table('users')->where('id', '=', $id)->first();
        if ($user) {
            $newStatus = ($user['is_active'] ?? 0) == 1 ? 0 : 1;
            QueryBuilder::table('users')->where('id', '=', $id)->update([
                'is_active'  => $newStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->redirectWithSuccess(url('/admin/users'), 'User status updated successfully.');
        }

        $this->redirectWithError(url('/admin/users'), 'User not found.');
    }

    /**
     * Display system activity audit logs.
     */
    public function auditLogs(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $logs = [];
        try {
            $logs = QueryBuilder::table('activity_logs')
                ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
                ->select([
                    'activity_logs.*',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                ])
                ->orderBy('activity_logs.id', 'DESC')
                ->limit(50)
                ->get();
        } catch (\Exception $e) {}

        $this->view('admin.audit_logs.index', [
            'page_title'       => 'Audit Logs',
            'page_description' => 'Track system-wide activity, student attendance sessions, and resource updates.',
            'logs'             => $logs,
        ]);
    }
}
