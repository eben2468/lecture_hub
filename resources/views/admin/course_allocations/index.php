<?php
/**
 * Nadics LectureHub — Course Allocation View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Course Allocation Management',
    'page_description' => 'Allocate courses to lecturers and manage course coordinators.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">Course Allocation Management</h1>
            <p class="page-subtitle">Allocate academic courses to lecturers and designate course coordinators.</p>
        </div>
        <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#allocateCourseModal">
            <i class="fas fa-plus me-1"></i> Allocate Course
        </button>
    </div>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Assigned Lecturer</th>
                        <th>Staff ID</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allocations)): ?>
                        <?php foreach ($allocations as $alloc): ?>
                            <tr>
                                <td><span class="badge-slms badge-primary"><?= e($alloc['course_code']) ?></span></td>
                                <td><strong><?= e($alloc['course_title']) ?></strong> (<?= (int)($alloc['unit_load'] ?? 3) ?> Units)</td>
                                <td>
                                    <div class="fw-600"><?= e($alloc['first_name'] . ' ' . $alloc['last_name']) ?></div>
                                    <div class="small text-muted"><?= e($alloc['email']) ?></div>
                                </td>
                                <td><code><?= e($alloc['matric_staff_id'] ?: 'N/A') ?></code></td>
                                <td>
                                    <?php if (!empty($alloc['is_coordinator'])): ?>
                                        <span class="badge bg-warning-subtle text-warning" style="color:#d97706!important;background:rgba(245,158,11,0.15)!important;"><i class="fas fa-star me-1"></i> Course Coordinator</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Co-Lecturer</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('/admin/course-allocations/' . $alloc['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Remove this course allocation?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Allocation">
                                            <i class="fas fa-trash-alt me-1"></i> De-allocate
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-tasks mb-2" style="font-size:2.5rem;opacity:0.3;"></i>
                                <p class="mb-0">No course allocations recorded yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Allocate Course Modal -->
<div class="modal fade" id="allocateCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700"><i class="fas fa-plus me-2 text-primary"></i> Allocate Course to Lecturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/admin/course-allocations') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-control-slms" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['code'] . ' — ' . $c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Lecturer <span class="text-danger">*</span></label>
                        <select name="lecturer_id" class="form-control-slms" required>
                            <option value="">Select Lecturer</option>
                            <?php foreach ($lecturers as $lec): ?>
                                <option value="<?= $lec['id'] ?>"><?= e($lec['first_name'] . ' ' . $lec['last_name']) ?> (Staff ID: <?= e($lec['matric_staff_id'] ?: 'N/A') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                        <select name="academic_session_id" class="form-control-slms" required>
                            <?php foreach ($sessions as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= !empty($s['is_current']) ? 'selected' : '' ?>><?= e($s['name'] ?? '2025/2026') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_coordinator" value="1" id="coordCheck" checked>
                        <label class="form-check-label fw-600" for="coordCheck">
                            Assign as Lead Course Coordinator
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">
                        <i class="fas fa-check me-1"></i> Allocate Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
