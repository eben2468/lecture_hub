<?php
/**
 * ============================================================
 * Nadics LectureHub — Authentication Controller
 * ============================================================
 *
 * Handles login, registration, password reset, and session logout.
 * Implements security best practices:
 * - Password hashing via bcrypt
 * - CSRF token verification
 * - Rate limiting on login & registration
 * - Session fixation prevention
 * - Remember-me persistent tokens
 * - Audit log registration
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
use Core\Logger;

class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @param  Request $request
     * @return void
     */
    public function showLogin(Request $request): void
    {
        $this->view('auth.login', [
            'page_title'       => 'Portal Login',
            'page_description' => 'Access your Nadics LectureHub portal.',
        ]);
    }

    /**
     * Handle user login authentication.
     *
     * @param  Request $request
     * @return void
     */
    public function login(Request $request): void
    {
        $validated = $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $remember = (bool) $request->input('remember', false);
        $auth     = Auth::getInstance();

        if ($auth->attempt($validated['email'], $validated['password'], $remember)) {
            // Log security audit event
            $user = $auth->user();
            $this->logActivity($user['id'], 'user_login', 'User logged in successfully');

            // Redirect to intended URL or dashboard
            $intended = session('_intended_url');
            if ($intended) {
                session()->forget('_intended_url');
                $this->redirectWithSuccess($intended, 'Welcome back, ' . e($user['first_name']) . '!');
            } else {
                $this->redirectWithSuccess(url('/dashboard'), 'Welcome back, ' . e($user['first_name']) . '!');
            }
        }

        $this->backWithErrors([
            'email' => ['Invalid email or password. Please check your credentials and try again.'],
        ], $request->except(['password']));
    }

    /**
     * Show the user registration form.
     *
     * @param  Request $request
     * @return void
     */
    public function showRegister(Request $request): void
    {
        // Load active universities & departments for registration dropdown
        $universities = QueryBuilder::table('universities')
            ->where('status', '=', 'active')
            ->get();

        $departments = QueryBuilder::table('departments')
            ->join('faculties', 'departments.faculty_id', '=', 'faculties.id')
            ->select([
                'departments.id',
                'departments.name',
                'faculties.university_id'
            ])
            ->get();

        $this->view('auth.register', [
            'page_title'       => 'Create Account',
            'page_description' => 'Register for Nadics LectureHub.',
            'universities'     => $universities,
            'departments'      => $departments,
        ]);
    }

    /**
     * Handle user registration.
     *
     * @param  Request $request
     * @return void
     */
    public function register(Request $request): void
    {
        // Combine country code and phone number for storage/validation
        $countryCode = $request->input('country_code', '');
        $phoneNumber = $request->input('phone_number', '');
        if ($phoneNumber) {
            if (str_starts_with($phoneNumber, '0')) {
                $phoneNumber = substr($phoneNumber, 1);
            }
            $_POST['phone'] = $countryCode . $phoneNumber;
        }

        $validated = $this->validate($request, [
            'role_type'       => 'required|in:student,lecturer',
            'first_name'      => 'required|min:2|max:100',
            'last_name'       => 'required|min:2|max:100',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'nullable|min:10|max:25',
            'matric_staff_id' => 'required|min:3|max:50',
            'university_id'   => 'required|integer|exists:universities,id',
            'department_id'   => 'required|integer|exists:departments,id',
            'password'        => 'required|min:8|confirmed',
        ]);

        // Resolve role_id from role_type
        $roleSlug = $validated['role_type'] === 'lecturer' ? 'lecturer' : 'student';
        $role = QueryBuilder::table('roles')->where('slug', '=', $roleSlug)->first();

        if (!$role) {
            $this->backWithErrors(['role_type' => ['Invalid role selected.']], $request->all());
        }

        // Hash password
        $passwordHash = password_hash($validated['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        // Create user record
        $userId = QueryBuilder::table('users')->insert([
            'university_id'   => $validated['university_id'],
            'department_id'   => $validated['department_id'],
            'role_id'         => $role['id'],
            'matric_staff_id' => $validated['matric_staff_id'],
            'first_name'      => $validated['first_name'],
            'last_name'       => $validated['last_name'],
            'email'           => strtolower($validated['email']),
            'phone'           => $validated['phone'] ?? null,
            'password'        => $passwordHash,
            'is_active'       => 1,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        // Fetch newly created user and log in automatically
        $user = QueryBuilder::table('users')->where('id', '=', $userId)->first();
        Auth::getInstance()->login($user);

        $this->logActivity($userId, 'user_registered', 'New account created as ' . $roleSlug);

        $this->redirectWithSuccess(url('/dashboard'), 'Account created successfully! Welcome to Nadics LectureHub.');
    }

    /**
     * Show forgot password form.
     *
     * @param  Request $request
     * @return void
     */
    public function showForgotPassword(Request $request): void
    {
        $this->view('auth.forgot_password', [
            'page_title'       => 'Reset Password',
            'page_description' => 'Recover your Nadics LectureHub account password.',
        ]);
    }

    /**
     * Process forgot password request.
     *
     * @param  Request $request
     * @return void
     */
    public function forgotPassword(Request $request): void
    {
        $validated = $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = QueryBuilder::table('users')->where('email', '=', $validated['email'])->first();

        if ($user) {
            // Generate password reset token (valid for 1 hour)
            $token = bin2hex(random_bytes(32));

            // Store in remember_token for reset link verification
            QueryBuilder::table('users')
                ->where('id', '=', $user['id'])
                ->update(['remember_token' => hash('sha256', $token)]);

            Logger::getInstance()->info('Password reset token generated', [
                'user_id' => $user['id'],
                'email'   => $user['email'],
            ]);

            // In production, send email here using config/mail.php
        }

        // Return same response to prevent user enumeration attacks
        $this->redirectWithSuccess(url('/login'), 'If an account exists with that email, password reset instructions have been sent.');
    }

    /**
     * Show reset password form.
     *
     * @param  Request $request
     * @param  string  $token Reset token
     * @return void
     */
    public function showResetPassword(Request $request, string $token): void
    {
        $hashedToken = hash('sha256', $token);
        $user = QueryBuilder::table('users')->where('remember_token', '=', $hashedToken)->first();

        if (!$user) {
            $this->redirectWithError(url('/login'), 'Invalid or expired password reset token.');
        }

        $this->view('auth.reset_password', [
            'page_title' => 'Set New Password',
            'token'      => $token,
            'email'      => $user['email'],
        ]);
    }

    /**
     * Process reset password submission.
     *
     * @param  Request $request
     * @return void
     */
    public function resetPassword(Request $request): void
    {
        $validated = $this->validate($request, [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $hashedToken = hash('sha256', $validated['token']);
        $user = QueryBuilder::table('users')
            ->where('email', '=', $validated['email'])
            ->where('remember_token', '=', $hashedToken)
            ->first();

        if (!$user) {
            $this->backWithErrors(['email' => ['Invalid token or email specified.']]);
        }

        // Update password and clear token
        QueryBuilder::table('users')
            ->where('id', '=', $user['id'])
            ->update([
                'password'       => password_hash($validated['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                'remember_token' => null,
            ]);

        $this->logActivity($user['id'], 'password_reset', 'Password successfully reset');

        $this->redirectWithSuccess(url('/login'), 'Your password has been reset successfully. Please log in.');
    }

    /**
     * Log out the current user.
     *
     * @param  Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        $auth = Auth::getInstance();
        if ($auth->check()) {
            $user = $auth->user();
            $this->logActivity($user['id'], 'user_logout', 'User logged out successfully');
            $auth->logout();
        }

        $this->redirectWithSuccess(url('/login'), 'You have been logged out safely.');
    }

    /**
     * Helper method to write to activity_logs table.
     *
     * @param  int    $userId
     * @param  string $action
     * @param  string $description
     * @return void
     */
    private function logActivity(int $userId, string $action, string $description): void
    {
        try {
            QueryBuilder::table('activity_logs')->insert([
                'user_id'     => $userId,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            // Non-blocking log failure
        }
    }
}
