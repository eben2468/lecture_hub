<?php
/**
 * ============================================================
 * Nadics LectureHub — Auth Middleware
 * ============================================================
 *
 * Protects routes by ensuring the user is authenticated.
 * Redirects unauthenticated users to the login page.
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
use Core\Auth;
use Core\Response;

class AuthMiddleware extends Middleware
{
    /**
     * Handle the incoming request.
     *
     * @param  Request  $request The HTTP request
     * @param  callable $next    Next middleware
     * @return void
     */
    public function handle(Request $request, callable $next): void
    {
        $auth = Auth::getInstance();

        if (!$auth->check()) {
            if ($request->expectsJson() || $request->isApi()) {
                Response::error('Unauthenticated', 401);
                return;
            }

            // Store intended URL for redirect after login
            session('_intended_url', $request->fullUrl());

            flash('warning', 'Please log in to access this page.');
            redirect(url('/login'));
            return;
        }

        $next();
    }
}
