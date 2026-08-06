<?php
/**
 * ============================================================
 * Nadics LectureHub — Profile Controller
 * ============================================================
 *
 * Handles user profile viewing, personal detail updates,
 * profile photo uploads, and security password modifications.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Controllers
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;
use Core\QueryBuilder;

class ProfileController extends Controller
{
    /**
     * Display current user profile.
     *
     * @param  Request $request
     * @return void
     */
    public function show(Request $request): void
    {
        $auth = Auth::getInstance();
        $user = $auth->user();

        // Load university and department details
        $university = null;
        $department = null;

        if (!empty($user['university_id'])) {
            $university = QueryBuilder::table('universities')->where('id', '=', $user['university_id'])->first();
        }

        if (!empty($user['department_id'])) {
            $department = QueryBuilder::table('departments')->where('id', '=', $user['department_id'])->first();
        }

        // Fetch recent user activity logs
        $activities = QueryBuilder::table('activity_logs')
            ->where('user_id', '=', $user['id'])
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get();

        $this->view('profile.show', [
            'page_title'       => 'My Profile',
            'page_description' => 'Manage your personal account and security settings.',
            'user'             => $user,
            'university'       => $university,
            'department'       => $department,
            'activities'       => $activities,
        ]);
    }

    /**
     * Update user personal details.
     *
     * @param  Request $request
     * @return void
     */
    public function update(Request $request): void
    {
        $user = Auth::getInstance()->user();

        $validated = $this->validate($request, [
            'first_name' => 'required|min:2|max:100',
            'last_name'  => 'required|min:2|max:100',
            'phone'      => 'nullable|min:10|max:20',
            'gender'     => 'nullable|in:male,female,other',
        ]);

        // Sanitize gender: empty string must become NULL to avoid ENUM truncation
        $gender = (!empty($validated['gender']) && in_array($validated['gender'], ['male', 'female', 'other']))
            ? $validated['gender']
            : null;

        QueryBuilder::table('users')
            ->where('id', '=', $user['id'])
            ->update([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'phone'      => (!empty($validated['phone'])) ? $validated['phone'] : null,
                'gender'     => $gender,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Refresh cached user data in session
        Auth::getInstance()->refresh();

        $this->backWithSuccess('Profile details updated successfully.');
    }

    /**
     * Upload and update user profile photo.
     *
     * @param  Request $request
     * @return void
     */
    public function updatePhoto(Request $request): void
    {
        $user = Auth::getInstance()->user();

        if (!$request->hasFile('profile_photo')) {
            $this->backWithErrors(['profile_photo' => ['Please select an image file to upload.']]);
        }

        $file = $request->file('profile_photo');

        // Check error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->backWithErrors(['profile_photo' => ['File upload failed. Please try again.']]);
        }

        // Validate MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileMime = mime_content_type($file['tmp_name']);

        if (!in_array($fileMime, $allowedMimes)) {
            $this->backWithErrors(['profile_photo' => ['Only JPG, PNG, WebP, and GIF images are allowed.']]);
        }

        // Validate size (max 5MB)
        if ($file['size'] > 5242880) {
            $this->backWithErrors(['profile_photo' => ['Profile photo size must not exceed 5MB.']]);
        }

        // Generate filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $user['id'] . '_' . time() . '.' . $ext;
        $uploadDir = BASE_PATH . '/public/uploads/profiles';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $photoUrl = 'uploads/profiles/' . $filename;

            // Delete old photo if exists
            if (!empty($user['profile_photo']) && file_exists(BASE_PATH . '/public/' . $user['profile_photo'])) {
                @unlink(BASE_PATH . '/public/' . $user['profile_photo']);
            }

            QueryBuilder::table('users')
                ->where('id', '=', $user['id'])
                ->update([
                    'profile_photo' => $photoUrl,
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);

            Auth::getInstance()->refresh();

            $this->backWithSuccess('Profile photo updated successfully.');
        }

        $this->backWithErrors(['profile_photo' => ['Failed to save uploaded file.']]);
    }

    /**
     * Update user password.
     *
     * @param  Request $request
     * @return void
     */
    public function updatePassword(Request $request): void
    {
        $user = Auth::getInstance()->user();

        $validated = $this->validate($request, [
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        // Verify current password
        if (!password_verify($validated['current_password'], $user['password'])) {
            $this->backWithErrors(['current_password' => ['Your current password is incorrect.']]);
        }

        // Hash and update new password
        $newHash = password_hash($validated['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        QueryBuilder::table('users')
            ->where('id', '=', $user['id'])
            ->update([
                'password'   => $newHash,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        Auth::getInstance()->refresh();

        $this->backWithSuccess('Password changed successfully.');
    }
}
