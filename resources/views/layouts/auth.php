<?php
/**
 * Nadics LectureHub — Auth Pages Layout
 * Used by login, register, forgot password, and reset password pages.
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="<?= e($page_description ?? 'Nadics LectureHub — Smart Lecture Management System') ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">

    <title><?= e($page_title ?? 'Login') ?> — <?= e(env('APP_NAME', 'Nadics LectureHub')) ?></title>

    <link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/auth.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dark-mode.css') ?>" rel="stylesheet">
</head>
<body>

    <div id="flash-data" class="d-none"
        data-success="<?= e($flash_success ?? '') ?>"
        data-error="<?= e($flash_error ?? '') ?>"
        data-warning="<?= e($flash_warning ?? '') ?>"
        data-info="<?= e($flash_info ?? '') ?>">
    </div>

    <?= $__view->yield('content') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/dark-mode.js') ?>"></script>
</body>
</html>
