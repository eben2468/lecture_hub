<?php
/**
 * ============================================================
 * Nadics LectureHub — Administrative System Setting Controller
 * ============================================================
 */

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;

class SettingController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['super_admin']);

        $settings = QueryBuilder::table('system_settings')->get();

        $this->view('admin.settings.index', [
            'page_title'       => 'System Settings',
            'page_description' => 'Configure platform parameters, security, and integration keys.',
            'settings'         => $settings,
        ]);
    }

    /**
     * Save dynamic application settings.
     */
    public function update(Request $request): void
    {
        $this->authorize(['super_admin']);

        $allInputs = $request->all();
        unset($allInputs['csrf_token']);

        foreach ($allInputs as $key => $value) {
            $exists = QueryBuilder::table('system_settings')
                ->where('setting_key', '=', $key)
                ->exists();

            if ($exists) {
                QueryBuilder::table('system_settings')
                    ->where('setting_key', '=', $key)
                    ->update([
                        'setting_value' => $value,
                    ]);
            } else {
                QueryBuilder::table('system_settings')->insert([
                    'setting_key'   => $key,
                    'setting_value' => $value,
                    'group_name'    => 'general',
                ]);
            }
        }

        $this->redirectWithSuccess(url('/admin/settings'), 'System settings updated successfully.');
    }
}
