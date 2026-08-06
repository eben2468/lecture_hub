<?php
/**
 * ============================================================
 * Nadics LectureHub — Guest Middleware
 * ============================================================
 *
 * Redirects authenticated users away from guest-only pages
 * (login, register). Prevents logged-in users from accessing
 * authentication forms.
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

class GuestMiddleware extends Middleware
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
        if (Auth::getInstance()->check()) {
            redirect(url('/dashboard'));
            return;
        }

        $next();
    }
}
