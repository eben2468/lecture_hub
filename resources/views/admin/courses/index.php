<?php
/**
 * Nadics LectureHub — Course Registry Management View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Course Registry',
    'page_description' => 'Manage academic course catalog and lecturer assignments.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Academic Course Registry</h1>
            <p class="page-subtitle">Configure course codes, credit units, academic levels, and lecturer allocations.</p>
        </div>
        <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="fas fa-plus-circle me-1"></i> Add New Course
        </button>
    </div>

    <!-- Alert Container -->
    <div id="courseAlertContainer"></div>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Department</th>
                        <th>Units</th>
                        <th>Level</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $c): ?>
                            <tr id="courseRow<?= $c['id'] ?>">
                                <td><strong class="text-primary"><?= e($c['code']) ?></strong></td>
                                <td><?= e($c['title']) ?></td>
                                <td><?= e($c['department_name']) ?></td>
                                <td><span class="badge-slms badge-info"><?= (int)$c['credit_unit'] ?> Units</span></td>
                                <td><?= (int)$c['level'] ?> Level</td>
                                <td><?= e(ucfirst($c['semester'])) ?> Semester</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAllocateModal(<?= $c['id'] ?>, '<?= e($c['code']) ?>', '<?= e(addslashes($c['title'])) ?>')" title="Allocate Course to Lecturer">
                                            <i class="fas fa-user-plus me-1"></i> Allocate
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCourse(<?= $c['id'] ?>, '<?= e($c['code']) ?>')" title="Delete Course">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No courses registered.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Global Allocate Lecturer Modal (Outside Table to prevent blinking/backdrop flickering) -->
<div class="modal fade" id="globalAllocateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700" id="allocateModalTitle"><i class="fas fa-user-plus me-2 text-primary"></i> Allocate Lecturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="allocateLecturerForm">
                <?= csrf_field() ?>
                <div class="modal-body text-start">
                    <div class="form-group mb-3">
                        <label class="form-label">Course Session</label>
                        <input type="text" id="allocateCourseDisplay" class="form-control-slms" value="" readonly disabled>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Lecturer <span class="text-danger">*</span></label>
                        <select name="lecturer_id" id="allocateLecturerSelect" class="form-control-slms" required>
                            <option value="">Select Lecturer</option>
                            <?php foreach ($lecturers ?? [] as $lec): ?>
                                <option value="<?= $lec['id'] ?>"><?= e($lec['first_name'] . ' ' . $lec['last_name']) ?> (ID: <?= e($lec['matric_staff_id'] ?: 'N/A') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_coordinator" value="1" id="globalCoordCheck" checked>
                        <label class="form-check-label fw-600" for="globalCoordCheck">
                            Assign as Lead Course Coordinator
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary" id="allocateSubmitBtn">
                        <i class="fas fa-check me-1"></i> Confirm Allocation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700"><i class="fas fa-plus-circle me-2 text-primary"></i> Add New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/admin/courses') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-control-slms" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments ?? [] as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Course Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control-slms" placeholder="e.g. CSC 301" required>
                        </div>
                        <div class="col-md-8 form-group">
                            <label class="form-label">Course Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control-slms" placeholder="e.g. Data Structures" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Credit Units <span class="text-danger">*</span></label>
                            <input type="number" name="credit_unit" class="form-control-slms" value="3" min="1" max="6" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Academic Level <span class="text-danger">*</span></label>
                            <select name="level" class="form-control-slms" required>
                                <option value="100">100 Level</option>
                                <option value="200">200 Level</option>
                                <option value="300" selected>300 Level</option>
                                <option value="400">400 Level</option>
                                <option value="500">500 Level</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-control-slms" required>
                                <option value="first">First</option>
                                <option value="second">Second</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control-slms" rows="2" placeholder="Course outline & syllabus summary"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentAllocateCourseId = null;

function openAllocateModal(courseId, code, title) {
    currentAllocateCourseId = courseId;
    const targetUrl = '<?= url("/admin/courses/") ?>' + courseId + '/allocate';
    
    const allocForm = document.getElementById('allocateLecturerForm');
    if (allocForm) {
        allocForm.action = targetUrl;
        allocForm.method = 'POST';
    }

    document.getElementById('allocateModalTitle').innerHTML = '<i class="fas fa-user-plus me-2 text-primary"></i> Allocate Lecturer to ' + code;
    document.getElementById('allocateCourseDisplay').value = code + ' — ' + title;
    document.getElementById('allocateLecturerSelect').value = '';
    document.getElementById('globalCoordCheck').checked = true;
    
    const modalEl = document.getElementById('globalAllocateModal');
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    const allocForm = document.getElementById('allocateLecturerForm');
    if (allocForm) {
        allocForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentAllocateCourseId) return;

            const submitBtn = document.getElementById('allocateSubmitBtn');
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Allocating...';

            const formData = new FormData(allocForm);

            try {
                const targetUrl = '<?= url("/admin/courses/") ?>' + currentAllocateCourseId + '/allocate';
                const response = await fetch(targetUrl, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const res = await response.json();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;

                if (res.success) {
                    const modalEl = document.getElementById('globalAllocateModal');
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();

                    showAlert('success', res.message || 'Course allocated to lecturer successfully!');
                } else {
                    showAlert('danger', res.message || 'Allocation failed.');
                }
            } catch (err) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
                
                // Fallback to standard form submission if non-JSON response
                allocForm.action = '<?= url("/admin/courses/") ?>' + currentAllocateCourseId + '/allocate';
                allocForm.submit();
            }
        });
    }
});

async function deleteCourse(courseId, code) {
    if (!confirm('Are you sure you want to delete course ' + code + ' from the registry?')) return;

    try {
        const formData = new FormData();
        formData.append('_token', '<?= csrf_token() ?>');

        const response = await fetch('<?= url("/admin/courses/") ?>' + courseId + '/delete', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });

        const res = await response.json();
        if (res.success) {
            const row = document.getElementById('courseRow' + courseId);
            if (row) row.remove();
            showAlert('success', 'Course ' + code + ' deleted from registry.');
        } else {
            showAlert('danger', res.message || 'Failed to delete course.');
        }
    } catch (err) {
        window.location.reload();
    }
}

function showAlert(type, msg) {
    const alertBox = document.createElement('div');
    alertBox.className = `alert alert-${type} alert-dismissible fade show mb-4`;
    alertBox.style.borderRadius = '12px';
    alertBox.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    const container = document.getElementById('courseAlertContainer');
    container.innerHTML = '';
    container.appendChild(alertBox);
}
</script>
<?php $__view->endSection(); ?>
