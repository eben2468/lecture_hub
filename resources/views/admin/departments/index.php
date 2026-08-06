<?php
/**
 * Nadics LectureHub — Department Management View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Department Management',
    'page_description' => 'Manage academic departments.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Department Directory</h1>
            <p class="page-subtitle">Manage academic departments across faculties.</p>
        </div>
        <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal">
            <i class="fas fa-plus-circle me-1"></i> Add Department
        </button>
    </div>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Department Name</th>
                        <th>Parent Faculty</th>
                        <th>Head of Dept (HOD)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td><span class="badge-slms badge-primary"><?= e($dept['code']) ?></span></td>
                                <td><strong class="text-primary"><?= e($dept['name']) ?></strong></td>
                                <td><?= e($dept['faculty_name']) ?> (<?= e($dept['faculty_code']) ?>)</td>
                                <td><?= e($dept['hod_name'] ?: 'Not Assigned') ?></td>
                                <td>
                                    <button class="btn-slms btn-sm btn-outline-slms"><i class="fas fa-edit"></i> Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No departments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/admin/departments') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Parent Faculty</label>
                        <select name="faculty_id" class="form-control-slms" required>
                            <option value="">Select Faculty</option>
                            <?php foreach ($faculties ?? [] as $fac): ?>
                                <option value="<?= $fac['id'] ?>"><?= e($fac['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8 form-group">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="name" class="form-control-slms" placeholder="e.g. Department of Computer Sciences" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control-slms" placeholder="e.g. CSC" required>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">HOD Name</label>
                        <input type="text" name="hod_name" class="form-control-slms" placeholder="e.g. Dr. A. O. Adebayo">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
