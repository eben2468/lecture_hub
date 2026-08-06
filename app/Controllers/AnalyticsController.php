<?php
/**
 * Nadics LectureHub — Analytics Controller
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;
use Core\QueryBuilder;
use App\Services\ReportingService;

class AnalyticsController extends Controller
{
    public function index(Request $request): void
    {
        $user = Auth::getInstance()->user();
        $role = Auth::getInstance()->role();

        $reportingService = new ReportingService();
        $attendanceStats = $reportingService->getAttendanceAnalytics();

        $stats = [
            'total_lectures'    => 0,
            'total_students'    => 0,
            'total_materials'   => 0,
            'total_assignments' => 0,
            'total_submissions' => 0,
            'live_now'          => 0,
            'attendance_rate'   => $attendanceStats['attendance_rate'],
        ];

        try {
            $stats['total_lectures']    = QueryBuilder::table('lectures')->count();
            $stats['total_students']    = QueryBuilder::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->where('roles.slug', '=', 'student')->count();
            $stats['total_materials']   = QueryBuilder::table('course_materials')->count();
            $stats['total_assignments'] = QueryBuilder::table('assignments')->count();
            $stats['total_submissions'] = QueryBuilder::table('assignment_submissions')->count();
            $stats['live_now']          = QueryBuilder::table('lectures')->where('status', '=', 'live')->count();
        } catch (\Exception $e) {
            // Graceful fallback
        }

        // Recent lectures chart data (last 7 days)
        $recentLectures = [];
        try {
            $recentLectures = QueryBuilder::table('lectures')
                ->join('courses', 'lectures.course_id', '=', 'courses.id')
                ->select(['lectures.*', 'courses.code as course_code', 'courses.title as course_title'])
                ->orderBy('lectures.scheduled_start', 'DESC')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {}

        $this->view('analytics.index', [
            'page_title'       => 'Analytics & Reports',
            'page_description' => 'Academic performance metrics and system analytics.',
            'user'             => $user,
            'user_role'        => $role,
            'stats'            => $stats,
            'attendance_stats' => $attendanceStats,
            'recentLectures'   => $recentLectures,
        ]);
    }

    /**
     * Download CSV report of attendance records.
     */
    public function exportAttendanceCSV(Request $request): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $reportingService = new ReportingService();
        $csvData = $reportingService->exportAttendanceCSV();

        $filename = 'slms_attendance_report_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');

        echo $csvData;
        exit;
    }
}
