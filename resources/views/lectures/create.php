<?php
/**
 * Nadics LectureHub — Schedule New Lecture View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Schedule New Lecture',
    'page_description' => 'Create and schedule a new lecture session.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Schedule New Lecture</h1>
            <p class="page-subtitle">Configure lecture details, time slot, and venue assignment.</p>
        </div>
        <a href="<?= url('/lectures') ?>" class="btn-slms btn-ghost">
            <i class="fas fa-arrow-left me-1"></i> Back to Lectures
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="slms-card p-4">
                <form method="POST" action="<?= url('/lectures') ?>">
                    <?= csrf_field() ?>

                    <div class="form-group mb-3">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-control-slms" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= old('course_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Lecture Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control-slms" placeholder="e.g. Introduction to Linear Algebra" value="<?= e(old('title')) ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control-slms" rows="3" placeholder="Lecture outline, topics covered..."><?= e(old('description')) ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Lecture Hall / Venue</label>
                        <select name="lecture_hall_id" class="form-control-slms">
                            <option value="">Virtual (No Physical Venue)</option>
                            <?php foreach ($lecture_halls ?? [] as $hall): ?>
                                <option value="<?= $hall['id'] ?>" <?= old('lecture_hall_id') == $hall['id'] ? 'selected' : '' ?>><?= e($hall['building_name'] . ' — ' . $hall['hall_number']) ?> (Capacity: <?= (int)$hall['capacity'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php
                        $defaultStart = old('scheduled_start') ?: ($default_start ?? date('Y-m-d\TH:i', strtotime('+5 minutes')));
                        $defaultEnd   = old('scheduled_end')   ?: ($default_end   ?? date('Y-m-d\TH:i', strtotime('+1 hour 5 minutes')));
                    ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Scheduled Start <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_start" class="form-control-slms" value="<?= e($defaultStart) ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Scheduled End <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_end" class="form-control-slms" value="<?= e($defaultEnd) ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="<?= url('/lectures') ?>" class="btn-slms btn-ghost">Cancel</a>
                        <button type="submit" class="btn-slms btn-primary">
                            <i class="fas fa-calendar-check me-1"></i> Schedule Lecture
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
