<?php
/**
 * ============================================================
 * Nadics LectureHub — Authentication Manager
 * ============================================================
 *
 * Manages user authentication state. Provides login, logout,
 * user retrieval, and session-based auth persistence.
 * Supports remember-me cookie tokens.
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Auth
{
    /**
     * Session key for storing authenticated user ID.
     *
     * @var string
     */
    private const SESSION_KEY = '_auth_user_id';

    /**
     * Session key for storing user data cache.
     *
     * @var string
     */
    private const USER_DATA_KEY = '_auth_user_data';

    /**
     * Cookie name for remember-me token.
     *
     * @var string
     */
    private const REMEMBER_COOKIE = 'slms_remember';

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Cached authenticated user data.
     *
     * @var array|null
     */
    private ?array $user = null;

    /**
     * Get the singleton Auth instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Attempt to authenticate a user with credentials.
     *
     * @param  string $email    User email
     * @param  string $password Plain-text password
     * @param  bool   $remember Whether to set remember-me cookie
     * @return bool             True if authentication succeeds
     */
    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        // Find user by email with role information
        $user = QueryBuilder::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.email', '=', $email)
            ->select([
                'users.*',
                'roles.slug as role_slug',
                'roles.name as role_name',
            ])
            ->first();

        if (!$user) {
            Logger::getInstance()->warning('Login attempt failed: user not found', [
                'email' => $email,
                'ip'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
            return false;
        }

        // Check if account is active
        if (isset($user['is_active']) && !$user['is_active']) {
            Logger::getInstance()->warning('Login attempt failed: account inactive', [
                'email' => $email,
            ]);
            return false;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            Logger::getInstance()->warning('Login attempt failed: invalid password', [
                'email' => $email,
                'ip'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
            return false;
        }

        // Authentication successful
        $this->login($user, $remember);

        return true;
    }

    /**
     * Log in a user (set session and optional remember cookie).
     *
     * @param  array $user     User data array
     * @param  bool  $remember Whether to set remember-me cookie
     * @return void
     */
    public function login(array $user, bool $remember = false): void
    {
        $session = Session::getInstance();

        // Regenerate session ID to prevent session fixation
        $session->regenerate();

        // Store user ID and data in session
        $session->set(self::SESSION_KEY, $user['id']);
        $session->set(self::USER_DATA_KEY, $user);

        // Cache the user
        $this->user = $user;

        // Handle remember-me
        if ($remember) {
            $this->setRememberToken($user['id']);
        }

        // Regenerate CSRF token
        CSRF::regenerate();

        // Update last login timestamp
        try {
            QueryBuilder::table('users')
                ->where('id', '=', $user['id'])
                ->update([
                    'last_login_at' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ]);
        } catch (\Exception $e) {
            // Non-critical: don't fail login if this update fails
            Logger::getInstance()->warning('Could not update last login', [
                'user_id' => $user['id'],
                'error'   => $e->getMessage(),
            ]);
        }

        Logger::getInstance()->info('User logged in', [
            'user_id' => $user['id'],
            'email'   => $user['email'] ?? '',
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);
    }

    /**
     * Log out the current user.
     *
     * @return void
     */
    public function logout(): void
    {
        $userId = $this->id();

        // Clear remember token from database
        if ($userId) {
            try {
                QueryBuilder::table('users')
                    ->where('id', '=', $userId)
                    ->update(['remember_token' => null]);
            } catch (\Exception $e) {
                // Non-critical
            }
        }

        // Clear remember cookie
        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            setcookie(self::REMEMBER_COOKIE, '', time() - 3600, '/', '', false, true);
        }

        // Destroy session
        $session = Session::getInstance();
        $session->destroy();

        // Clear cached user
        $this->user = null;

        Logger::getInstance()->info('User logged out', [
            'user_id' => $userId,
        ]);
    }

    /**
     * Check if a user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        // Check session first
        $session = Session::getInstance();
        if ($session->has(self::SESSION_KEY)) {
            return true;
        }

        // Check remember cookie
        return $this->loginFromRememberCookie();
    }

    /**
     * Check if the current user is a guest (not authenticated).
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the authenticated user's data.
     *
     * @return array|null User data or null if not authenticated
     */
    public function user(): ?array
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $session = Session::getInstance();
        $userData = $session->get(self::USER_DATA_KEY);

        if ($userData) {
            $this->user = $userData;
            return $this->user;
        }

        // Try to load from database with role information
        $userId = $session->get(self::SESSION_KEY);
        if ($userId) {
            $user = QueryBuilder::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->where('users.id', '=', $userId)
                ->select([
                    'users.*',
                    'roles.slug as role_slug',
                    'roles.name as role_name',
                ])
                ->first();

            if ($user) {
                $this->user = $user;
                $session->set(self::USER_DATA_KEY, $user);
                return $this->user;
            }
        }

        return null;
    }

    /**
     * Get the authenticated user's ID.
     *
     * @return int|null
     */
    public function id(): ?int
    {
        $session = Session::getInstance();
        $id = $session->get(self::SESSION_KEY);
        return $id ? (int) $id : null;
    }

    public function role(): ?string
    {
        $user = $this->user();
        if (!$user) {
            return null;
        }

        if (isset($user['role_slug'])) {
            return $user['role_slug'];
        }

        if (isset($user['role_id'])) {
            try {
                $role = QueryBuilder::table('roles')->where('id', '=', $user['role_id'])->first();
                return $role['slug'] ?? null;
            } catch (\Exception $e) {
                return null;
            }
        }

        return $user['role'] ?? null;
    }

    /**
     * Check if the user has a specific role.
     *
     * @param  string|array $roles Role name(s) to check
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        $userRole = $this->role();

        if ($userRole === null) {
            return false;
        }

        if (is_string($roles)) {
            return $userRole === $roles;
        }

        return in_array($userRole, $roles);
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param  string $permission Permission name
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        // Super admin has all permissions
        if (($user['role'] ?? '') === 'super_admin') {
            return true;
        }

        // Check role_permissions table
        try {
            $roleId = $user['role_id'] ?? null;
            if (!$roleId) {
                return false;
            }

            return QueryBuilder::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role_id', '=', $roleId)
                ->where('permissions.slug', '=', $permission)
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Refresh the cached user data from the database.
     *
     * @return void
     */
    public function refresh(): void
    {
        $userId = $this->id();

        if ($userId) {
            $user = QueryBuilder::table('users')
                ->where('id', '=', $userId)
                ->first();

            if ($user) {
                $this->user = $user;
                Session::getInstance()->set(self::USER_DATA_KEY, $user);
            }
        }
    }

    // ========================================================
    // REMEMBER ME
    // ========================================================

    /**
     * Set a remember-me cookie and token.
     *
     * @param  int $userId User ID
     * @return void
     */
    private function setRememberToken(int $userId): void
    {
        try {
            $token = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);

            // Store hashed token in database
            QueryBuilder::table('users')
                ->where('id', '=', $userId)
                ->update(['remember_token' => $hashedToken]);

            // Set cookie (30 days) if headers not sent
            if (!headers_sent()) {
                $cookie = $userId . '|' . $token;
                setcookie(
                    self::REMEMBER_COOKIE,
                    $cookie,
                    time() + (30 * 24 * 60 * 60),
                    '/',
                    '',
                    false,
                    true // HttpOnly
                );
            }
        } catch (\Exception $e) {
            Logger::getInstance()->error('Failed to set remember token', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Attempt to log in from a remember-me cookie.
     *
     * @return bool True if logged in from cookie
     */
    private function loginFromRememberCookie(): bool
    {
        if (!isset($_COOKIE[self::REMEMBER_COOKIE])) {
            return false;
        }

        $cookie = $_COOKIE[self::REMEMBER_COOKIE];

        if (!str_contains($cookie, '|')) {
            return false;
        }

        [$userId, $token] = explode('|', $cookie, 2);
        $hashedToken = hash('sha256', $token);

        try {
            $user = QueryBuilder::table('users')
                ->where('id', '=', $userId)
                ->where('remember_token', '=', $hashedToken)
                ->first();

            if ($user) {
                $this->login($user, true);
                return true;
            }
        } catch (\Exception $e) {
            Logger::getInstance()->error('Remember cookie validation failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // Invalid cookie — clear it
        setcookie(self::REMEMBER_COOKIE, '', time() - 3600, '/', '', false, true);
        return false;
    }

    /**
     * Prevent cloning of singleton.
     */
    private function __clone() {}
}
