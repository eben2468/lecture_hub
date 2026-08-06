<?php
/**
 * Nadics LectureHub — Student Timetable Grid View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'My Timetable',
    'page_description' => 'Your weekly class schedule and lecture hall assignments.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Academic Timetable</h1>
            <p class="page-subtitle">Browse your weekly lecture times slots, courses, and allocated lecture halls.</p>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($timetable as $day => $slots): ?>
            <div class="col-lg-12">
                <div class="slms-card p-4">
                    <h4 class="fw-800 text-primary border-bottom pb-2 mb-3">
                        <i class="fas fa-calendar-day me-2 text-accent"></i> <?= $day ?>
                    </h4>
                    
                    <?php if (!empty($slots)): ?>
                        <div class="row g-3">
                            <?php foreach ($slots as $slot): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="p-3 rounded border" style="background: var(--bg-surface-alt); border-left: 4px solid var(--accent) !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary-subtle text-primary"><?= e($slot['course_code']) ?></span>
                                            <span class="small text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                <?= date('h:i A', strtotime($slot['scheduled_start'])) ?>
                                            </span>
                                        </div>
                                        <h5 class="fw-700 mb-1" style="font-size: 1rem;"><?= e($slot['title']) ?></h5>
                                        <div class="small text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i> <?= e($slot['hall_name'] ?? 'Virtual Hall') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i> No lectures scheduled for this day.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php $__view->endSection(); ?>
