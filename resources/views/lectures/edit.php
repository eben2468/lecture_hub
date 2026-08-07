<?php
/**
 * Nadics LectureHub — Edit Lecture View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Edit Lecture — ' . ($lecture['title'] ?? ''),
    'page_description' => 'Update the details and schedule for this lecture.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Lecture</h1>
            <p class="page-subtitle">Update lecture details, time slot, and venue assignment.</p>
        </div>
        <a href="<?= url('/lectures/' . $lecture['id']) ?>" class="btn-slms btn-ghost">
            <i class="fas fa-arrow-left me-1"></i> Back to Details
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="slms-card p-4">
                <form method="POST" action="<?= url('/lectures/' . $lecture['id']) ?>">
                    <?= csrf_field() ?>

                    <?php
                        $isEditable = ($lecture['status'] === 'scheduled');
                    ?>

                    <?php if (!$isEditable): ?>
                        <div class="alert alert-warning mb-4" style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;border-radius:8px;">
                            <i class="fas fa-exclamation-triangle me-2"></i> This lecture has already started or completed. You can only update the Title and Description. The Course, Venue, and Schedule times cannot be modified.
                        </div>
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <?php if ($isEditable): ?>
                            <select name="course_id" class="form-control-slms" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses ?? [] as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (old('course_id', $lecture['course_id']) == $c['id']) ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="hidden" name="course_id" value="<?= $lecture['course_id'] ?>">
                            <select class="form-control-slms" disabled>
                                <?php foreach ($courses ?? [] as $c): ?>
                                    <?php if ($c['id'] == $lecture['course_id']): ?>
                                        <option selected><?= e($c['code'] . ' — ' . $c['title']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Lecture Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control-slms" placeholder="e.g. Introduction to Linear Algebra" value="<?= e(old('title', $lecture['title'])) ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control-slms" rows="3" placeholder="Lecture outline, topics covered..."><?= e(old('description', $lecture['description'])) ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Lecture Hall / Venue</label>
                        <?php if ($isEditable): ?>
                            <select name="lecture_hall_id" class="form-control-slms">
                                <option value="">Virtual (No Physical Venue)</option>
                                <?php foreach ($lecture_halls ?? [] as $hall): ?>
                                    <option value="<?= $hall['id'] ?>" <?= (old('lecture_hall_id', $lecture['lecture_hall_id']) == $hall['id']) ? 'selected' : '' ?>><?= e($hall['building_name'] . ' — ' . $hall['hall_number']) ?> (Capacity: <?= (int)$hall['capacity'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="hidden" name="lecture_hall_id" value="<?= $lecture['lecture_hall_id'] ?>">
                            <select class="form-control-slms" disabled>
                                <option value="">Virtual (No Physical Venue)</option>
                                <?php foreach ($lecture_halls ?? [] as $hall): ?>
                                    <?php if ($hall['id'] == $lecture['lecture_hall_id']): ?>
                                        <option selected><?= e($hall['building_name'] . ' — ' . $hall['hall_number']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <?php
                        $defaultStart = old('scheduled_start') ?: date('Y-m-d\TH:i', strtotime($lecture['scheduled_start']));
                        $defaultEnd   = old('scheduled_end')   ?: date('Y-m-d\TH:i', strtotime($lecture['scheduled_end']));
                    ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Scheduled Start <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_start" class="form-control-slms" value="<?= e($defaultStart) ?>" <?= $isEditable ? 'required' : 'disabled' ?>>
                            <?php if (!$isEditable): ?>
                                <input type="hidden" name="scheduled_start" value="<?= e($defaultStart) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Scheduled End <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_end" class="form-control-slms" value="<?= e($defaultEnd) ?>" <?= $isEditable ? 'required' : 'disabled' ?>>
                            <?php if (!$isEditable): ?>
                                <input type="hidden" name="scheduled_end" value="<?= e($defaultEnd) ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="<?= url('/lectures/' . $lecture['id']) ?>" class="btn-slms btn-ghost">Cancel</a>
                        <button type="submit" class="btn-slms btn-primary">
                            <i class="fas fa-save me-1"></i> Update Lecture
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
