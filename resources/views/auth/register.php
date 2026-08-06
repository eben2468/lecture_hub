<?php
/**
 * Nadics LectureHub — Registration View
 */
$__view->layout('layouts.auth', [
    'page_title'       => 'Create Account',
    'page_description' => 'Register for Nadics LectureHub.',
]);
?>

<?php $__view->section('content'); ?>
<div class="auth-wrapper py-5">
    <div class="auth-card" style="max-width: 540px;">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="logo-icon" style="background:transparent;"><img src="<?= asset('img/logo.png') ?>" alt="Logo" style="height:100%; width:100%; object-fit:contain; border-radius:inherit;"></div>
            <h2>Create Account</h2>
            <p>Join Nadics LectureHub Smart Academic Platform</p>
        </div>

        <!-- Validation Errors -->
        <?php $__view->component('components.alerts', ['errors' => $errors ?? []]); ?>

        <!-- Registration Form -->
        <form method="POST" action="<?= url('/register') ?>" data-validate>
            <?= csrf_field() ?>

            <!-- Role Selector -->
            <div class="form-group mb-4">
                <label class="form-label">I am registering as a:</label>
                <div class="d-flex gap-3">
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="role_type" id="role_student" value="student" checked onchange="toggleMatricLabel('student')">
                        <label class="btn btn-outline-primary w-100 py-2 fw-600" for="role_student">
                            <i class="fas fa-user-graduate me-2"></i> Student
                        </label>
                    </div>
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="role_type" id="role_lecturer" value="lecturer" onchange="toggleMatricLabel('lecturer')">
                        <label class="btn btn-outline-primary w-100 py-2 fw-600" for="role_lecturer">
                            <i class="fas fa-chalkboard-teacher me-2"></i> Lecturer
                        </label>
                    </div>
                </div>
            </div>

            <!-- First & Last Name -->
            <div class="row g-3">
                <div class="col-md-6 form-group">
                    <label class="form-label" for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control-slms" placeholder="e.g. Oluwaseun" value="<?= e(old('first_name')) ?>" required>
                </div>
                <div class="col-md-6 form-group">
                    <label class="form-label" for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control-slms" placeholder="e.g. Adebayo" value="<?= e(old('last_name')) ?>" required>
                </div>
            </div>

            <!-- Email & Phone with Country Code Selector -->
            <div class="row g-3">
                <div class="col-md-6 form-group">
                    <label class="form-label" for="email">Institutional Email</label>
                    <input type="email" id="email" name="email" class="form-control-slms" placeholder="user@university.edu.ng" value="<?= e(old('email')) ?>" required>
                </div>
                <div class="col-md-6 form-group">
                    <label class="form-label" for="phone_number">Phone Number</label>
                    <div class="input-group">
                        <select name="country_code" id="country_code" class="form-control-slms" style="max-width: 100px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="+234" <?= old('country_code') === '+234' ? 'selected' : '' ?>>+234 (NG)</option>
                            <option value="+233" <?= old('country_code', '+233') === '+233' ? 'selected' : '' ?>>+233 (GH)</option>
                            <option value="+254" <?= old('country_code') === '+254' ? 'selected' : '' ?>>+254 (KE)</option>
                            <option value="+27" <?= old('country_code') === '+27' ? 'selected' : '' ?>>+27 (ZA)</option>
                            <option value="+1" <?= old('country_code') === '+1' ? 'selected' : '' ?>>+1 (US)</option>
                            <option value="+44" <?= old('country_code') === '+44' ? 'selected' : '' ?>>+44 (UK)</option>
                        </select>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control-slms" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" placeholder="0241234567" value="<?= e(old('phone_number')) ?>" required>
                    </div>
                </div>
            </div>

            <!-- University & Department -->
            <div class="row g-3">
                <div class="col-md-6 form-group">
                    <label class="form-label" for="university_id">University</label>
                    <select id="university_id" name="university_id" class="form-control-slms" required onchange="filterDepartments()">
                        <option value="">Select University</option>
                        <?php foreach ($universities ?? [] as $univ): ?>
                            <option value="<?= $univ['id'] ?>" data-country="<?= e($univ['country'] ?? 'Nigeria') ?>" <?= old('university_id') == $univ['id'] ? 'selected' : '' ?>>
                                <?= e($univ['name']) ?> (<?= e($univ['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="form-label" for="department_id">Department</label>
                    <select id="department_id" name="department_id" class="form-control-slms" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments ?? [] as $dept): ?>
                            <option value="<?= $dept['id'] ?>" data-university-id="<?= $dept['university_id'] ?? '' ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                <?= e($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Matric / Staff ID -->
            <div class="form-group">
                <label class="form-label" id="id_label" for="matric_staff_id">Matriculation Number</label>
                <div class="input-group-slms">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="matric_staff_id" name="matric_staff_id" class="form-control-slms"
                           placeholder="e.g. 210407001" value="<?= e(old('matric_staff_id')) ?>" required>
                </div>
            </div>

            <!-- Password & Confirmation -->
            <div class="row g-3">
                <div class="col-md-6 form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control-slms" placeholder="Min. 8 characters" required>
                </div>
                <div class="col-md-6 form-group">
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-slms" placeholder="Re-enter password" required>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-slms btn-primary w-100 py-3 mt-3" style="font-size: var(--text-base);">
                <i class="fas fa-user-plus me-2"></i> Register Account
            </button>
        </form>

        <!-- Auth Footer -->
        <div class="auth-footer">
            Already have an account? <a href="<?= url('/login') ?>">Log In Here</a>
        </div>
    </div>
</div>

<script>
function toggleMatricLabel(role) {
    const label = document.getElementById('id_label');
    const input = document.getElementById('matric_staff_id');
    if (role === 'lecturer') {
        label.textContent = 'Staff / Employee ID';
        input.placeholder = 'e.g. STF/CSC/001';
    } else {
        label.textContent = 'Matriculation Number';
        input.placeholder = 'e.g. 210407001';
    }
}

function filterDepartments() {
    const univSelect = document.getElementById('university_id');
    const deptSelect = document.getElementById('department_id');
    const countrySelect = document.getElementById('country_code');
    const selectedUnivOption = univSelect.options[univSelect.selectedIndex];
    
    const univId = univSelect.value;
    const country = selectedUnivOption ? selectedUnivOption.getAttribute('data-country') : '';

    // Auto-select Country Code based on university's country
    if (country === 'Ghana') {
        countrySelect.value = '+233';
    } else if (country === 'Nigeria') {
        countrySelect.value = '+234';
    }

    // Filter departments based on university_id
    const options = deptSelect.querySelectorAll('option');
    options.forEach(opt => {
        if (!opt.value) return; // Skip placeholder option
        const deptUnivId = opt.getAttribute('data-university-id');
        if (univId && deptUnivId !== univId) {
            opt.style.display = 'none';
            opt.disabled = true;
        } else {
            opt.style.display = '';
            opt.disabled = false;
        }
    });

    // Reset department select value if currently selected is filtered out
    const activeOption = deptSelect.options[deptSelect.selectedIndex];
    if (activeOption && activeOption.style.display === 'none') {
        deptSelect.value = '';
    }
}

// Initial filter execution
document.addEventListener('DOMContentLoaded', () => {
    filterDepartments();
});
</script>
<?php $__view->endSection(); ?>
