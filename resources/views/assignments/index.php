<?php
/**
 * Nadics LectureHub — Assignments Index View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Assignments',
    'page_description' => 'Manage and submit academic assignments.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Academic Assignments</h1>
            <p class="page-subtitle"><?= $userRole === 'student' ? 'View and submit your course assignments.' : 'Create, manage, and grade assignments.' ?></p>
        </div>
        <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
            <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                <i class="fas fa-plus-circle me-1"></i> Create Assignment
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($selectedCourse)): ?>
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 border-0 shadow-sm" style="border-radius: var(--radius-lg); background: rgba(37, 99, 235, 0.1); color: var(--secondary-dark);">
            <div>
                <i class="fas fa-filter me-2"></i>
                Showing assignments for course: <strong><?= e($selectedCourse['code']) ?> — <?= e($selectedCourse['title']) ?></strong>
            </div>
            <a href="<?= url('/assignments') ?>" class="btn btn-sm btn-outline-primary" style="border-color: rgba(37, 99, 235, 0.3);">Clear Filter</a>
        </div>
    <?php endif; ?>

    <!-- Assignments Cards -->
    <?php if (!empty($assignments)): ?>
        <div class="row g-4">
            <?php foreach ($assignments as $asgn): ?>
                <?php
                    $isPastDue  = strtotime($asgn['due_date']) < time();
                    $dueDate    = date('M d, Y h:i A', strtotime($asgn['due_date']));
                    $daysLeft   = ceil((strtotime($asgn['due_date']) - time()) / 86400);
                    $submission = $submissions[$asgn['id']] ?? null;
                    $asgnStatus = $asgn['status'] ?? 'published';
                    $isStaff    = in_array($userRole, ['lecturer', 'university_admin', 'super_admin']);
                ?>
                <div class="col-lg-6">
                    <div class="slms-card p-4 h-100" style="border-left:4px solid <?= $isPastDue ? 'var(--danger)' : 'var(--accent)' ?>;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge-slms badge-primary"><?= e($asgn['course_code']) ?></span>
                            <div class="d-flex gap-2 align-items-center">
                                <?php if ($isStaff): ?>
                                    <?php if ($asgnStatus === 'published'): ?>
                                        <span class="badge bg-success-subtle text-success"><i class="fas fa-check-circle me-1"></i> Published</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning" style="color:#d97706!important;background:rgba(245,158,11,0.15)!important;"><i class="fas fa-pen me-1"></i> Draft</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <span class="badge-slms badge-info"><?= (int)$asgn['max_score'] ?> pts</span>
                                <?php if ($isPastDue): ?>
                                    <span class="badge-slms badge-danger">Past Due</span>
                                <?php else: ?>
                                    <span class="badge-slms badge-success"><?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?> left</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h5 class="fw-700 text-primary mb-1"><?= e($asgn['title']) ?></h5>
                        <p class="text-muted small mb-3" style="line-height:1.7;max-height:60px;overflow:hidden;">
                            <?= e(substr($asgn['description'], 0, 150)) ?><?= strlen($asgn['description']) > 150 ? '...' : '' ?>
                        </p>

                        <div class="d-flex align-items-center justify-content-between small text-muted mb-3">
                            <span><i class="fas fa-calendar-alt me-1"></i> Due: <?= $dueDate ?></span>
                            <span><i class="fas fa-user me-1"></i> <?= e($asgn['creator_first_name'] . ' ' . $asgn['creator_last_name']) ?></span>
                        </div>

                        <!-- Student: Submit / View Status -->
                        <?php if ($userRole === 'student'): ?>
                            <?php if ($submission): ?>
                                <div class="p-2 rounded-2 mb-2" style="background:var(--bg-tertiary);">
                                    <div class="d-flex justify-content-between align-items-center small">
                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> Submitted <?= date('M d', strtotime($submission['submitted_at'])) ?></span>
                                        <?php if ($submission['score'] !== null): ?>
                                            <span class="fw-700"><?= number_format($submission['score'], 1) ?> / <?= (int)$asgn['max_score'] ?></span>
                                        <?php else: ?>
                                            <span class="text-warning">Awaiting Grade</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($submission['feedback']): ?>
                                        <p class="text-muted small mt-2 mb-0"><i class="fas fa-comment me-1"></i> <?= e($submission['feedback']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!$isPastDue): ?>
                                <form method="POST" action="<?= url('/assignments/' . $asgn['id'] . '/submit') ?>" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                                    <?= csrf_field() ?>
                                    <input type="file" name="submission_file" class="form-control-slms form-control-sm flex-fill" required>
                                    <button type="submit" class="btn-slms btn-sm btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> <?= $submission ? 'Resubmit' : 'Submit' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Lecturer: View Submissions & Publish Control -->
                        <?php if ($isStaff): ?>
                            <div class="d-flex gap-2 align-items-center">
                                <a href="<?= url('/assignments/' . $asgn['id'] . '/submissions') ?>" class="btn-slms btn-sm btn-outline-slms flex-fill">
                                    <i class="fas fa-eye me-1"></i> View Submissions
                                </a>
                                <form method="POST" action="<?= url('/assignments/' . $asgn['id'] . '/publish') ?>" class="m-0">
                                    <?= csrf_field() ?>
                                    <?php if ($asgnStatus === 'published'): ?>
                                        <button type="submit" class="btn-slms btn-sm btn-outline-warning" title="Save as Draft">
                                            <i class="fas fa-eye-slash me-1"></i> Unpublish
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn-slms btn-sm btn-success" title="Publish to Enrolled Students">
                                            <i class="fas fa-paper-plane me-1"></i> Publish
                                        </button>
                                    <?php endif; ?>
                                </form>
                                <?php if ($asgn['file_attachment']): ?>
                                    <a href="<?= url($asgn['file_attachment']) ?>" class="btn-slms btn-sm btn-outline-slms" target="_blank">
                                        <i class="fas fa-paperclip"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="slms-card p-5 text-center">
            <div class="mb-3" style="font-size:3rem;opacity:0.3;"><i class="fas fa-tasks"></i></div>
            <h4 class="text-muted">No assignments found</h4>
            <p class="text-muted small">Assignments will appear here once they are created.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Create Assignment Modal (Lecturers) -->
<?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
<div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700"><i class="fas fa-tasks me-2"></i> Create Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/assignments') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-control-slms" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['code'] . ' — ' . $c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Publishing Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control-slms">
                            <option value="published">Publish Immediately (Visible to Enrolled Students)</option>
                            <option value="draft">Save as Draft (Visible only to Lecturers)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Assignment Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control-slms" placeholder="e.g. Assignment 1: Sorting Algorithms" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Instructions <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control-slms" rows="4" placeholder="Detailed assignment instructions..." required></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Max Score <span class="text-danger">*</span></label>
                            <input type="number" name="max_score" class="form-control-slms" value="100" min="1" max="100" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="due_date" class="form-control-slms" required>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">File Attachment (Optional)</label>
                        <input type="file" name="attachment" class="form-control-slms">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Create Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__view->endSection(); ?>
