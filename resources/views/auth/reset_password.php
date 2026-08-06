<?php
/**
 * Nadics LectureHub — Reset Password View
 */
$__view->layout('layouts.auth', [
    'page_title' => 'Set New Password',
]);
?>

<?php $__view->section('content'); ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon" style="background:transparent;"><img src="<?= asset('img/logo.png') ?>" alt="Logo" style="height:100%; width:100%; object-fit:contain; border-radius:inherit;"></div>
            <h2>Set New Password</h2>
            <p>Create a strong password for <?= e($email ?? '') ?></p>
        </div>

        <?php $__view->component('components.alerts', ['errors' => $errors ?? []]); ?>

        <form method="POST" action="<?= url('/reset-password') ?>" data-validate>
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
            <input type="hidden" name="email" value="<?= e($email ?? '') ?>">

            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-control-slms" placeholder="Min. 8 characters" required autofocus>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-slms" placeholder="Re-enter password" required>
            </div>

            <button type="submit" class="btn-slms btn-primary w-100 py-3" style="font-size: var(--text-base);">
                <i class="fas fa-save me-2"></i> Save New Password
            </button>
        </form>

        <div class="auth-footer">
            <a href="<?= url('/login') ?>">Back to Login</a>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
