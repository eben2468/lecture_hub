<?php
/**
 * ============================================================
 * Nadics LectureHub — Administrative Faculty Controller
 * ============================================================
 */

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class FacultyController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $faculties = QueryBuilder::table('faculties')
            ->join('universities', 'faculties.university_id', '=', 'universities.id')
            ->select([
                'faculties.*',
                'universities.name as university_name',
                'universities.code as university_code',
            ])
            ->orderBy('faculties.id', 'DESC')
            ->get();

        $universities = QueryBuilder::table('universities')->where('status', '=', 'active')->get();

        $this->view('admin.faculties.index', [
            'page_title'       => 'Faculty Management',
            'page_description' => 'Manage university faculties and colleges.',
            'faculties'        => $faculties,
            'universities'     => $universities,
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorize(['super_admin', 'university_admin']);

        $validated = $this->validate($request, [
            'university_id' => 'required|integer|exists:universities,id',
            'name'          => 'required|min:2|max:255',
            'code'          => 'required|min:2|max:50',
            'dean_name'     => 'nullable|max:150',
        ]);

        QueryBuilder::table('faculties')->insert([
            'university_id' => $validated['university_id'],
            'name'          => $validated['name'],
            'code'          => strtoupper($validated['code']),
            'dean_name'     => $validated['dean_name'] ?? null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->redirectWithSuccess(url('/admin/faculties'), 'Faculty added successfully.');
    }
}
