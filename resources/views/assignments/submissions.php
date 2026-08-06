<?php
/**
 * Nadics LectureHub — Assignment Submissions View (Lecturer Grading)
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Submissions — ' . ($assignment['title'] ?? ''),
    'page_description' => ($assignment['course_code'] ?? '') . ' Assignment Submissions',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <?php $asgnStatus = $assignment['status'] ?? 'published'; ?>
    <div class="page-header flex-wrap gap-3">
        <div>
            <a href="<?= url('/assignments') ?>" class="text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Assignments</a>
            <div class="d-flex align-items-center gap-2 mt-2">
                <h1 class="page-title mb-0"><?= e($assignment['title']) ?></h1>
                <?php if ($asgnStatus === 'published'): ?>
                    <span class="badge bg-success text-white px-3 py-1 fw-700"><i class="fas fa-check-circle me-1"></i> Published</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark px-3 py-1 fw-700"><i class="fas fa-pen me-1"></i> Draft</span>
                <?php endif; ?>
            </div>
            <p class="page-subtitle"><?= e($assignment['course_code']) ?> — <?= e($assignment['course_title']) ?> | Due: <?= date('M d, Y h:i A', strtotime($assignment['due_date'])) ?></p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form method="POST" action="<?= url('/assignments/' . $assignment['id'] . '/publish') ?>" class="m-0">
                <?= csrf_field() ?>
                <?php if ($asgnStatus === 'published'): ?>
                    <button type="submit" class="btn btn-outline-warning btn-sm font-weight-bold">
                        <i class="fas fa-eye-slash me-1"></i> Unpublish (Save as Draft)
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                        <i class="fas fa-paper-plane me-1"></i> Publish to Enrolled Students
                    </button>
                <?php endif; ?>
            </form>
            <span class="badge-slms badge-info" style="font-size:1rem;padding:8px 20px;">
                <?= count($submissions) ?> Submission<?= count($submissions) !== 1 ? 's' : '' ?>
            </span>
        </div>
    </div>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Matric ID</th>
                        <th>Submitted</th>
                        <th>File</th>
                        <th>Score</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($submissions)): ?>
                        <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary"><?= e($sub['first_name'] . ' ' . $sub['last_name']) ?></strong>
                                </td>
                                <td><code><?= e($sub['matric_staff_id'] ?? 'N/A') ?></code></td>
                                <td class="small"><?= date('M d, Y h:i A', strtotime($sub['submitted_at'])) ?></td>
                                <td>
                                    <a href="<?= url($sub['file_path']) ?>" target="_blank" class="btn-slms btn-sm btn-outline-slms">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </td>
                                <td>
                                    <?php if ($sub['score'] !== null): ?>
                                        <span class="fw-700"><?= number_format($sub['score'], 1) ?> / <?= (int)$assignment['max_score'] ?></span>
                                    <?php else: ?>
                                        <span class="text-warning small">Not graded</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('/assignments/grade/' . $sub['id']) ?>" class="d-flex gap-2 align-items-center">
                                        <?= csrf_field() ?>
                                        <input type="number" name="score" class="form-control-slms form-control-sm" style="width:70px;" placeholder="0" min="0" max="<?= (int)$assignment['max_score'] ?>" value="<?= $sub['score'] !== null ? number_format($sub['score'], 1) : '' ?>" required>
                                        <input type="text" name="feedback" class="form-control-slms form-control-sm" style="width:150px;" placeholder="Feedback..." value="<?= e($sub['feedback'] ?? '') ?>">
                                        <button type="submit" class="btn-slms btn-sm btn-primary">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No submissions received yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
