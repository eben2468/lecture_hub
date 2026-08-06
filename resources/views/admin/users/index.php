<?php
/**
 * Nadics LectureHub — User Management Directory View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'User Directory',
    'page_description' => 'Manage system users, roles, and access status.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">User Directory & Security Governance</h1>
            <p class="page-subtitle">Manage accounts across Students, Lecturers, University Admins, and Super Administrators.</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="slms-card mb-4 p-3">
        <form method="GET" action="<?= url('/admin/users') ?>" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group-slms">
                    <i class="fas fa-search input-icon"></i>
                    <input type="text" name="search" class="form-control-slms" placeholder="Search by name, email, or matric ID..." value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-control-slms">
                    <option value="">All Roles</option>
                    <?php foreach ($roles ?? [] as $r): ?>
                        <option value="<?= e($r['slug']) ?>" <?= ($selectedRole ?? '') === $r['slug'] ? 'selected' : '' ?>>
                            <?= e($r['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn-slms btn-primary flex-fill"><i class="fas fa-search me-1"></i> Search Users</button>
                <a href="<?= url('/admin/users') ?>" class="btn-slms btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Matric / Staff ID</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-placeholder" style="width:32px;height:32px;font-size:12px;">
                                            <?= e(strtoupper(substr($u['first_name'], 0, 1))) ?>
                                        </div>
                                        <strong class="text-primary"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= e($u['email']) ?></td>
                                <td><code><?= e($u['matric_staff_id'] ?: 'N/A') ?></code></td>
                                <td><span class="badge-slms badge-info"><?= e($u['role_name']) ?></span></td>
                                <td>
                                    <?php if (($u['is_active'] ?? 0) == 1): ?>
                                        <span class="badge-slms badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge-slms badge-danger">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('/admin/users/' . $u['id'] . '/toggle') ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-slms btn-sm <?= ($u['is_active'] ?? 0) == 1 ? 'btn-danger-slms' : 'btn-success-slms' ?>">
                                            <?= ($u['is_active'] ?? 0) == 1 ? '<i class="fas fa-ban"></i> Disable' : '<i class="fas fa-check-circle"></i> Activate' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
