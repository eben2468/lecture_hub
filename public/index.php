<?php
/**
 * ============================================================
 * Nadics LectureHub — Public Front Controller
 * ============================================================
 *
 * This entry point is used when the web host document root
 * is pointed at the /public/ folder (standard on cPanel,
 * Plesk, and most shared hosting providers).
 *
 * The real application root is one level above /public/,
 * so we define BASE_PATH accordingly before bootstrapping.
 *
 * @package    NadicsLectureHub
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-08-06
 * ============================================================
 */

// ============================================================
// 1. DEFINE THE BASE PATH (one level up from /public/)
// ============================================================
define('SLMS_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

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
