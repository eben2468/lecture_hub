<?php
/**
 * Nadics LectureHub — Administrative Audit Logs Controller
 */

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class AuditLogController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $logs = [];
        try {
            $logs = QueryBuilder::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->select([
                    'audit_logs.*',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                ])
                ->orderBy('audit_logs.id', 'DESC')
                ->limit(100)
                ->get();
        } catch (\Exception $e) {
            \Core\Logger::getInstance()->warning('Audit log query failed', ['error' => $e->getMessage()]);
        }

        $this->view('admin.audit_logs.index', [
            'page_title'       => 'System Audit Trail',
            'page_description' => 'Monitor user activities, course creations, and audio stream sessions.',
            'logs'             => $logs,
        ]);
    }
}
