<?php
/**
 * Nadics LectureHub — Lecture Recordings Management View
 * Lecturers can view, play, download, and delete recorded lecture streams.
 */
$auth = \Core\Auth::getInstance();
$userRole = $auth->role() ?? 'student';

$__view->layout('layouts.app', [
    'page_title'       => 'Lecture Recordings',
    'page_description' => 'View, play, download, and manage recorded lecture streams.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-compact-disc text-accent me-2"></i> Lecture Recordings</h1>
            <p class="page-subtitle">Browse, playback, download, and manage your recorded lecture streams.</p>
        </div>
        <span class="badge-slms badge-info" style="font-size:1rem;padding:8px 20px;">
            <?= count($recordings ?? []) ?> Recording<?= count($recordings ?? []) !== 1 ? 's' : '' ?>
        </span>
    </div>

    <?php if (!empty($recordings)): ?>
        <div class="row g-4">
            <?php foreach ($recordings as $rec): ?>
                <?php
                    $hasFile = !empty($rec['audio_file_path']);
                    $filePath = $hasFile ? url($rec['audio_file_path']) : '';
                    $fileSize = $rec['recording_file_size'] ?? 0;
                    $duration = (int)($rec['duration_seconds'] ?? 0);
                    $durationFormatted = sprintf('%02d:%02d:%02d', floor($duration / 3600), floor(($duration % 3600) / 60), $duration % 60);
                    $recordedDate = $rec['ended_at'] ? date('M d, Y h:i A', strtotime($rec['ended_at'])) : 'N/A';
                    $fileSizeFormatted = $fileSize > 0 ? number_format($fileSize / (1024 * 1024), 2) . ' MB' : '—';
                ?>
                <div class="col-xl-6 col-lg-6 col-md-12">
                    <div class="slms-card p-0 overflow-hidden recording-card" style="transition:transform 0.2s,box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 40px rgba(0,0,0,.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                        <!-- Card Header -->
                        <div class="p-4 pb-0">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge-slms badge-primary"><?= e($rec['course_code']) ?></span>
                                <?php if ($hasFile): ?>
                                    <span class="badge bg-success text-white px-2 py-1" style="font-size:11px;border-radius:6px;">
                                        <i class="fas fa-check-circle me-1"></i> Available
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white px-2 py-1" style="font-size:11px;border-radius:6px;">
                                        <i class="fas fa-times-circle me-1"></i> No File
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h5 class="fw-700 text-primary mb-1" style="line-height:1.3;">
                                <?= e($rec['lecture_title'] ?? 'Untitled Lecture') ?>
                            </h5>
                            <p class="text-muted small mb-3"><?= e($rec['course_title'] ?? '') ?></p>
                        </div>

                        <!-- Telemetry Row -->
                        <div class="px-4 mb-3">
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="p-2 rounded-2 text-center" style="background:var(--bg-tertiary);">
                                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">Duration</div>
                                        <div class="fw-700 text-info" style="font-size:14px;"><?= $durationFormatted ?></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-2 text-center" style="background:var(--bg-tertiary);">
                                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">File Size</div>
                                        <div class="fw-700 text-success" style="font-size:14px;"><?= $fileSizeFormatted ?></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-2 text-center" style="background:var(--bg-tertiary);">
                                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">Recorded</div>
                                        <div class="fw-700" style="font-size:12px;color:var(--text-secondary);"><?= date('M d', strtotime($rec['ended_at'] ?? 'now')) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inline Audio Player -->
                        <?php if ($hasFile): ?>
                            <div class="px-4 mb-3">
                                <audio controls class="w-100" style="height:40px;border-radius:8px;" preload="metadata">
                                    <source src="<?= $filePath ?>" type="audio/wav">
                                    <source src="<?= $filePath ?>" type="audio/x-wav">
                                    <source src="<?= $filePath ?>" type="audio/webm">
                                    <source src="<?= $filePath ?>" type="audio/ogg">
                                    <source src="<?= $filePath ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        <?php endif; ?>

                        <!-- Card Footer Actions -->
                        <div class="px-4 pb-4 d-flex gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-2 text-muted small flex-fill">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?= $recordedDate ?></span>
                                <span class="mx-1">•</span>
                                <i class="fas fa-user-tie"></i>
                                <span><?= e(($rec['lecturer_first_name'] ?? '') . ' ' . ($rec['lecturer_last_name'] ?? '')) ?></span>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($hasFile): ?>
                                    <a href="<?= url('/stream/recordings/' . $rec['id'] . '/download') ?>" class="btn-slms btn-sm btn-primary" title="Download">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                <?php endif; ?>
                                <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin', 'admin'])): ?>
                                <form method="POST" action="<?= url('/stream/recordings/' . $rec['id'] . '/delete') ?>" onsubmit="return confirm('Are you sure you want to delete this recording? This action cannot be undone.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-slms btn-sm btn-outline-slms" style="color:#ef4444;border-color:#ef4444;" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="slms-card p-5 text-center">
            <div class="mb-3" style="font-size:3rem;opacity:0.3;"><i class="fas fa-compact-disc"></i></div>
            <h4 class="text-muted">No recordings found</h4>
            <?php if ($userRole === 'student'): ?>
                <p class="text-muted small">Recordings will appear here once your lecturers end live class broadcasts for your enrolled courses.</p>
            <?php else: ?>
                <p class="text-muted small">Recordings will appear here once your live broadcasts have ended. Use the Broadcaster Studio to start a stream.</p>
            <?php endif; ?>
            <a href="<?= url('/lectures') ?>" class="btn-slms btn-primary mt-2">
                <i class="fas fa-chalkboard-teacher me-1"></i> View Lectures
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    .recording-card audio::-webkit-media-controls-panel {
        background: var(--bg-tertiary);
    }
    @media (max-width: 768px) {
        .recording-card .d-flex.gap-2.flex-wrap {
            flex-direction: column;
        }
    }
</style>
<?php $__view->endSection(); ?>
