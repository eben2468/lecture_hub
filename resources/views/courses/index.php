<?php
/**
 * Nadics LectureHub — Courses Index View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Courses',
    'page_description' => 'Browse all available courses in your institution.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Courses</h1>
            <p class="page-subtitle">All courses available in your institution.</p>
        </div>
        <?php if (in_array($user_role, ['admin', 'super_admin'])): ?>
            <a href="<?= url('/admin/courses') ?>" class="btn-slms btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add Course
            </a>
        <?php endif; ?>
    </div>

    <!-- Search -->
    <div class="slms-card mb-4 p-3">
        <form method="GET" action="<?= url('/courses') ?>" class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group-slms">
                    <i class="fas fa-search input-icon"></i>
                    <input type="text" name="search" class="form-control-slms"
                           placeholder="Search courses by title or code..."
                           value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn-slms btn-primary flex-fill">
                    <i class="fas fa-filter me-1"></i> Search
                </button>
                <a href="<?= url('/courses') ?>" class="btn-slms btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <!-- Courses Grid -->
    <?php if (!empty($courses)): ?>
        <div class="row g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4">
                    <div class="slms-card p-4 h-100" style="border-left: 4px solid var(--primary);">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="stat-icon icon-primary" style="width:48px;height:48px;font-size:1.2rem;">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <span class="badge-slms badge-primary"><?= e($course['units'] ?? 3) ?> Units</span>
                        </div>
                        <h5 class="fw-700 mb-1"><?= e($course['title']) ?></h5>
                        <div class="text-secondary fw-600 mb-1"><?= e($course['code']) ?></div>
                        <div class="text-muted small mb-3"><?= e($course['department_name'] ?? '') ?></div>
                        <?php if ($course['description']): ?>
                            <p class="small text-muted mb-3" style="line-height:1.5;">
                                <?= e(mb_substr($course['description'], 0, 100)) ?>...
                            </p>
                        <?php endif; ?>
                        <div class="d-flex gap-2 mt-auto">
                            <a href="<?= url('/lectures?course=' . $course['id']) ?>" class="btn-slms btn-sm btn-primary flex-fill">
                                <i class="fas fa-chalkboard-teacher me-1"></i> View Lectures
                            </a>
                            <a href="<?= url('/materials?course=' . $course['id']) ?>" class="btn-slms btn-sm btn-ghost" title="Course Materials">
                                <i class="fas fa-file-alt"></i>
                            </a>
                            <?php if (in_array($user_role, ['lecturer', 'university_admin', 'super_admin'])): ?>
                                <a href="<?= url('/quizzes?course=' . $course['id']) ?>" class="btn-slms btn-sm btn-outline-slms" title="Course Quizzes">
                                    <i class="fas fa-question-circle"></i>
                                </a>
                                <a href="<?= url('/assignments?course=' . $course['id']) ?>" class="btn-slms btn-sm btn-outline-slms" title="Course Assignments">
                                    <i class="fas fa-tasks"></i>
                                </a>
                                <a href="<?= url('/courses/' . $course['id'] . '/enrollments') ?>" class="btn-slms btn-sm btn-outline-slms" title="Manage Enrollments">
                                    <i class="fas fa-users-cog"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="slms-card p-5 text-center">
            <div class="stat-icon icon-primary mx-auto mb-4" style="width:72px;height:72px;font-size:2rem;">
                <i class="fas fa-book-open"></i>
            </div>
            <h4 class="fw-700">No courses found</h4>
            <p class="text-muted">No courses have been registered for your institution yet.</p>
            <?php if (in_array($user_role, ['admin', 'super_admin'])): ?>
                <a href="<?= url('/admin/courses') ?>" class="btn-slms btn-primary mt-2">
                    <i class="fas fa-plus me-1"></i> Add First Course
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php $__view->endSection(); ?>
