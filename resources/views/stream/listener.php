<?php
/**
 * Nadics LectureHub — Student Live Audio & Video Stream Viewer
 * Enhanced with: live video player, equalizer canvas, connection status indicator, responsive layout.
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Live Stream — ' . ($lecture['title'] ?? ''),
    'page_description' => 'Live classroom audio & video stream.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header flex-wrap gap-3">
        <div>
            <a href="<?= url('/lectures/' . $lecture['id']) ?>" class="text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Lecture Detail</a>
            <h1 class="page-title mt-2"><i class="fas fa-video text-info me-2"></i> Live Classroom Stream (Audio & Video)</h1>
            <p class="page-subtitle"><?= e($lecture['course_code']) ?> — <?= e($lecture['title']) ?></p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Connection Status Indicator -->
            <div id="connectionStatus" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                <span id="connectionDot" class="connection-dot disconnected"></span>
                <span id="connectionText" class="small fw-600" style="color:var(--text-secondary);">Disconnected</span>
            </div>
            <div id="listenerStatusBadge">
                <?php if (($stream['status'] ?? '') === 'streaming'): ?>
                    <span class="badge-slms badge-danger pulse-badge" style="font-size:1rem;padding:8px 20px;">
                        <i class="fas fa-signal me-1"></i> LIVE STREAM ACTIVE
                    </span>
                <?php else: ?>
                    <span class="badge-slms badge-warning" style="font-size:1rem;padding:8px 20px;">
                        <i class="fas fa-clock me-1"></i> WAITING FOR LECTURER
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Audio & Video Stream Player -->
        <div class="col-lg-8">
            <div class="slms-card p-4 mb-4 text-center" style="background:linear-gradient(135deg,#0f172a,#1e293b);border:1px solid rgba(255,255,255,0.08);">
                <!-- Live Video Stream Viewer -->
                <div class="mb-4 position-relative rounded-3 overflow-hidden bg-black" style="min-height: 380px;">
                    <video id="listenerVideo" autoplay playsinline class="w-100 h-100 object-fit-cover" style="display:none; min-height: 380px;"></video>
                    <div id="videoPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted" style="background:#020617; min-height: 380px;">
                        <i class="fas fa-video-slash mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                        <div class="fw-600 mb-1">Live Video Stream Offline</div>
                        <div class="small" style="opacity: 0.6;">Press "Watch Live" to connect to the lecturer's live camera & audio feed</div>
                    </div>
                    <!-- Live overlay badge -->
                    <div id="liveOverlayBadge" class="position-absolute top-0 start-0 m-3 d-none">
                        <span class="badge bg-danger px-3 py-2" style="font-size: 0.8rem; animation: pulse 1.5s infinite;">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> LIVE
                        </span>
                    </div>
                    <!-- Lecturer name overlay -->
                    <div id="lecturerOverlay" class="position-absolute bottom-0 start-0 end-0 p-3 d-none" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                        <div class="text-white fw-600"><i class="fas fa-user-tie me-1"></i> <?= e($lecture['lecturer_first_name'] . ' ' . $lecture['lecturer_last_name']) ?></div>
                        <div class="text-white-50 small"><?= e($lecture['course_code']) ?> — <?= e($lecture['title']) ?></div>
                    </div>
                </div>

                <!-- Audio Equalizer Visualizer -->
                <div class="mb-4 position-relative">
                    <canvas id="listenerEqualizer" width="700" height="80" style="width:100%;height:80px;background:#090d16;border-radius:var(--radius-lg);border:1px solid rgba(255,255,255,0.05);"></canvas>
                </div>

                <div class="mb-4">
                    <h4 class="fw-700 text-primary mb-1"><?= e($lecture['title']) ?></h4>
                    <p class="text-muted small mb-0">Lecturer: <?= e($lecture['lecturer_first_name'] . ' ' . $lecture['lecturer_last_name']) ?></p>
                </div>

                <!-- Player Controls -->
                <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                    <button id="btnListenPlay" class="btn-slms btn-primary btn-lg px-5" onclick="SLMSStream.toggleListen(<?= $lecture['id'] ?>)">
                        <i class="fas fa-play me-1"></i> Watch Live
                    </button>
                    <div class="d-flex align-items-center gap-2" style="min-width:150px;max-width:200px;">
                        <i class="fas fa-volume-up text-muted"></i>
                        <input type="range" id="volumeControl" class="form-range" min="0" max="1" step="0.05" value="0.8" onchange="SLMSStream.setVolume(this.value)">
                    </div>
                </div>

                <!-- Stream Info Bar -->
                <div class="mt-4 d-flex justify-content-center gap-4 text-muted small">
                    <span><i class="fas fa-users me-1"></i> <span id="listenerCount"><?= (int)($stream['listeners_count'] ?? 0) ?></span> listeners</span>
                    <span><i class="fas fa-signal me-1"></i> <span id="streamQuality"><?= (int)($stream['quality_kbps'] ?? 64) ?></span> kbps</span>
                    <span><i class="fas fa-wifi me-1"></i> <span id="streamLatency">—</span> ms latency</span>
                </div>
            </div>
        </div>

        <!-- Live Q&A Chat -->
        <div class="col-lg-4">
            <div class="slms-card p-4 h-100 d-flex flex-column">
                <h5 class="fw-700 mb-3"><i class="fas fa-question-circle text-accent me-2"></i> Ask a Question</h5>

                <!-- Chat Feed -->
                <div id="chatFeed" class="flex-fill mb-3 p-3 rounded-3" style="background:var(--bg-tertiary);max-height:350px;overflow-y:auto;font-size:13px;">
                    <div class="text-center text-muted py-4">No questions asked yet.</div>
                </div>

                <!-- Send Question Form -->
                <form id="chatForm" onsubmit="SLMSStream.sendChatMessage(event, <?= $lecture['id'] ?>, 1)">
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control-slms form-control-sm" placeholder="Type your question to lecturer..." required>
                        <button type="submit" class="btn-slms btn-sm btn-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .connection-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        transition: background 0.3s;
    }
    .connection-dot.connected {
        background: #10b981;
        box-shadow: 0 0 6px rgba(16,185,129,0.5);
    }
    .connection-dot.reconnecting {
        background: #f59e0b;
        animation: connPulse 1s ease-in-out infinite;
    }
    .connection-dot.disconnected {
        background: #64748b;
    }
    @keyframes connPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start !important; }
        .d-flex.justify-content-center.gap-3.flex-wrap {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<?php $__view->endSection(); ?>

<?php $__view->section('extra_js'); ?>
<script src="<?= asset('js/stream.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        SLMSStream.initListener(<?= $lecture['id'] ?>);
    });
</script>
<?php $__view->endSection(); ?>
