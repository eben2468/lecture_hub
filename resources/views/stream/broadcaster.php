<?php
/**
 * Nadics LectureHub — Live Audio & Video Broadcaster Studio View
 * Enhanced with: webcam video, external mic selector, recording indicator, upload progress, responsive layout.
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Live Studio — ' . ($lecture['title'] ?? ''),
    'page_description' => 'WebRTC low-latency live audio & video streaming cockpit.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header flex-wrap gap-3">
        <div>
            <a href="<?= url('/lectures/' . $lecture['id']) ?>" class="text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Lecture Detail</a>
            <h1 class="page-title mt-2"><i class="fas fa-broadcast-tower text-danger me-2"></i> Live Broadcaster Studio (Audio & Video)</h1>
            <p class="page-subtitle"><?= e($lecture['course_code']) ?> — <?= e($lecture['title']) ?></p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Recording Timer -->
            <div id="recordingTimer" class="d-none align-items-center gap-2 px-3 py-2 rounded-3" style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);">
                <span class="recording-dot"></span>
                <span class="fw-700 text-danger" style="font-family:monospace;font-size:1.1rem;" id="timerDisplay">00:00:00</span>
                <span class="text-muted small ms-1">REC</span>
            </div>
            <div id="broadcasterStatusBadge">
                <?php if (($stream['status'] ?? '') === 'streaming'): ?>
                    <span class="badge-slms badge-danger pulse-badge" style="font-size:1rem;padding:8px 20px;">
                        <i class="fas fa-circle me-1"></i> BROADCASTING LIVE
                    </span>
                <?php else: ?>
                    <span class="badge-slms badge-warning" style="font-size:1rem;padding:8px 20px;">
                        <i class="fas fa-pause-circle me-1"></i> STUDIO IDLE
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Studio Cockpit -->
        <div class="col-lg-8">
            <div class="slms-card p-4 mb-4 text-center" style="background:linear-gradient(135deg,#0f172a,#1e293b);border:1px solid rgba(255,255,255,0.08);">

                <!-- Audio Device Selector -->
                <div class="mb-4 text-start">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-600"><i class="fas fa-microphone me-1"></i> Audio Input Device</label>
                            <select id="micSelector" class="form-control-slms" onchange="SLMSStream.switchMicrophone(this.value)">
                                <option value="">Loading devices...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-600"><i class="fas fa-sliders-h me-1"></i> Audio Quality</label>
                            <select id="audioQuality" class="form-control-slms">
                                <option value="32000">32 kbps (Low)</option>
                                <option value="64000">64 kbps (Standard)</option>
                                <option value="128000" selected>128 kbps (High)</option>
                                <option value="256000">256 kbps (Studio)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-600"><i class="fas fa-signal me-1"></i> Input Level</label>
                            <div class="mic-level-container p-2 rounded-2" style="background:rgba(0,0,0,0.3);height:38px;display:flex;align-items:center;">
                                <div id="micLevelBar" style="height:8px;width:0%;background:linear-gradient(90deg,#10b981,#f59e0b,#ef4444);border-radius:4px;transition:width 0.1s;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visualizer Canvas -->
                <div class="mb-4 position-relative">
                    <canvas id="audioVisualizer" width="700" height="150" style="width:100%;height:150px;background:#090d16;border-radius:var(--radius-lg);border:1px solid rgba(255,255,255,0.05);"></canvas>
                    <div id="visualizerOverlay" class="position-absolute top-50 start-50 translate-middle text-muted small">
                        <i class="fas fa-microphone-slash me-1"></i> Microphone Off — Press "Start Broadcast" to stream
                    </div>
                </div>

                <!-- Video Studio Stream Preview -->
                <div class="mb-4 position-relative rounded-3 overflow-hidden bg-black" style="height: 350px;">
                    <video id="broadcasterVideo" autoplay muted playsinline class="w-100 h-100 object-fit-cover" style="display:none;"></video>
                    <div id="videoPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted" style="background:#020617;">
                        <i class="fas fa-video-slash mb-2" style="font-size: 2.5rem;"></i>
                        <div class="small">Camera Stream Offline</div>
                    </div>
                </div>

                <!-- Telemetry Metrics Bar -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                            <div class="small text-muted mb-1">Live Listeners</div>
                            <h3 id="listenerCount" class="fw-700 text-info mb-0"><?= (int)($stream['listeners_count'] ?? 0) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                            <div class="small text-muted mb-1">Stream Bitrate</div>
                            <h3 id="bitrateDisplay" class="fw-700 text-success mb-0">— kbps</h3>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                            <div class="small text-muted mb-1">Audio Latency</div>
                            <h3 id="latencyDisplay" class="fw-700 text-warning mb-0">— ms</h3>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                            <div class="small text-muted mb-1">Recording Size</div>
                            <h3 id="recordingSizeDisplay" class="fw-700 text-accent mb-0">0 KB</h3>
                        </div>
                    </div>
                </div>

                <!-- Upload Progress Bar (hidden by default) -->
                <div id="uploadProgress" class="mb-4 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-600"><i class="fas fa-cloud-upload-alt me-1"></i> Uploading Recording...</span>
                        <span id="uploadPercent" class="fw-700 text-info small">0%</span>
                    </div>
                    <div class="progress" style="height:8px;background:rgba(255,255,255,0.05);border-radius:4px;">
                        <div id="uploadBar" class="progress-bar" role="progressbar" style="width:0%;background:linear-gradient(90deg,#3b82f6,#8b5cf6);border-radius:4px;transition:width 0.3s;"></div>
                    </div>
                </div>

                <!-- Studio Controls -->
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <button id="btnStartStream" class="btn-slms btn-danger-slms btn-lg" onclick="SLMSStream.startBroadcasting(<?= $lecture['id'] ?>)">
                        <i class="fas fa-play-circle me-1"></i> Start Broadcast
                    </button>
                    <button id="btnMuteMic" class="btn-slms btn-ghost btn-lg" onclick="SLMSStream.toggleMute()" disabled>
                        <i class="fas fa-microphone me-1"></i> Mute Mic
                    </button>
                    <button id="btnToggleVideo" class="btn-slms btn-ghost btn-lg" onclick="SLMSStream.toggleVideo()" disabled>
                        <i class="fas fa-video me-1"></i> Toggle Video
                    </button>
                    <button id="btnScreenShare" class="btn-slms btn-ghost btn-lg" onclick="SLMSStream.toggleScreenShare(<?= $lecture['id'] ?>)" disabled>
                        <i class="fas fa-desktop me-1"></i> Share Screen
                    </button>
                    <button id="btnStopStream" class="btn-slms btn-outline-slms btn-lg" onclick="SLMSStream.stopBroadcasting(<?= $lecture['id'] ?>)" disabled>
                        <i class="fas fa-stop-circle me-1"></i> End Broadcast
                    </button>
                </div>
            </div>
        </div>

        <!-- Live Q&A & Chat Moderation Sidebar -->
        <div class="col-lg-4">
            <div class="slms-card p-4 h-100 d-flex flex-column">
                <h5 class="fw-700 mb-3"><i class="fas fa-comments text-accent me-2"></i> Audience Q&A</h5>

                <!-- Chat Feed -->
                <div id="chatFeed" class="flex-fill mb-3 p-3 rounded-3" style="background:var(--bg-tertiary);max-height:350px;overflow-y:auto;font-size:13px;">
                    <div class="text-center text-muted py-4">No live messages yet.</div>
                </div>

                <!-- Send Broadcast Message Form -->
                <form id="chatForm" onsubmit="SLMSStream.sendChatMessage(event, <?= $lecture['id'] ?>)">
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control-slms form-control-sm" placeholder="Announcement to class..." required>
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
    .recording-dot {
        width: 12px; height: 12px;
        background: #ef4444;
        border-radius: 50%;
        display: inline-block;
        animation: recPulse 1s ease-in-out infinite;
    }
    @keyframes recPulse {
        0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(239,68,68,0.6); }
        50% { opacity: 0.4; box-shadow: 0 0 0 8px rgba(239,68,68,0); }
    }
    @media (max-width: 768px) {
        .d-flex.justify-content-center.gap-3.flex-wrap .btn-slms {
            flex: 1 1 45%;
            text-align: center;
        }
        .page-header { flex-direction: column; align-items: flex-start !important; }
    }
</style>

<?php $__view->endSection(); ?>

<?php $__view->section('extra_js'); ?>
<script src="<?= asset('js/stream.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        SLMSStream.initBroadcaster(<?= $lecture['id'] ?>);
    });
</script>
<?php $__view->endSection(); ?>
