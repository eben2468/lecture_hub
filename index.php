<?php
/**
 * ============================================================
 * Nadics LectureHub — Front Controller (Single Entry Point)
 * ============================================================
 *
 * All HTTP requests are routed through this file by Apache's
 * mod_rewrite. This file bootstraps the application framework,
 * loads the autoloader and helpers, and dispatches the request.
 *
 * @package    NadicsLectureHub
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

// ============================================================
// 1. DEFINE THE BASE PATH
// ============================================================
define('SLMS_START', microtime(true));
define('BASE_PATH', __DIR__);

// ============================================================
// 2. LOAD THE HELPERS & APPLICATION CLASS
// ============================================================
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

// ============================================================
// 3. BOOTSTRAP THE APPLICATION
// ============================================================
$app = new Core\Application(BASE_PATH);
$app->boot();

// ============================================================
// 4. DISPATCH THE REQUEST
// ============================================================
$app->run();
