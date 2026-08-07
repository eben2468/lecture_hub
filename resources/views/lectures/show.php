<?php
/**
 * Nadics LectureHub — Lecture Detail View
 */
$__view->layout('layouts.app', [
    'page_title'       => $lecture['title'] ?? 'Lecture Detail',
    'page_description' => ($lecture['course_code'] ?? '') . ' — Lecture Detail',
]);

$auth     = \Core\Auth::getInstance();
$userRole = $auth->role();

$statusClass = match($lecture['status']) {
    'live'      => 'badge-danger',
    'completed' => 'badge-success',
    'cancelled' => 'badge-warning',
    default     => 'badge-info',
};
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <a href="<?= url('/lectures') ?>" class="text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Lectures</a>
            <h1 class="page-title mt-2"><?= e($lecture['title']) ?></h1>
            <p class="page-subtitle"><?= e($lecture['course_code']) ?> — <?= e($lecture['course_title']) ?></p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge-slms <?= $statusClass ?>" style="font-size:1rem;padding:8px 20px;">
                <?= ucfirst($lecture['status']) ?>
            </span>
            <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin']) && ((int)$lecture['lecturer_id'] === (int)$auth->id() || in_array($userRole, ['university_admin', 'super_admin']))): ?>
                <div class="d-flex gap-2">
                    <a href="<?= url('/lectures/' . $lecture['id'] . '/edit') ?>" class="btn-slms btn-sm btn-outline-slms" title="Edit Lecture">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <form method="POST" action="<?= url('/lectures/' . $lecture['id'] . '/delete') ?>" onsubmit="return confirm('Are you sure you want to delete this lecture? This will permanently delete all associated recordings, transcripts, and materials.');" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-slms btn-sm btn-danger-slms">
                            <i class="fas fa-trash-alt me-1"></i> Delete
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Lecture Info Panel -->
        <div class="col-lg-8">
            <div class="slms-card p-4 mb-4">
                <h5 class="fw-700 mb-3"><i class="fas fa-info-circle text-accent me-2"></i> Lecture Information</h5>

                <?php if ($lecture['description']): ?>
                    <p class="mb-4" style="line-height:1.8;"><?= nl2br(e($lecture['description'])) ?></p>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:var(--bg-tertiary);">
                            <div class="small text-muted mb-1">Scheduled Date</div>
                            <strong><?= date('l, M d, Y', strtotime($lecture['scheduled_start'])) ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background:var(--bg-tertiary);">
                            <div class="small text-muted mb-1">Start Time</div>
                            <strong><?= date('h:i A', strtotime($lecture['scheduled_start'])) ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background:var(--bg-tertiary);">
                            <div class="small text-muted mb-1">End Time</div>
                            <strong><?= date('h:i A', strtotime($lecture['scheduled_end'])) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:var(--bg-tertiary);">
                            <div class="small text-muted mb-1">Lecturer</div>
                            <strong><i class="fas fa-user-tie me-1"></i> <?= e($lecture['lecturer_first_name'] . ' ' . $lecture['lecturer_last_name']) ?></strong>
                        </div>
                    </div>
                    <?php if ($lecture['actual_start']): ?>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background:var(--bg-tertiary);">
                            <div class="small text-muted mb-1">Actual Start</div>
                            <strong><?= date('h:i A', strtotime($lecture['actual_start'])) ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($lecture['actual_end']): ?>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background:var(--bg-tertiary);">
                            <div class="small text-muted mb-1">Actual End</div>
                            <strong><?= date('h:i A', strtotime($lecture['actual_end'])) ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Audio & Video Stream Panel -->
            <?php if (in_array($lecture['status'], ['scheduled', 'live']) || $stream): ?>
            <div class="slms-card p-4 mb-4">
                <h5 class="fw-700 mb-3"><i class="fas fa-video text-danger me-2"></i> Live Studio Broadcast (Audio & Video)</h5>
                <?php if ($lecture['status'] === 'scheduled'): ?>
                    <div class="p-4 rounded-3 text-center" style="background:var(--bg-tertiary); border: 1px dashed rgba(255,255,255,0.08);">
                        <div class="mb-3">
                            <span class="badge-slms badge-info" style="font-size:0.9rem;">
                                <i class="fas fa-clock me-1"></i> UPCOMING BROADCAST
                            </span>
                        </div>
                        
                        <?php if (in_array($userRole, ['lecturer', 'super_admin', 'university_admin'])): ?>
                            <p class="text-muted mb-3">Launch the live studio to begin streaming live audio & video to enrolled students.</p>
                            <a href="<?= url('/stream/broadcaster/' . $lecture['id']) ?>" class="btn-slms btn-danger-slms btn-lg w-100 py-3 d-inline-block" style="text-align:center;">
                                <i class="fas fa-video me-1"></i> Launch Broadcaster Studio
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-0">This lecture is scheduled. Waiting for the lecturer to start the live video & audio stream.</p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($lecture['status'] === 'live'): ?>
                    <div class="p-4 rounded-3 text-center" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border:1px solid rgba(255,255,255,0.08);">
                        <div class="mb-3">
                            <span class="badge-slms badge-danger" style="font-size:0.9rem;animation:pulse 1.5s infinite;">
                                <i class="fas fa-broadcast-tower me-1"></i> LIVE AUDIO + VIDEO STREAMING
                            </span>
                        </div>
                        <p class="text-muted mb-3">Live video & audio studio stream is active. Connect now to watch and listen.</p>
                        
                        <?php if (in_array($userRole, ['lecturer', 'super_admin', 'university_admin'])): ?>
                            <a href="<?= url('/stream/broadcaster/' . $lecture['id']) ?>" class="btn-slms btn-danger-slms btn-lg w-100 py-3 mb-3 d-inline-block" style="text-align:center;">
                                <i class="fas fa-video me-1"></i> Launch Broadcaster Studio
                            </a>
                        <?php else: ?>
                            <a href="<?= url('/stream/listener/' . $lecture['id']) ?>" class="btn-slms btn-primary btn-lg w-100 py-3 mb-3 d-inline-block" style="text-align:center; background:#10B981; border-color:#10B981;">
                                <i class="fas fa-play-circle me-1"></i> Tune In Live (Audio + Video)
                            </a>
                        <?php endif; ?>

                        <?php if ($stream): ?>
                            <div class="small text-muted">
                                <span class="me-3"><i class="fas fa-signal me-1"></i> <?= (int)($stream['quality_kbps'] ?? 64) ?> kbps</span>
                                <span><i class="fas fa-users me-1"></i> <?= (int)($stream['listeners_count'] ?? 0) ?> listeners</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($stream && (!empty($stream['audio_file_path']) || $lecture['status'] === 'completed')): ?>
                    <div class="p-4 rounded-3" style="background:var(--bg-tertiary);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-700 mb-1"><i class="fas fa-play-circle text-success me-1"></i> Lecture Recording Available</h6>
                                <div class="text-muted small">
                                    <?php
                                        $dur = (int)($stream['duration_seconds'] ?? 0);
                                        $durFormatted = sprintf('%02d:%02d:%02d', floor($dur / 3600), floor(($dur % 3600) / 60), $dur % 60);
                                        $fSize = (int)($stream['recording_file_size'] ?? 0);
                                        $fSizeFormatted = $fSize > 0 ? number_format($fSize / (1024 * 1024), 2) . ' MB' : '—';
                                    ?>
                                    <span class="me-3"><i class="fas fa-clock me-1"></i> Duration: <?= $durFormatted ?></span>
                                    <span><i class="fas fa-hdd me-1"></i> Size: <?= $fSizeFormatted ?></span>
                                </div>
                            </div>
                            <a href="<?= url('/stream/recordings/' . $stream['id'] . '/download') ?>" class="btn-slms btn-sm btn-primary">
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                        </div>
                        <audio controls class="w-100" style="border-radius:8px;" preload="metadata">
                            <source src="<?= url($stream['audio_file_path']) ?>" type="audio/wav">
                            <source src="<?= url($stream['audio_file_path']) ?>" type="audio/x-wav">
                            <source src="<?= url($stream['audio_file_path']) ?>" type="audio/webm">
                            <source src="<?= url($stream['audio_file_path']) ?>" type="audio/ogg">
                            <source src="<?= url($stream['audio_file_path']) ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Transcript Panel -->
            <?php if ($transcript && $transcript['status'] === 'completed'): ?>
            <div class="slms-card p-4 mb-4">
                <h5 class="fw-700 mb-3"><i class="fas fa-file-alt text-info me-2"></i> AI Transcript</h5>
                <?php if ($transcript['summary']): ?>
                    <div class="p-3 rounded-3 mb-3" style="background:var(--bg-tertiary);">
                        <div class="small text-muted mb-1">Summary</div>
                        <p class="mb-0"><?= nl2br(e($transcript['summary'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($transcript['full_text']): ?>
                    <div class="p-3 rounded-3" style="background:var(--bg-tertiary);max-height:400px;overflow-y:auto;">
                        <div class="small text-muted mb-1">Full Transcript (<?= number_format($transcript['word_count'] ?? 0) ?> words)</div>
                        <p class="mb-0 small" style="line-height:1.8;"><?= nl2br(e($transcript['full_text'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Lecturer Controls -->
            <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
            <div class="slms-card p-4 mb-4">
                <h5 class="fw-700 mb-3"><i class="fas fa-sliders-h text-primary me-2"></i> Lecture Controls</h5>
                <?php if ($lecture['status'] === 'scheduled'): ?>
                    <a href="<?= url('/stream/broadcaster/' . $lecture['id']) ?>" class="btn-slms btn-danger-slms w-100 mb-2 text-center d-inline-block" style="text-align:center;">
                        <i class="fas fa-broadcast-tower me-1"></i> Launch Live Studio
                    </a>
                    <form method="POST" action="<?= url('/lectures/' . $lecture['id'] . '/status') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn-slms btn-warning w-100">
                            <i class="fas fa-times-circle me-1"></i> Cancel Lecture
                        </button>
                    </form>
                <?php elseif ($lecture['status'] === 'live'): ?>
                    <a href="<?= url('/stream/broadcaster/' . $lecture['id']) ?>" class="btn-slms btn-danger-slms w-100 mb-2 text-center d-inline-block" style="text-align:center;">
                        <i class="fas fa-broadcast-tower me-1"></i> Enter Live Studio
                    </a>
                    <form method="POST" action="<?= url('/lectures/' . $lecture['id'] . '/status') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn-slms btn-success-slms w-100">
                            <i class="fas fa-check-circle me-1"></i> End Lecture
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Associated Materials -->
            <div class="slms-card p-4">
                <h5 class="fw-700 mb-3"><i class="fas fa-paperclip text-accent me-2"></i> Lecture Materials</h5>
                <?php if (!empty($materials)): ?>
                    <?php foreach ($materials as $mat): ?>
                        <div class="d-flex align-items-center gap-3 p-2 rounded-2 mb-2" style="background:var(--bg-tertiary);">
                            <i class="fas fa-file-pdf text-danger" style="font-size:1.2rem;"></i>
                            <div class="flex-fill">
                                <div class="fw-600 small"><?= e($mat['title']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= number_format($mat['file_size'] / 1024) ?> KB</div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="<?= url('/materials/' . $mat['id'] . '/download') ?>" class="btn-slms btn-sm btn-outline-slms" title="Download Material">
                                    <i class="fas fa-download"></i>
                                </a>
                                <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
                                    <form method="POST" action="<?= url('/materials/' . $mat['id'] . '/delete') ?>" onsubmit="return confirm('Delete this material?');" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-slms btn-sm btn-danger-slms" title="Delete Material">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small text-center mb-0">No materials attached to this lecture.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
