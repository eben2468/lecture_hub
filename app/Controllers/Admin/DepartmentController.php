<?php
/**
 * ============================================================
 * Nadics LectureHub — Administrative Department Controller
 * ============================================================
 */

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class DepartmentController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $departments = QueryBuilder::table('departments')
            ->join('faculties', 'departments.faculty_id', '=', 'faculties.id')
            ->select([
                'departments.*',
                'faculties.name as faculty_name',
                'faculties.code as faculty_code',
            ])
            ->orderBy('departments.id', 'DESC')
            ->get();

        $faculties = QueryBuilder::table('faculties')->get();

        $this->view('admin.departments.index', [
            'page_title'       => 'Department Management',
            'page_description' => 'Manage academic departments.',
            'departments'      => $departments,
            'faculties'        => $faculties,
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $validated = $this->validate($request, [
            'faculty_id' => 'required|integer|exists:faculties,id',
            'name'       => 'required|min:2|max:255',
            'code'       => 'required|min:2|max:50',
            'hod_name'   => 'nullable|max:150',
        ]);

        QueryBuilder::table('departments')->insert([
            'faculty_id' => $validated['faculty_id'],
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'hod_name'   => $validated['hod_name'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->redirectWithSuccess(url('/admin/departments'), 'Department created successfully.');
    }
}
