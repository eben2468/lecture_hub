<?php
/**
 * ============================================================
 * Nadics LectureHub — CSRF Middleware
 * ============================================================
 *
 * Validates CSRF tokens on all state-changing requests
 * (POST, PUT, PATCH, DELETE). Protects against cross-site
 * request forgery attacks.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Middleware
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace App\Middleware;

use Core\Middleware;
use Core\Request;
use Core\CSRF;
use Core\Response;

class CsrfMiddleware extends Middleware
{
    /**
     * URIs excluded from CSRF verification.
     *
     * @var array
     */
    private array $except = [
        '/api/*',
        '/webhooks/*',
    ];

    /**
     * Handle the incoming request.
     *
     * Validates the CSRF token for POST, PUT, PATCH, DELETE requests.
     * Skips validation for excluded URIs and GET/HEAD/OPTIONS requests.
     *
     * @param  Request  $request The HTTP request
     * @param  callable $next    Next middleware
     * @return void
     */
    public function handle(Request $request, callable $next): void
    {
        // Only verify on state-changing methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            $next();
            return;
        }

        // Check excluded URIs
        if ($this->isExcluded($request->uri())) {
            $next();
            return;
        }

        // Validate CSRF token
        $token = CSRF::getSubmittedToken();

        if (!$token || !CSRF::validate($token)) {
            if ($request->expectsJson()) {
                Response::error('CSRF token mismatch', 419);
            }

            // For web requests, redirect back with error
            flash('error', 'Your session has expired. Please try again.');
            back();
            return;
        }

        $next();
    }

    /**
     * Check if the URI is excluded from CSRF verification.
     *
     * @param  string $uri Request URI
     * @return bool
     */
    private function isExcluded(string $uri): bool
    {
        foreach ($this->except as $pattern) {
            $pattern = str_replace('*', '.*', $pattern);
            if (preg_match('#^' . $pattern . '$#', $uri)) {
                return true;
            }
        }
        return false;
    }
}
