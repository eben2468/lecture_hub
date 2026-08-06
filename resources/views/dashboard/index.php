<?php
/**
 * Nadics LectureHub — Role-Customized Dashboard View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Dashboard',
    'page_description' => 'Overview of your academic activity and lecture schedules.',
]);

$userName = e($user['first_name'] . ' ' . $user['last_name']);
$userRole = e(ucfirst(str_replace('_', ' ', $user_role ?? 'student')));
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Welcome back, <?= $userName ?> 👋</h1>
            <p class="page-subtitle">Here is your live academic overview for today.</p>
        </div>
        <div>
            <?php if (in_array($user_role, ['lecturer', 'admin', 'super_admin'])): ?>
                <a href="<?= url('/lectures/create') ?>" class="btn-slms btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Schedule New Lecture
                </a>
            <?php else: ?>
                <a href="<?= url('/attendance') ?>" class="btn-slms btn-accent">
                    <i class="fas fa-qrcode me-1"></i> Scan Attendance QR
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Streams Notification Banner -->
    <?php if (!empty($activeStreams)): ?>
        <?php foreach ($activeStreams as $stream): ?>
            <div class="alert alert-danger d-flex align-items-center justify-content-between p-4 mb-4 border-0 shadow-lg" style="border-radius: var(--radius-xl); background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; animation: pulse 2s infinite;">
                <div class="d-flex align-items-center gap-3">
                    <div class="spinner-grow text-white" role="status" style="width: 20px; height: 20px;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div>
                        <span class="badge bg-white text-danger fw-700 me-2" style="font-size: 11px;">LIVE NOW</span>
                        <strong style="font-size: 16px;"><?= e($stream['course_code']) ?> — <?= e($stream['lecture_title']) ?></strong>
                        <div class="small opacity-90 mt-1">Conducted by <?= e($stream['lecturer_first_name'] . ' ' . $stream['lecturer_last_name']) ?></div>
                    </div>
                </div>
                <a href="<?= url('/stream/listener/' . $stream['lecture_id']) ?>" class="btn btn-light text-danger fw-700 px-4 py-2 border-0" style="border-radius: var(--radius-lg); font-size: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                    <i class="fas fa-headphones-alt me-1"></i> Join Live Stream
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Stats Grid Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" id="count-courses"><?= (int) ($stats['total_courses'] ?? 0) ?></div>
                    <div class="stat-label">Enrolled Courses</div>
                </div>
                <div class="stat-icon icon-primary">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center justify-content-between">
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active Semester</span>
                <span class="text-muted small">First Semester</span>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" id="count-lectures"><?= (int) ($stats['total_lectures'] ?? 0) ?></div>
                    <div class="stat-label">Lectures Scheduled</div>
                </div>
                <div class="stat-icon icon-success">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center justify-content-between">
                <span class="stat-change up"><i class="fas fa-check"></i> On Schedule</span>
                <span class="text-muted small">This Week</span>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" id="count-attendance"><?= (int) ($stats['attendance_rate'] ?? 94) ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
                <div class="stat-icon icon-info">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center justify-content-between">
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> +4.2%</span>
                <span class="text-muted small">vs Last Month</span>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" id="count-streams"><?= (int) ($stats['active_streams'] ?? 0) ?></div>
                    <div class="stat-label">Live Broadcasts</div>
                </div>
                <div class="stat-icon icon-warning">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center justify-content-between">
                <span class="stat-change up"><i class="fas fa-signal"></i> WebRTC Stream</span>
                <span class="text-muted small">Low-Latency</span>
            </div>
        </div>
    </div>

    <!-- Main Activity Section -->
    <div class="activity-grid">
        <!-- Upcoming Lectures Table -->
        <div class="slms-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="m-0 fw-700"><i class="fas fa-calendar-alt text-secondary me-2"></i> Upcoming Lectures</h5>
                <a href="<?= url('/lectures') ?>" class="text-secondary small fw-600">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-slms">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Lecture Title</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upcomingLectures)): ?>
                                <?php foreach ($upcomingLectures as $lecture): ?>
                                    <tr>
                                        <td><strong class="text-primary"><?= e($lecture['course_code']) ?></strong></td>
                                        <td><?= e($lecture['title']) ?></td>
                                        <td><?= e(date('M d, g:i A', strtotime($lecture['scheduled_start']))) ?></td>
                                        <td>
                                            <?php if (($lecture['is_live'] ?? 0) == 1 && ($lecture['stream_status'] ?? '') === 'streaming'): ?>
                                                <span class="badge-slms badge-success"><i class="fas fa-broadcast-tower me-1"></i> LIVE</span>
                                            <?php else: ?>
                                                <span class="badge-slms badge-primary"><?= e(ucfirst($lecture['status'] === 'live' ? 'scheduled' : $lecture['status'])) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (($lecture['is_live'] ?? 0) == 1 && ($lecture['stream_status'] ?? '') === 'streaming'): ?>
                                                <a href="<?= url('/stream/listener/' . $lecture['id']) ?>" class="btn-slms btn-sm btn-primary">
                                                    <i class="fas fa-headphones me-1"></i> Join Live
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= url('/lectures/' . $lecture['id']) ?>" class="btn-slms btn-sm btn-outline-slms">
                                                    <i class="fas fa-info-circle me-1"></i> Details
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Demo Row if database is clean -->
                                <tr>
                                    <td><strong class="text-primary">CSC 301</strong></td>
                                    <td>Data Structures and Algorithms</td>
                                    <td>Today, 10:00 AM - 12:00 PM</td>
                                    <td><span class="badge-slms badge-success"><i class="fas fa-broadcast-tower me-1"></i> LIVE</span></td>
                                    <td>
                                        <a href="<?= url('/lectures') ?>" class="btn-slms btn-sm btn-primary">
                                            <i class="fas fa-headphones me-1"></i> Join Audio
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong class="text-primary">CSC 305</strong></td>
                                    <td>Database Systems Architecture</td>
                                    <td>Tomorrow, 2:00 PM - 4:00 PM</td>
                                    <td><span class="badge-slms badge-primary">Scheduled</span></td>
                                    <td>
                                        <a href="<?= url('/lectures') ?>" class="btn-slms btn-sm btn-ghost">
                                            <i class="fas fa-info-circle me-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong class="text-primary">CSC 401</strong></td>
                                    <td>Artificial Intelligence & Neural Networks</td>
                                    <td>Thursday, 9:00 AM - 11:00 AM</td>
                                    <td><span class="badge-slms badge-primary">Scheduled</span></td>
                                    <td>
                                        <a href="<?= url('/lectures') ?>" class="btn-slms btn-sm btn-ghost">
                                            <i class="fas fa-info-circle me-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Side Widgets: Attendance & Notifications -->
        <div class="d-flex flex-column gap-4">

            <!-- Quick QR Attendance Widget -->
            <div class="slms-card p-4 text-center">
                <div style="width:64px;height:64px;border-radius:16px;background:rgba(16,185,129,0.1);color:var(--success);display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 15px;">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h4>Attendance Verification</h4>
                <p class="text-muted small">Scan the lecturer's dynamic QR code during active lectures to mark your attendance.</p>
                <a href="<?= url('/attendance') ?>" class="btn-slms btn-success-slms w-100 py-2" style="display:block;text-align:center;">
                    <i class="fas fa-camera me-1"></i> Launch QR Scanner
                </a>
            </div>

            <!-- AI Assistant Shortcut -->
            <div class="slms-card p-4 gradient-accent text-white">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h5 class="m-0 text-white fw-700">AI Study Assistant</h5>
                        <div class="small opacity-75">Instant Lecture Summaries</div>
                    </div>
                </div>
                <p class="small opacity-90 mb-3">Ask questions about your recent lectures or generate revision quizzes automatically.</p>
                <a href="<?= url('/ai-assistant') ?>" class="btn-slms btn-sm" style="background:white;color:var(--secondary);font-weight:700;">
                    Open AI Assistant <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
