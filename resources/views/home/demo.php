<?php
/**
 * Nadics LectureHub — Interactive Live Demo View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Interactive Live Demo — Smart Lecture Management System',
    'page_description' => 'Test live WebRTC audio streaming, listener equalizer, and QR attendance simulation.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-play-circle"></i> INTERACTIVE SIMULATOR
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Experience SLMS Live Demo</h1>
        <p class="hero-subtitle">
            Simulate a real-time lecture broadcast, test student audio reception, and experience the geofenced QR attendance engine.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0;">
    <div class="container">
        <div class="row g-5 align-items-center">
            
            <!-- Live Audio Stream Simulator -->
            <div class="col-lg-6">
                <div class="slms-showcase-card">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="spinner-grow spinner-grow-sm text-danger"></span>
                            <span style="font-size: 0.85rem; color: #EF4444; font-weight: 700; letter-spacing: 0.5px;">LIVE BROADCAST DEMO</span>
                        </div>
                        <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill" id="demo-status">64 kbps WebRTC</span>
                    </div>

                    <div style="background: #0F172A; border-radius: 16px; padding: 24px; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.05);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span style="font-size: 0.85rem; color: #38BDF8; font-weight: 600;">CSC 301 — Data Structures</span>
                            <span class="badge bg-primary">Hall A (Cap: 500)</span>
                        </div>
                        <h4 style="color: white; font-weight: 700; margin-bottom: 4px;">Prof. O. Adebayo</h4>
                        <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 15px;">Topic: Binary Search Trees & Memory Optimization</p>

                        <!-- Equalizer animation -->
                        <div class="d-flex align-items-end gap-1 mb-3" style="height: 50px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 8px;" id="demo-eq">
                            <div class="bg-primary flex-fill rounded" style="height: 40%;"></div>
                            <div class="bg-accent flex-fill rounded" style="height: 75%;"></div>
                            <div class="bg-primary flex-fill rounded" style="height: 60%;"></div>
                            <div class="bg-info flex-fill rounded" style="height: 90%;"></div>
                            <div class="bg-primary flex-fill rounded" style="height: 35%;"></div>
                            <div class="bg-accent flex-fill rounded" style="height: 80%;"></div>
                            <div class="bg-primary flex-fill rounded" style="height: 50%;"></div>
                            <div class="bg-info flex-fill rounded" style="height: 95%;"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center" style="font-size: 0.85rem; color: #94A3B8;">
                            <span><i class="fas fa-users text-accent me-1"></i> <strong style="color: white;" id="demo-listener-count">342</strong> Active Listeners</span>
                            <span><i class="fas fa-bolt text-warning me-1"></i> Latency: <strong>38ms</strong></span>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button class="btn-slms btn-primary flex-fill py-3" onclick="toggleDemoAudio()" id="demo-audio-btn" style="background: linear-gradient(135deg, #2563EB, #06B6D4); border: none; color: white; border-radius: 12px; font-weight: 700;">
                            <i class="fas fa-volume-up me-2"></i> Listen Live Stream
                        </button>
                    </div>
                </div>
            </div>

            <!-- QR Attendance Scanner Demo -->
            <div class="col-lg-6">
                <div class="slms-showcase-card">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                        <span style="font-size: 0.85rem; color: #34D399; font-weight: 700; letter-spacing: 0.5px;"><i class="fas fa-qrcode me-2"></i> SMART QR DEMO</span>
                        <span class="badge bg-success">Geofence Validated</span>
                    </div>

                    <div class="text-center p-4 mb-4" style="background: #0F172A; border-radius: 16px; border: 1px dashed rgba(56, 189, 248, 0.3);">
                        <div class="mb-3">
                            <i class="fas fa-qrcode" style="font-size: 5rem; color: #38BDF8;"></i>
                        </div>
                        <h5 style="color: white; font-weight: 700; margin-bottom: 6px;">Dynamic QR Refresh Token</h5>
                        <p style="color: #94A3B8; font-size: 0.85rem; margin-bottom: 12px;">Token refreshes automatically every 10 seconds to prevent proxy attendance.</p>
                        <div class="badge bg-dark border border-secondary px-3 py-2" style="font-family: monospace; font-size: 1rem; color: #34D399;" id="demo-qr-token">
                            SLMS-2026-X89F-772B
                        </div>
                    </div>

                    <button class="btn-slms btn-outline-slms w-100 py-3" onclick="simulateAttendance()" style="color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-check-circle text-success me-2"></i> Simulate Student Attendance Log
                    </button>
                    
                    <div id="demo-log-output" class="mt-3 p-3 rounded d-none" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34D399; font-size: 0.85rem;">
                        <i class="fas fa-check-circle me-1"></i> Attendance verified! Student ID: <strong>210407001</strong> logged at geofenced GPS coordinates (6.5244° N, 3.3792° E).
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
let isListening = false;
let audioInterval;

function toggleDemoAudio() {
    isListening = !isListening;
    const btn = document.getElementById('demo-audio-btn');
    const status = document.getElementById('demo-status');
    
    if (isListening) {
        btn.innerHTML = '<i class="fas fa-pause me-2"></i> Mute Live Audio';
        btn.style.background = '#EF4444';
        status.innerText = 'Streaming Active (64kbps)';
        status.className = 'badge bg-danger px-3 py-1 rounded-pill';
        
        audioInterval = setInterval(() => {
            const count = document.getElementById('demo-listener-count');
            count.innerText = Math.floor(340 + Math.random() * 15);
        }, 2000);
    } else {
        btn.innerHTML = '<i class="fas fa-volume-up me-2"></i> Listen Live Stream';
        btn.style.background = 'linear-gradient(135deg, #2563EB, #06B6D4)';
        status.innerText = '64 kbps WebRTC';
        status.className = 'badge bg-success-subtle text-success px-3 py-1 rounded-pill';
        clearInterval(audioInterval);
    }
}

function simulateAttendance() {
    const output = document.getElementById('demo-log-output');
    output.classList.remove('d-none');
    setTimeout(() => {
        const token = document.getElementById('demo-qr-token');
        token.innerText = 'SLMS-2026-' + Math.random().toString(36).substring(2, 6).toUpperCase() + '-' + Math.random().toString(36).substring(2, 6).toUpperCase();
    }, 1000);
}
</script>
<?php $__view->endSection(); ?>
