<?php
/**
 * Nadics LectureHub — Student Management View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Student Management',
    'page_description' => 'Manage student matriculation, degree programmes, and active status.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">Student Management</h1>
            <p class="page-subtitle">Register and manage student matriculation records, active status, and course enrollments.</p>
        </div>
        <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#registerStudentModal">
            <i class="fas fa-user-plus me-1"></i> Register Student
        </button>
    </div>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Matriculation No.</th>
                        <th>Student Name</th>
                        <th>Email Address</th>
                        <th>Department</th>
                        <th>Enrolled Courses</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $st): ?>
                            <tr>
                                <td><code><?= e($st['matric_staff_id'] ?: 'N/A') ?></code></td>
                                <td><strong><?= e($st['first_name'] . ' ' . $st['last_name']) ?></strong></td>
                                <td><?= e($st['email']) ?></td>
                                <td><?= e($st['department_name'] ?? 'Computer Science') ?></td>
                                <td><span class="badge bg-info-subtle text-info"><?= (int)$st['enrollment_count'] ?> Enrolled</span></td>
                                <td>
                                    <?php if (!empty($st['is_active'])): ?>
                                        <span class="badge bg-success-subtle text-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger"><i class="fas fa-ban me-1"></i> Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('/admin/students/' . $st['id'] . '/toggle') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <?php if (!empty($st['is_active'])): ?>
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Suspend Student">
                                                <i class="fas fa-user-slash me-1"></i> Suspend
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-sm btn-success" title="Activate Student">
                                                <i class="fas fa-user-check me-1"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-user-graduate mb-2" style="font-size:2.5rem;opacity:0.3;"></i>
                                <p class="mb-0">No student accounts found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Register Student Modal -->
<div class="modal fade" id="registerStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700"><i class="fas fa-user-plus me-2 text-primary"></i> Register New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/admin/students') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control-slms" placeholder="e.g. Babatunde" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control-slms" placeholder="e.g. Okafor" required>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Matriculation Number <span class="text-danger">*</span></label>
                        <input type="text" name="matric_staff_id" class="form-control-slms" placeholder="e.g. 190407089" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-control-slms" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?> (<?= e($dept['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Student Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control-slms" placeholder="student@live.unilag.edu.ng" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Account Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control-slms" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">
                        <i class="fas fa-check me-1"></i> Register Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
