<?php
/**
 * ============================================================
 * Nadics LectureHub — Middleware Pipeline
 * ============================================================
 *
 * Base middleware interface and pipeline executor.
 * All middleware classes must implement the handle() method.
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

abstract class Middleware
{
    /**
     * Handle the incoming request.
     *
     * Each middleware receives the request and a $next closure.
     * Call $next() to pass the request to the next middleware
     * or the final controller action. Do NOT call $next() to
     * short-circuit (e.g., redirect unauthenticated users).
     *
     * @param  Request  $request The HTTP request
     * @param  callable $next    The next middleware or action
     * @return void
     */
    abstract public function handle(Request $request, callable $next): void;
}
