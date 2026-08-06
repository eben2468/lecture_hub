<?php
/**
 * Nadics LectureHub — Profile & Account Settings View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'My Profile',
    'page_description' => 'Manage your profile and security settings.',
]);

$userRole = ucfirst(str_replace('_', ' ', $user['role'] ?? 'User'));
$initials = strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1));
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">User Profile</h1>
            <p class="page-subtitle">Manage your personal account details, photo, and security preferences.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Card Left Column -->
        <div class="col-lg-4">
            <div class="slms-card text-center p-4">
                <div class="position-relative d-inline-block mb-3">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?= url($user['profile_photo']) ?>" alt="Profile Photo" class="avatar avatar-xl" style="width:110px;height:110px;">
                    <?php else: ?>
                        <div class="avatar avatar-placeholder mx-auto" style="width:110px;height:110px;font-size:2.2rem;border-radius:50%;">
                            <?= e($initials) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h3 class="mb-1"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                <div class="badge-slms badge-primary mb-3"><?= e($userRole) ?></div>

                <p class="text-muted small mb-4">
                    <i class="fas fa-envelope me-1"></i> <?= e($user['email']) ?><br>
                    <?php if (!empty($user['matric_staff_id'])): ?>
                        <i class="fas fa-id-card me-1 mt-2"></i> ID: <?= e($user['matric_staff_id']) ?>
                    <?php endif; ?>
                </p>

                <!-- Change Photo Form -->
                <form method="POST" action="<?= url('/profile/photo') ?>" enctype="multipart/form-data" class="border-top pt-3">
                    <?= csrf_field() ?>
                    <label class="form-label text-start w-100 small fw-600">Update Profile Photo (Max 5MB)</label>
                    <div class="input-group mb-2">
                        <input type="file" name="profile_photo" class="form-control form-control-sm" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn-slms btn-accent btn-sm w-100">
                        <i class="fas fa-upload me-1"></i> Upload New Photo
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Tabs (Edit Details, Security, Activity Log) -->
        <div class="col-lg-8">
            <div class="slms-card">
                <div class="card-header p-0 border-bottom">
                    <style>
                        #profileTabs::-webkit-scrollbar {
                            display: none;
                        }
                        #profileTabs {
                            -ms-overflow-style: none;
                            scrollbar-width: none;
                        }
                        #profileTabs .nav-link {
                            white-space: nowrap;
                        }
                    </style>
                    <ul class="nav nav-tabs border-0 px-3 pt-2 flex-nowrap overflow-x-auto" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-600" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                                <i class="fas fa-user-edit me-1"></i> Edit Profile
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-600" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-lock me-1"></i> Security & Password
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-600" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                                <i class="fas fa-history me-1"></i> Recent Activity
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">

                        <!-- TAB 1: EDIT DETAILS -->
                        <div class="tab-pane fade show active" id="details" role="tabpanel">
                            <form method="POST" action="<?= url('/profile') ?>">
                                <?= csrf_field() ?>

                                <div class="row g-3">
                                    <div class="col-md-6 form-group">
                                        <label class="form-label" for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control-slms" value="<?= e($user['first_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control-slms" value="<?= e($user['last_name']) ?>" required>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6 form-group">
                                        <label class="form-label">Email Address (Read Only)</label>
                                        <input type="email" class="form-control-slms" value="<?= e($user['email']) ?>" readonly style="background:var(--bg-surface-alt);">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label" for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" class="form-control-slms" value="<?= e($user['phone'] ?? '') ?>" placeholder="08012345678">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6 form-group">
                                        <label class="form-label" for="gender">Gender</label>
                                        <select id="gender" name="gender" class="form-control-slms">
                                            <option value="">— Select Gender —</option>
                                            <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                            <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Prefer not to say</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label">Institution</label>
                                        <input type="text" class="form-control-slms" value="<?= e($university['name'] ?? 'Nadics University Platform') ?>" readonly style="background:var(--bg-surface-alt);">
                                    </div>
                                </div>

                                <button type="submit" class="btn-slms btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </form>
                        </div>

                        <!-- TAB 2: SECURITY -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <form method="POST" action="<?= url('/profile/password') ?>">
                                <?= csrf_field() ?>

                                <div class="form-group mb-3">
                                    <label class="form-label" for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control-slms" required>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6 form-group">
                                        <label class="form-label" for="password">New Password</label>
                                        <input type="password" id="password" name="password" class="form-control-slms" placeholder="Min. 8 characters" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-slms" placeholder="Re-enter new password" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn-slms btn-danger-slms">
                                    <i class="fas fa-key me-1"></i> Change Password
                                </button>
                            </form>
                        </div>

                        <!-- TAB 3: RECENT ACTIVITY -->
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table-slms">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($activities)): ?>
                                            <?php foreach ($activities as $act): ?>
                                                <tr>
                                                    <td><span class="badge-slms badge-info"><?= e($act['action']) ?></span></td>
                                                    <td><?= e($act['description']) ?></td>
                                                    <td><?= e($act['created_at']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No activity records found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
