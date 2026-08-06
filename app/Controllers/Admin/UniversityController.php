<?php
/**
 * ============================================================
 * Nadics LectureHub — Administrative University Controller
 * ============================================================
 *
 * Manages university onboarding, institution directory listings,
 * domain configuration, and active status governance.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Controllers\Admin
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;
use Core\Auth;

class UniversityController extends Controller
{
    /**
     * Display directory of universities.
     *
     * @param  Request $request
     * @return void
     */
    public function index(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = QueryBuilder::table('universities');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        if ($status) {
            $query->where('status', '=', $status);
        }

        $universities = $query->orderBy('id', 'DESC')->get();

        $this->view('admin.universities.index', [
            'page_title'       => 'University Management',
            'page_description' => 'Onboard and manage higher education institutions.',
            'universities'     => $universities,
            'search'           => $search,
            'status'           => $status,
        ]);
    }

    /**
     * Store a newly created university.
     *
     * @param  Request $request
     * @return void
     */
    public function store(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $validated = $this->validate($request, [
            'name'    => 'required|min:3|max:255',
            'code'    => 'required|min:2|max:50|unique:universities,code',
            'domain'  => 'nullable|max:255|unique:universities,domain',
            'city'    => 'nullable|max:100',
            'state'   => 'nullable|max:100',
            'status'  => 'required|in:active,inactive,suspended',
        ]);

        $id = QueryBuilder::table('universities')->insert([
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'domain'     => strtolower($validated['domain'] ?? ''),
            'city'       => $validated['city'] ?? null,
            'state'      => $validated['state'] ?? null,
            'country'    => 'Nigeria',
            'status'     => $validated['status'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('university_created', "Created university: {$validated['name']} ({$validated['code']})");

        $this->redirectWithSuccess(url('/admin/universities'), 'University onboarded successfully.');
    }

    /**
     * Update an existing university.
     *
     * @param  Request $request
     * @param  string  $id
     * @return void
     */
    public function update(Request $request, string $id): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $university = QueryBuilder::table('universities')->where('id', '=', $id)->first();
        if (!$university) {
            $this->redirectWithError(url('/admin/universities'), 'University not found.');
        }

        $validated = $this->validate($request, [
            'name'   => 'required|min:3|max:255',
            'city'   => 'nullable|max:100',
            'state'  => 'nullable|max:100',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        QueryBuilder::table('universities')
            ->where('id', '=', $id)
            ->update([
                'name'       => $validated['name'],
                'city'       => $validated['city'] ?? null,
                'state'      => $validated['state'] ?? null,
                'status'     => $validated['status'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->logActivity('university_updated', "Updated university ID: {$id}");

        $this->redirectWithSuccess(url('/admin/universities'), 'University updated successfully.');
    }

    /**
     * Log administrative activity.
     */
    private function logActivity(string $action, string $description): void
    {
        try {
            $userId = Auth::getInstance()->id();
            QueryBuilder::table('activity_logs')->insert([
                'user_id'     => $userId,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {}
    }
}
