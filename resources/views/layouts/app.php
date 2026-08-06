<?php
/**
 * Nadics LectureHub — Main Authenticated Layout
 * 
 * Used by all dashboard and authenticated pages.
 * Includes sidebar, navbar, notification dropdown, and footer.
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Nadics LectureHub — Smart Lecture Management System">
    <meta name="author" content="Nadics Solutions">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="app-version" content="<?= e(env('APP_VERSION', '1.0.0')) ?>">

    <title><?= e($page_title ?? 'Dashboard') ?> — <?= e(env('APP_NAME', 'Nadics LectureHub')) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Application CSS -->
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dashboard.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dark-mode.css') ?>" rel="stylesheet">

    <!-- Page-specific CSS -->
    <?php if (isset($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>

    <script>
        window.SLMS_APP_URL = '<?= url('') ?>';
    </script>
</head>
<body>

    <!-- ============================================================
         SIDEBAR
         ============================================================ -->
    <?php $__view->component('components.sidebar', [
        'auth_user' => $auth_user ?? null,
    ]); ?>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- ============================================================
         NAVBAR
         ============================================================ -->
    <?php $__view->component('components.navbar', [
        'auth_user' => $auth_user ?? null,
    ]); ?>

    <!-- ============================================================
         FLASH MESSAGES (Hidden data for JS)
         ============================================================ -->
    <div id="flash-data" class="d-none"
        data-success="<?= e($flash_success ?? '') ?>"
        data-error="<?= e($flash_error ?? '') ?>"
        data-warning="<?= e($flash_warning ?? '') ?>"
        data-info="<?= e($flash_info ?? '') ?>">
    </div>

    <!-- ============================================================
         MAIN CONTENT
         ============================================================ -->
    <main class="slms-main">
        <!-- Alerts Component -->
        <?php $__view->component('components.alerts', [
            'errors' => $errors ?? [],
        ]); ?>

        <!-- Page Content -->
        <?= $__view->yield('content') ?>
    </main>

    <!-- ============================================================
         FOOTER
         ============================================================ -->
    <?php $__view->component('components.footer'); ?>

    <!-- ============================================================
         SCRIPTS
         ============================================================ -->
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>

    <!-- Application JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/ajax.js') ?>"></script>
    <script src="<?= asset('js/sidebar.js') ?>"></script>
    <script src="<?= asset('js/dark-mode.js') ?>"></script>
    <script src="<?= asset('js/live-stream-poller.js') ?>"></script>

    <!-- Page-specific JS -->
    <?php if (isset($extra_js)): ?>
        <?= $extra_js ?>
    <?php endif; ?>
</body>
</html>
