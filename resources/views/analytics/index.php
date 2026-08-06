<?php
/**
 * Nadics LectureHub — Analytics & Reports View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Analytics & Reports',
    'page_description' => 'Academic performance metrics and system analytics.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Analytics & Reports</h1>
            <p class="page-subtitle">Academic performance metrics and system-wide statistics.</p>
        </div>
        <button class="btn-slms btn-ghost" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid mb-4">
        <div class="stat-card stat-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value"><?= $stats['total_lectures'] ?></div>
                    <div class="stat-label">Total Lectures</div>
                </div>
                <div class="stat-icon icon-primary"><i class="fas fa-chalkboard-teacher"></i></div>
            </div>
            <div class="mt-3">
                <?php if ($stats['live_now'] > 0): ?>
                    <span class="badge-slms badge-success"><i class="fas fa-broadcast-tower me-1"></i> <?= $stats['live_now'] ?> Live Now</span>
                <?php else: ?>
                    <span class="text-muted small">No live sessions</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="stat-card stat-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value"><?= $stats['total_students'] ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-icon icon-success"><i class="fas fa-user-graduate"></i></div>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value"><?= $stats['total_materials'] ?></div>
                    <div class="stat-label">Course Materials</div>
                </div>
                <div class="stat-icon icon-info"><i class="fas fa-file-alt"></i></div>
            </div>
        </div>
        <div class="stat-card stat-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value"><?= $stats['total_assignments'] ?></div>
                    <div class="stat-label">Assignments</div>
                </div>
                <div class="stat-icon icon-warning"><i class="fas fa-tasks"></i></div>
            </div>
        </div>
    </div>

    <!-- Recent Lectures Table -->
    <div class="activity-grid" style="grid-template-columns: 2fr 1fr;">
        <div class="slms-card">
            <div class="card-header">
                <h5 class="m-0 fw-700"><i class="fas fa-chart-bar text-secondary me-2"></i> Recent Lecture Activity</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-slms">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Lecture</th>
                                <th>Scheduled</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentLectures)): ?>
                                <?php foreach ($recentLectures as $lec): ?>
                                    <tr>
                                        <td><strong class="text-primary"><?= e($lec['course_code']) ?></strong></td>
                                        <td><?= e($lec['title']) ?></td>
                                        <td class="text-muted small"><?= e(date('M d, g:i A', strtotime($lec['scheduled_start']))) ?></td>
                                        <td>
                                            <?php if ($lec['is_live']): ?>
                                                <span class="badge-slms badge-success">LIVE</span>
                                            <?php else: ?>
                                                <span class="badge-slms badge-primary"><?= e(ucfirst($lec['status'])) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No lecture data available yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Submission Stats -->
        <div class="slms-card p-4">
            <h5 class="fw-700 mb-4"><i class="fas fa-chart-pie text-secondary me-2"></i> Assignment Stats</h5>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-600">Total Assignments</span>
                    <span class="fw-700 text-primary"><?= $stats['total_assignments'] ?></span>
                </div>
                <div style="height:8px;background:var(--bg-secondary);border-radius:4px;overflow:hidden;">
                    <div style="height:100%;width:100%;background:var(--primary);border-radius:4px;"></div>
                </div>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-600">Submissions Received</span>
                    <span class="fw-700 text-success"><?= $stats['total_submissions'] ?></span>
                </div>
                <div style="height:8px;background:var(--bg-secondary);border-radius:4px;overflow:hidden;">
                    <?php $rate = $stats['total_assignments'] > 0 ? min(100, round($stats['total_submissions'] / max(1, $stats['total_assignments']) * 100)) : 0; ?>
                    <div style="height:100%;width:<?= $rate ?>%;background:var(--success);border-radius:4px;transition:width 1s;"></div>
                </div>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-600">Attendance Rate</span>
                    <span class="fw-700 text-secondary">94%</span>
                </div>
                <div style="height:8px;background:var(--bg-secondary);border-radius:4px;overflow:hidden;">
                    <div style="height:100%;width:94%;background:var(--secondary);border-radius:4px;"></div>
                </div>
            </div>
            <a href="<?= url('/lectures') ?>" class="btn-slms btn-primary w-100 mt-2">
                <i class="fas fa-arrow-right me-1"></i> View All Lectures
            </a>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
