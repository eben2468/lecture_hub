<?php
/**
 * Nadics LectureHub — Timetable Controller
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;
use Core\QueryBuilder;

class TimetableController extends Controller
{
    public function index(Request $request): void
    {
        $user = Auth::getInstance()->user();
        $role = Auth::getInstance()->role();

        $selectedDay = strtolower($request->query('day', 'all'));

        // Query lectures joined with courses, lecturers, and halls
        $query = QueryBuilder::table('lectures')
            ->join('courses', 'lectures.course_id', '=', 'courses.id')
            ->join('users', 'lectures.lecturer_id', '=', 'users.id')
            ->leftJoin('lecture_halls', 'lectures.lecture_hall_id', '=', 'lecture_halls.id')
            ->select([
                'lectures.*',
                'courses.code as course_code',
                'courses.title as course_title',
                'courses.credit_unit',
                'users.first_name as lecturer_first_name',
                'users.last_name as lecturer_last_name',
                'lecture_halls.name as hall_name',
                'lecture_halls.building as hall_building',
            ])
            ->orderBy('lectures.scheduled_start', 'ASC');

        if ($role === 'student') {
            // Filter by student's enrolled courses if course_enrollments populated
            try {
                $enrolledCourseIds = QueryBuilder::table('course_enrollments')
                    ->where('student_id', '=', $user['id'])
                    ->pluck('course_id');
                if (!empty($enrolledCourseIds)) {
                    $query->whereIn('lectures.course_id', $enrolledCourseIds);
                }
            } catch (\Exception $e) {}
        } elseif ($role === 'lecturer') {
            $query->where('lectures.lecturer_id', '=', $user['id']);
        }

        $allLectures = $query->get();

        // Organize lectures by day of the week
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $timetableGrid = [
            'Monday'    => [],
            'Tuesday'   => [],
            'Wednesday' => [],
            'Thursday'  => [],
            'Friday'    => [],
            'Saturday'  => [],
        ];

        foreach ($allLectures as $lec) {
            $startTime = strtotime($lec['scheduled_start']);
            $dayName = date('l', $startTime);
            if (isset($timetableGrid[$dayName])) {
                $timetableGrid[$dayName][] = $lec;
            }
        }

        $this->view('timetable.index', [
            'page_title'       => 'Interactive Class Timetable',
            'page_description' => 'Weekly lecture schedules, venues, and time slots.',
            'timetableGrid'    => $timetableGrid,
            'allLectures'      => $allLectures,
            'days'             => $days,
            'selectedDay'      => $selectedDay,
            'user'             => $user,
            'user_role'        => $role,
        ]);
    }
}
