<?php
/**
 * Nadics LectureHub — Login View
 */
$__view->layout('layouts.auth', [
    'page_title'       => 'Portal Login',
    'page_description' => 'Access your Nadics LectureHub portal.',
]);
?>

<?php $__view->section('content'); ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="logo-icon" style="background:transparent;"><img src="<?= asset('img/logo.png') ?>" alt="Logo" style="height:100%; width:100%; object-fit:contain; border-radius:inherit;"></div>
            <h2>Nadics LectureHub</h2>
            <p>Smart Lecture Management System</p>
        </div>

        <!-- Validation Errors -->
        <?php $__view->component('components.alerts', ['errors' => $errors ?? []]); ?>

        <!-- Login Form -->
        <form method="POST" action="<?= url('/login') ?>" data-validate>
            <?= csrf_field() ?>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <div class="input-group-slms">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control-slms <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                           placeholder="name@university.edu.ng" value="<?= e(old('email')) ?>" required autofocus>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0" for="password">Password</label>
                    <a href="<?= url('/forgot-password') ?>" class="forgot-link">Forgot password?</a>
                </div>
                <div class="input-group-slms password-toggle">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control-slms <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           placeholder="••••••••" required>
                    <button type="button" class="toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle Password Visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check d-flex align-items-center gap-2 m-0">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                    <label class="form-check-label text-secondary" for="remember" style="font-size: var(--text-sm);">
                        Keep me logged in for 30 days
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-slms btn-primary w-100 py-3" style="font-size: var(--text-base);">
                <i class="fas fa-sign-in-alt me-2"></i> Log In to Portal
            </button>
        </form>

        <!-- Auth Footer -->
        <div class="auth-footer">
            Don't have an account yet? <a href="<?= url('/register') ?>">Register Here</a>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
<?php $__view->endSection(); ?>
