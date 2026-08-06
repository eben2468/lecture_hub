<?php
/**
 * Nadics LectureHub — Lectures Index View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'All Lectures',
    'page_description' => 'Browse and manage scheduled, live, and completed lectures.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Lecture Schedule</h1>
            <p class="page-subtitle">View, schedule, and manage all lecture sessions.</p>
        </div>
        <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
            <a href="<?= url('/lectures/create') ?>" class="btn-slms btn-primary">
                <i class="fas fa-calendar-plus me-1"></i> Schedule Lecture
            </a>
        <?php endif; ?>
    </div>

    <!-- Filter Bar -->
    <div class="slms-card mb-4 p-3">
        <form method="GET" action="<?= url('/lectures') ?>" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group-slms">
                    <i class="fas fa-search input-icon"></i>
                    <input type="text" name="search" class="form-control-slms" placeholder="Search lectures by title..." value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control-slms">
                    <option value="">All Statuses</option>
                    <option value="scheduled" <?= ($selectedStatus ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="live" <?= ($selectedStatus ?? '') === 'live' ? 'selected' : '' ?>>Live Now</option>
                    <option value="completed" <?= ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn-slms btn-primary flex-fill"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="<?= url('/lectures') ?>" class="btn-slms btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <!-- Lectures Grid -->
    <?php if (!empty($lectures)): ?>
        <div class="row g-4">
            <?php foreach ($lectures as $lec): ?>
                <?php
                    $statusClass = match($lec['status']) {
                        'live'      => 'badge-danger',
                        'completed' => 'badge-success',
                        'cancelled' => 'badge-warning',
                        default     => 'badge-info',
                    };
                    $statusIcon = match($lec['status']) {
                        'live'      => 'fas fa-broadcast-tower',
                        'completed' => 'fas fa-check-circle',
                        'cancelled' => 'fas fa-times-circle',
                        default     => 'fas fa-clock',
                    };
                    $startDate = date('M d, Y', strtotime($lec['scheduled_start']));
                    $startTime = date('h:i A', strtotime($lec['scheduled_start']));
                    $endTime   = date('h:i A', strtotime($lec['scheduled_end']));
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="slms-card p-4 h-100" style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 40px rgba(0,0,0,.15)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge-slms badge-primary"><?= e($lec['course_code']) ?></span>
                            <?php 
                                $dispStatus = ($lec['status'] === 'live' || ($lec['is_live'] ?? 0) == 1) ? 'live' : $lec['status'];
                                $dispClass = match($dispStatus) {
                                    'live'      => 'badge-danger',
                                    'completed' => 'badge-success',
                                    'cancelled' => 'badge-warning',
                                    default     => 'badge-info',
                                };
                                $dispIcon = match($dispStatus) {
                                    'live'      => 'fas fa-broadcast-tower',
                                    'completed' => 'fas fa-check-circle',
                                    'cancelled' => 'fas fa-times-circle',
                                    default     => 'fas fa-clock',
                                };
                            ?>
                            <span class="badge-slms <?= $dispClass ?>">
                                <i class="<?= $dispIcon ?> me-1"></i> <?= ucfirst($dispStatus) ?>
                            </span>
                        </div>

                        <h5 class="fw-700 text-primary mb-1" style="line-height:1.3;">
                            <a href="<?= url('/lectures/' . $lec['id']) ?>" style="color:inherit;text-decoration:none;">
                                <?= e($lec['title']) ?>
                            </a>
                        </h5>
                        <p class="text-muted small mb-3"><?= e($lec['course_title']) ?></p>

                        <div class="small" style="color:var(--text-secondary);">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-calendar-alt" style="width:16px;"></i>
                                <span><?= $startDate ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-clock" style="width:16px;"></i>
                                <span><?= $startTime ?> — <?= $endTime ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-chalkboard-teacher" style="width:16px;"></i>
                                <span><?= e($lec['lecturer_first_name'] . ' ' . $lec['lecturer_last_name']) ?></span>
                            </div>
                        </div>

                        <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
                            <?php if (in_array($lec['status'], ['scheduled', 'live'])): ?>
                                <div class="mt-3">
                                    <a href="<?= url('/stream/broadcaster/' . $lec['id']) ?>" class="btn-slms btn-danger-slms w-100 text-center">
                                        <i class="fas fa-broadcast-tower me-1"></i> Live Studio
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($lec['status'] === 'live'): ?>
                                <div class="mt-3">
                                    <a href="<?= url('/stream/listener/' . $lec['id']) ?>" class="btn-slms btn-primary w-100 text-center" style="background:#10B981; border-color:#10B981;">
                                        <i class="fas fa-play-circle me-1"></i> Watch Live
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="slms-card p-5 text-center">
            <div class="mb-3" style="font-size:3rem;opacity:0.3;"><i class="fas fa-chalkboard"></i></div>
            <h4 class="text-muted">No lectures found</h4>
            <p class="text-muted small">Lectures will appear here once they are scheduled.</p>
        </div>
    <?php endif; ?>
</div>
<?php $__view->endSection(); ?>
