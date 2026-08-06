<?php
/**
 * ============================================================
 * Nadics LectureHub — Reporting & Analytics Engine
 * ============================================================
 *
 * Calculates institutional attendance rates, student engagement
 * metrics, and formats CSV export reports.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Services
 * @author     Nadics Solutions
 * @version    1.0.0
 * ============================================================
 */

namespace App\Services;

use Core\QueryBuilder;

class ReportingService
{
    /**
     * Calculate aggregate attendance analytics.
     */
    public function getAttendanceAnalytics(?int $courseId = null): array
    {
        $records = QueryBuilder::table('attendance_records')
            ->join('attendance_sessions', 'attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('lectures', 'attendance_sessions.lecture_id', '=', 'lectures.id')
            ->select([
                'attendance_records.status',
                'lectures.course_id',
            ]);

        if ($courseId) {
            $records->where('lectures.course_id', '=', $courseId);
        }

        $all = $records->get();
        $total = count($all);
        $present = 0;

        foreach ($all as $rec) {
            if ($rec['status'] === 'present' || $rec['status'] === 'verified') {
                $present++;
            }
        }

        $rate = $total > 0 ? round(($present / $total) * 100, 1) : 100.0;

        return [
            'total_records'   => $total,
            'present_records' => $present,
            'absent_records'  => $total - $present,
            'attendance_rate' => $rate,
        ];
    }

    /**
     * Generate CSV content for attendance export.
     */
    public function exportAttendanceCSV(?int $courseId = null): string
    {
        $query = QueryBuilder::table('attendance_records')
            ->join('attendance_sessions', 'attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('users', 'attendance_records.student_id', '=', 'users.id')
            ->join('lectures', 'attendance_sessions.lecture_id', '=', 'lectures.id')
            ->join('courses', 'lectures.course_id', '=', 'courses.id')
            ->select([
                'users.matric_staff_id as student_id',
                'users.first_name',
                'users.last_name',
                'courses.code as course_code',
                'lectures.title as lecture_title',
                'attendance_records.verification_method',
                'attendance_records.status',
                'attendance_records.verified_at',
            ]);

        if ($courseId) {
            $query->where('courses.id', '=', $courseId);
        }

        $records = $query->orderBy('attendance_records.verified_at', 'DESC')->get();

        $output = "Matric Number,Student Name,Course Code,Lecture Title,Method,Status,Verified At\n";
        foreach ($records as $r) {
            $name = '"' . $r['first_name'] . ' ' . $r['last_name'] . '"';
            $title = '"' . str_replace('"', '""', $r['lecture_title']) . '"';
            $output .= "{$r['student_id']},{$name},{$r['course_code']},{$title},{$r['verification_method']},{$r['status']},{$r['verified_at']}\n";
        }

        return $output;
    }
}
