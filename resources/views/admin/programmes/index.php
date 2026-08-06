<?php
/**
 * Nadics LectureHub — Programme Management View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Programme Management',
    'page_description' => 'Manage academic degree programmes and majors.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">Programme Management</h1>
            <p class="page-subtitle">Configure academic degree programmes, majors, and qualifications.</p>
        </div>
        <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#createProgrammeModal">
            <i class="fas fa-plus me-1"></i> Add Programme
        </button>
    </div>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Programme Title</th>
                        <th>Degree Type</th>
                        <th>Department</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($programmes)): ?>
                        <?php foreach ($programmes as $prog): ?>
                            <tr>
                                <td><span class="badge-slms badge-primary"><?= e($prog['code']) ?></span></td>
                                <td><strong><?= e($prog['title']) ?></strong></td>
                                <td><span class="badge bg-info-subtle text-info"><?= e($prog['degree_type']) ?></span></td>
                                <td><?= e($prog['department_name']) ?> (<?= e($prog['department_code']) ?>)</td>
                                <td><?= (int)$prog['duration_years'] ?> Years</td>
                                <td>
                                    <?php if ($prog['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('/admin/programmes/' . $prog['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this programme?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Programme">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-graduation-cap mb-2" style="font-size:2.5rem;opacity:0.3;"></i>
                                <p class="mb-0">No academic programmes configured yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Programme Modal -->
<div class="modal fade" id="createProgrammeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700"><i class="fas fa-plus me-2 text-primary"></i> Add Academic Programme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/admin/programmes') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
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
                        <label class="form-label">Programme Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control-slms" placeholder="e.g. BS-CS" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Programme Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control-slms" placeholder="e.g. B.Sc Computer Science" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Degree Type <span class="text-danger">*</span></label>
                            <select name="degree_type" class="form-control-slms" required>
                                <option value="B.Sc">B.Sc</option>
                                <option value="B.Eng">B.Eng</option>
                                <option value="B.A">B.A</option>
                                <option value="M.Sc">M.Sc</option>
                                <option value="Ph.D">Ph.D</option>
                                <option value="Diploma">Diploma</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Duration (Years) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_years" class="form-control-slms" value="4" min="1" max="7" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">
                        <i class="fas fa-check me-1"></i> Save Programme
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
