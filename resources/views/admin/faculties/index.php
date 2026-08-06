<?php
/**
 * Nadics LectureHub — Faculty Management View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Faculty Management',
    'page_description' => 'Manage university faculties and colleges.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Faculty & Colleges Directory</h1>
            <p class="page-subtitle">Configure academic faculties across institutions.</p>
        </div>
        <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
            <i class="fas fa-plus-circle me-1"></i> Add New Faculty
        </button>
    </div>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Faculty Name</th>
                        <th>University</th>
                        <th>Dean of Faculty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($faculties)): ?>
                        <?php foreach ($faculties as $fac): ?>
                            <tr>
                                <td><span class="badge-slms badge-primary"><?= e($fac['code']) ?></span></td>
                                <td><strong class="text-primary"><?= e($fac['name']) ?></strong></td>
                                <td><?= e($fac['university_name']) ?> (<?= e($fac['university_code']) ?>)</td>
                                <td><?= e($fac['dean_name'] ?: 'Not Assigned') ?></td>
                                <td>
                                    <button class="btn-slms btn-sm btn-outline-slms"><i class="fas fa-edit"></i> Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No faculties found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Faculty Modal -->
<div class="modal fade" id="addFacultyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700">Add Faculty</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/admin/faculties') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">University</label>
                        <select name="university_id" class="form-control-slms" required>
                            <option value="">Select University</option>
                            <?php foreach ($universities ?? [] as $univ): ?>
                                <option value="<?= $univ['id'] ?>"><?= e($univ['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8 form-group">
                            <label class="form-label">Faculty Name</label>
                            <input type="text" name="name" class="form-control-slms" placeholder="e.g. Faculty of Science" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control-slms" placeholder="e.g. FSC" required>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Dean Name</label>
                        <input type="text" name="dean_name" class="form-control-slms" placeholder="e.g. Prof. Elijah Johnson">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">Save Faculty</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
