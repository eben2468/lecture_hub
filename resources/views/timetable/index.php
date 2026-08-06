<?php
/**
 * Nadics LectureHub — Timetable View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Academic Timetable',
    'page_description' => 'Interactive weekly lecture schedules, time slots, and venues.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Academic Class Timetable</h1>
            <p class="page-subtitle">Weekly lecture schedules, lecture hall venues, and live broadcast shortcuts.</p>
        </div>
        <?php if (in_array($user_role, ['lecturer', 'admin', 'super_admin', 'university_admin'])): ?>
            <a href="<?= url('/lectures/create') ?>" class="btn-slms btn-primary">
                <i class="fas fa-calendar-plus me-1"></i> Schedule New Class
            </a>
        <?php endif; ?>
    </div>

    <!-- Day Selector Tabs -->
    <div class="d-flex align-items-center gap-2 mb-4 overflow-auto pb-2">
        <a href="<?= url('/timetable') ?>" class="btn-slms btn-sm <?= $selectedDay === 'all' ? 'btn-primary' : 'btn-ghost' ?>">
            <i class="fas fa-th me-1"></i> All Days
        </a>
        <?php foreach ($days as $day): ?>
            <a href="<?= url('/timetable?day=' . strtolower($day)) ?>" class="btn-slms btn-sm <?= $selectedDay === strtolower($day) ? 'btn-primary' : 'btn-ghost' ?>">
                <?= e($day) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Timetable Grid -->
    <div class="row g-4">
        <?php foreach ($days as $day): ?>
            <?php 
            if ($selectedDay !== 'all' && $selectedDay !== strtolower($day)) continue;
            $dayLectures = $timetableGrid[$day] ?? [];
            ?>
            <div class="<?= $selectedDay === 'all' ? 'col-lg-6 col-xl-4' : 'col-12' ?>">
                <div class="slms-card h-100">
                    <div class="card-header bg-primary-subtle text-primary border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="fw-700 m-0"><i class="fas fa-calendar-day me-2"></i> <?= e($day) ?></h5>
                        <span class="badge bg-primary text-white rounded-pill"><?= count($dayLectures) ?> Classes</span>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($dayLectures)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($dayLectures as $lec): ?>
                                    <?php 
                                    $start = strtotime($lec['scheduled_start']);
                                    $end = strtotime($lec['scheduled_end']);
                                    $isLive = strtolower($lec['status'] ?? '') === 'live' || !empty($lec['is_live']);
                                    ?>
                                    <div class="p-3 rounded-3 border bg-card-slms hover-shadow transition-all" style="border-left: 4px solid var(--primary-color)!important;">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge-slms badge-primary"><?= e($lec['course_code']) ?></span>
                                            <span class="small fw-700 text-secondary">
                                                <i class="fas fa-clock me-1 text-muted"></i>
                                                <?= date('g:i A', $start) ?> - <?= date('g:i A', $end) ?>
                                            </span>
                                        </div>
                                        <h6 class="fw-700 mb-1 text-dark"><?= e($lec['course_title']) ?></h6>
                                        <p class="small text-muted mb-2">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>
                                            <?= e($lec['lecturer_first_name'] . ' ' . $lec['lecturer_last_name']) ?>
                                        </p>
                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                            <span class="small text-muted">
                                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                <?= e($lec['hall_name'] ? ($lec['hall_name'] . ($lec['hall_building'] ? ' (' . $lec['hall_building'] . ')' : '')) : 'Virtual Studio') ?>
                                            </span>
                                            <?php if ($isLive): ?>
                                                <a href="<?= url('/lectures/' . $lec['id']) ?>" class="btn btn-sm btn-danger animate-pulse">
                                                    <i class="fas fa-play-circle me-1"></i> Watch Live
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= url('/lectures/' . $lec['id']) ?>" class="btn-slms btn-sm btn-ghost">
                                                    View Details <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times mb-2" style="font-size:2rem;opacity:0.3;"></i>
                                <p class="small m-0">No classes scheduled for <?= e($day) ?>.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php $__view->endSection(); ?>
