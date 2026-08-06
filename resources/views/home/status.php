<?php
/**
 * Nadics LectureHub — System Operational Status View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'System Operational Status — Nadics LectureHub',
    'page_description' => 'Real-time telemetry and service status across WebRTC audio edge nodes and database services.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-signal"></i> REAL-TIME TELEMETRY
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">System Operational Status</h1>
        <p class="hero-subtitle">
            All systems operational. Telemetry monitored 24/7 across campus audio edge nodes and database clusters.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0;">
    <div class="container">
        <div class="slms-showcase-card mb-4" style="border-color: rgba(16, 185, 129, 0.4);">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 16px; height: 16px; border-radius: 50%; background: #10B981; box-shadow: 0 0 15px #10B981;"></div>
                <h3 style="color: white; font-weight: 700; margin: 0; font-size: 1.25rem;">All Systems Operational — 99.99% Uptime SLA</h3>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="slms-showcase-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 style="color: white; font-weight: 600; font-size: 1.1rem;">WebRTC Audio Edge Nodes</h4>
                        <span class="badge bg-success">100% Operational</span>
                    </div>
                    <p style="color: #94A3B8; font-size: 0.85rem; margin: 0;">Sub-50ms audio latency streaming across African campus POPs.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="slms-showcase-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 style="color: white; font-weight: 600; font-size: 1.1rem;">QR Geofence Attendance Engine</h4>
                        <span class="badge bg-success">100% Operational</span>
                    </div>
                    <p style="color: #94A3B8; font-size: 0.85rem; margin: 0;">Time-sensitive token generator & GPS verification service active.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="slms-showcase-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 style="color: white; font-weight: 600; font-size: 1.1rem;">AI Whisper Transcription Pipeline</h4>
                        <span class="badge bg-success">100% Operational</span>
                    </div>
                    <p style="color: #94A3B8; font-size: 0.85rem; margin: 0;">Automated speech-to-text transcript processing queue.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="slms-showcase-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 style="color: white; font-weight: 600; font-size: 1.1rem;">REST API & Webhooks Gateway</h4>
                        <span class="badge bg-success">100% Operational</span>
                    </div>
                    <p style="color: #94A3B8; font-size: 0.85rem; margin: 0;">Rate-limited SSL/TLS HTTPS endpoints.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
