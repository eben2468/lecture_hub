<?php
/**
 * Nadics LectureHub — Forgot Password View
 */
$__view->layout('layouts.auth', [
    'page_title'       => 'Reset Password',
    'page_description' => 'Recover your Nadics LectureHub account password.',
]);
?>

<?php $__view->section('content'); ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon" style="background:transparent;"><img src="<?= asset('img/logo.png') ?>" alt="Logo" style="height:100%; width:100%; object-fit:contain; border-radius:inherit;"></div>
            <h2>Forgot Password?</h2>
            <p>Enter your institutional email address to receive a password reset link.</p>
        </div>

        <?php $__view->component('components.alerts', ['errors' => $errors ?? []]); ?>

        <form method="POST" action="<?= url('/forgot-password') ?>" data-validate>
            <?= csrf_field() ?>

            <div class="form-group mb-4">
                <label class="form-label" for="email">Email Address</label>
                <div class="input-group-slms">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control-slms"
                           placeholder="name@university.edu.ng" value="<?= e(old('email')) ?>" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn-slms btn-primary w-100 py-3" style="font-size: var(--text-base);">
                <i class="fas fa-paper-plane me-2"></i> Send Reset Link
            </button>
        </form>

        <div class="auth-footer">
            Remember your password? <a href="<?= url('/login') ?>">Back to Login</a>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
