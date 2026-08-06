<?php
/**
 * Nadics LectureHub — Administrative System Settings Controller
 */

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;
use Core\Database;

class SettingsController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $settings = [];
        try {
            $settings = QueryBuilder::table('system_settings')->get();
        } catch (\Exception $e) {
            \Core\Logger::getInstance()->warning('Settings query failed', ['error' => $e->getMessage()]);
        }

        $this->view('admin.settings.index', [
            'page_title'       => 'System Settings',
            'page_description' => 'Configure platform parameters, security, and integration keys.',
            'settings'         => $settings,
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorize(['university_admin', 'super_admin']);

        $keys = [
            'stream_bitrate',
            'audio_quality',
            'ai_model',
            'ai_max_tokens',
            'academic_year',
            'allow_student_self_registration'
        ];

        foreach ($keys as $k) {
            $val = trim($request->input($k, ''));
            if ($val !== '') {
                Database::query("
                    INSERT INTO `system_settings` (`setting_key`, `setting_value`)
                    VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = ?
                ", [$k, $val, $val]);
            }
        }

        $this->redirectWithSuccess(url('/admin/settings'), 'Global system settings saved successfully.');
    }
}
