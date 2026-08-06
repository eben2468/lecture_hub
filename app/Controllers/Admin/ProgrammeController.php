<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class ProgrammeController extends Controller
{
    /**
     * Display all academic programmes.
     */
    public function index(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $programmes = QueryBuilder::table('programmes')
            ->join('departments', 'programmes.department_id', '=', 'departments.id')
            ->select([
                'programmes.*',
                'departments.name as department_name',
                'departments.code as department_code',
            ])
            ->orderBy('programmes.id', 'DESC')
            ->get();

        $departments = QueryBuilder::table('departments')->get();

        $this->view('admin.programmes.index', [
            'page_title'       => 'Programme Management',
            'page_description' => 'Configure degree programmes, majors, and academic qualifications.',
            'programmes'       => $programmes,
            'departments'      => $departments,
        ]);
    }

    /**
     * Store a newly created academic programme.
     */
    public function store(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $validated = $this->validate($request, [
            'department_id'  => 'required|integer',
            'code'           => 'required|min:2|max:50',
            'title'          => 'required|min:3|max:255',
            'degree_type'    => 'required|max:50',
            'duration_years' => 'required|integer|min:1|max:7',
        ]);

        QueryBuilder::table('programmes')->insert([
            'department_id'  => $validated['department_id'],
            'code'           => strtoupper(trim($validated['code'])),
            'title'          => trim($validated['title']),
            'degree_type'    => trim($validated['degree_type']),
            'duration_years' => $validated['duration_years'],
            'status'         => 'active',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->redirectWithSuccess(url('/admin/programmes'), 'Academic programme created successfully.');
    }

    /**
     * Update an academic programme.
     */
    public function update(Request $request, string $id): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $validated = $this->validate($request, [
            'department_id'  => 'required|integer',
            'code'           => 'required|min:2|max:50',
            'title'          => 'required|min:3|max:255',
            'degree_type'    => 'required|max:50',
            'duration_years' => 'required|integer|min:1|max:7',
        ]);

        QueryBuilder::table('programmes')->where('id', '=', $id)->update([
            'department_id'  => $validated['department_id'],
            'code'           => strtoupper(trim($validated['code'])),
            'title'          => trim($validated['title']),
            'degree_type'    => trim($validated['degree_type']),
            'duration_years' => $validated['duration_years'],
        ]);

        $this->redirectWithSuccess(url('/admin/programmes'), 'Academic programme updated successfully.');
    }

    /**
     * Delete an academic programme.
     */
    public function destroy(Request $request, string $id): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        QueryBuilder::table('programmes')->where('id', '=', $id)->delete();

        $this->redirectWithSuccess(url('/admin/programmes'), 'Academic programme deleted successfully.');
    }
}
