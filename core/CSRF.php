<?php
/**
 * ============================================================
 * Nadics LectureHub — CSRF Protection
 * ============================================================
 *
 * Provides Cross-Site Request Forgery protection through
 * token generation, validation, and form helpers.
 *
 * Tokens are stored in the session and validated on every
 * state-changing request (POST, PUT, PATCH, DELETE).
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class CSRF
{
    /**
     * Session key for storing the CSRF token.
     *
     * @var string
     */
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Session key for storing the token timestamp.
     *
     * @var string
     */
    private const TOKEN_TIME_KEY = '_csrf_token_time';

    /**
     * Generate or retrieve the current CSRF token.
     *
     * If a valid token exists in the session and hasn't expired,
     * it will be reused. Otherwise, a new token is generated.
     *
     * @return string The CSRF token
     */
    public static function getToken(): string
    {
        $session  = Session::getInstance();
        $token    = $session->get(self::TOKEN_KEY);
        $tokenTime = $session->get(self::TOKEN_TIME_KEY, 0);
        $lifetime = (int) env('CSRF_TOKEN_LIFETIME', 3600);

        // Generate new token if none exists or has expired
        if (empty($token) || (time() - $tokenTime) > $lifetime) {
            $token = self::generateToken();
            $session->set(self::TOKEN_KEY, $token);
            $session->set(self::TOKEN_TIME_KEY, time());
        }

        return $token;
    }

    /**
     * Validate a submitted CSRF token.
     *
     * Compares the submitted token against the session token
     * using a timing-safe comparison to prevent timing attacks.
     *
     * @param  string $submittedToken The token from the form/header
     * @return bool                   True if valid, false otherwise
     */
    public static function validate(string $submittedToken): bool
    {
        $session    = Session::getInstance();
        $storedToken = $session->get(self::TOKEN_KEY, '');

        if (empty($storedToken) || empty($submittedToken)) {
            return false;
        }

        return hash_equals($storedToken, $submittedToken);
    }

    /**
     * Regenerate the CSRF token.
     *
     * Should be called after critical actions (login, password change)
     * to invalidate any stolen tokens.
     *
     * @return string The new CSRF token
     */
    public static function regenerate(): string
    {
        $session = Session::getInstance();
        $token   = self::generateToken();

        $session->set(self::TOKEN_KEY, $token);
        $session->set(self::TOKEN_TIME_KEY, time());

        return $token;
    }

    /**
     * Generate a cryptographically secure random token.
     *
     * @return string 64-character hexadecimal token
     */
    private static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get the token from the current request.
     *
     * Checks POST body first, then the X-CSRF-TOKEN header
     * (for AJAX requests).
     *
     * @return string|null The submitted token or null
     */
    public static function getSubmittedToken(): ?string
    {
        // Check POST body
        if (isset($_POST['_csrf_token'])) {
            return $_POST['_csrf_token'];
        }

        // Check request header (for AJAX)
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'x-csrf-token') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Generate a hidden form input field with the CSRF token.
     *
     * @return string HTML hidden input element
     */
    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Generate a meta tag for AJAX CSRF token injection.
     *
     * Place this in the <head> of your layout, then read it
     * from JavaScript to attach to AJAX request headers.
     *
     * @return string HTML meta tag
     */
    public static function metaTag(): string
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
