<?php
/**
 * ============================================================
 * Nadics LectureHub — Session Management
 * ============================================================
 *
 * Provides a secure, centralized session management layer.
 * Handles session start, regeneration, flash messages,
 * old input preservation, and CSRF token storage.
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Session
{
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Flash message key prefix in $_SESSION.
     *
     * @var string
     */
    private const FLASH_KEY = '_flash_messages';

    /**
     * Old input key in $_SESSION.
     *
     * @var string
     */
    private const OLD_INPUT_KEY = '_old_input';

    /**
     * Private constructor — use getInstance().
     */
    private function __construct()
    {
        // Session is started by Application::boot()
    }

    /**
     * Get the singleton Session instance.
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
     * Start the session with secure configuration.
     *
     * Configures cookie parameters for security:
     * - HttpOnly flag prevents JavaScript access
     * - SameSite attribute prevents CSRF via cross-origin requests
     * - Secure flag ensures cookies only sent over HTTPS
     *
     * @return void
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configure session cookie parameters if headers not sent
        if (!headers_sent()) {
            session_set_cookie_params([
                'lifetime' => (int) env('SESSION_LIFETIME', 120) * 60,
                'path'     => '/',
                'domain'   => '',
                'secure'   => (bool) env('SESSION_SECURE', false),
                'httponly'  => (bool) env('SESSION_HTTPONLY', true),
                'samesite' => env('SESSION_SAME_SITE', 'Lax'),
            ]);

            session_name('slms_session');
        }

        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        // Age flash messages from previous request
        self::ageFlashMessages();
    }

    /**
     * Get a session value.
     *
     * @param  string $key     The session key
     * @param  mixed  $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value.
     *
     * @param  string $key   The session key
     * @param  mixed  $value The value to store
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session key exists.
     *
     * @param  string $key The session key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key.
     *
     * @param  string $key The session key to remove
     * @return void
     */
    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data.
     *
     * @return array
     */
    public function all(): array
    {
        return $_SESSION ?? [];
    }

    /**
     * Clear all session data.
     *
     * @return void
     */
    public function flush(): void
    {
        session_unset();
    }

    /**
     * Regenerate the session ID for security.
     *
     * Should be called after authentication events (login, logout,
     * privilege escalation) to prevent session fixation attacks.
     *
     * @param  bool $deleteOldSession Whether to delete the old session data
     * @return bool
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            return session_regenerate_id($deleteOldSession);
        }
        return false;
    }

    /**
     * Destroy the session completely.
     *
     * @return void
     */
    public function destroy(): void
    {
        $_SESSION = [];

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    // ========================================================
    // FLASH MESSAGES
    // ========================================================

    /**
     * Set a flash message for the next request.
     *
     * Flash messages persist for exactly one subsequent request,
     * then are automatically removed.
     *
     * @param  string $key     Message type (success, error, warning, info)
     * @param  string $message The message content
     * @return void
     */
    public function flash(string $key, string $message): void
    {
        $_SESSION[self::FLASH_KEY]['new'][$key] = $message;
    }

    /**
     * Get a flash message.
     *
     * @param  string      $key     Message type
     * @param  string|null $default Default message
     * @return string|null
     */
    public function getFlash(string $key, ?string $default = null): ?string
    {
        return $_SESSION[self::FLASH_KEY]['display'][$key] ?? $default;
    }

    /**
     * Check if a flash message exists.
     *
     * @param  string $key Message type
     * @return bool
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION[self::FLASH_KEY]['display'][$key]);
    }

    /**
     * Get all flash messages for display.
     *
     * @return array
     */
    public function getAllFlash(): array
    {
        return $_SESSION[self::FLASH_KEY]['display'] ?? [];
    }

    /**
     * Age flash messages — move 'new' to 'display', clear old 'display'.
     *
     * Called automatically at session start.
     *
     * @return void
     */
    private static function ageFlashMessages(): void
    {
        // Move new messages to display (they become visible this request)
        $_SESSION[self::FLASH_KEY]['display'] = $_SESSION[self::FLASH_KEY]['new'] ?? [];
        // Clear new messages bucket
        $_SESSION[self::FLASH_KEY]['new'] = [];
    }

    // ========================================================
    // OLD INPUT
    // ========================================================

    /**
     * Preserve current request input for the next request.
     *
     * Used to repopulate forms after validation failure.
     *
     * @param  array $input The form input data to preserve
     * @return void
     */
    public function flashInput(array $input): void
    {
        // Never preserve passwords
        unset($input['password'], $input['password_confirmation'], $input['_csrf_token']);
        $_SESSION[self::OLD_INPUT_KEY] = $input;
    }

    /**
     * Get an old input value.
     *
     * @param  string $key     The input field name
     * @param  mixed  $default Default value
     * @return mixed
     */
    public function getOld(string $key, mixed $default = ''): mixed
    {
        $old = $_SESSION[self::OLD_INPUT_KEY] ?? [];
        $value = $old[$key] ?? $default;

        return $value;
    }

    /**
     * Clear old input data.
     *
     * @return void
     */
    public function clearOldInput(): void
    {
        unset($_SESSION[self::OLD_INPUT_KEY]);
    }

    /**
     * Prevent cloning of singleton.
     */
    private function __clone() {}
}
