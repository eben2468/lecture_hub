<?php
/**
 * Nadics LectureHub — Developer API & Webhooks View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Developer API & Webhooks — Smart Lecture Management System',
    'page_description' => 'Integrate Nadics LectureHub REST API endpoints with university ERPs and student portals.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-code"></i> DEVELOPER PLATFORM
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Developer API & Integration Webhooks</h1>
        <p class="hero-subtitle">
            Seamlessly connect SLMS to institutional student portals, RFID turnstiles, and university ERP database systems.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0;">
    <div class="container">
        <div class="row g-4">
            
            <div class="col-lg-6">
                <div class="slms-showcase-card">
                    <h3 style="color: white; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-terminal text-cyan"></i> REST API Endpoints (v1)
                    </h3>
                    <div style="background: #0F172A; border-radius: 12px; padding: 20px; font-family: monospace; font-size: 0.85rem; color: #E2E8F0; border: 1px solid rgba(255,255,255,0.08);">
                        <p class="mb-2"><span class="badge bg-success">GET</span> <span style="color: #38BDF8;">/api/v1/health</span> — System Telemetry</p>
                        <p class="mb-2"><span class="badge bg-primary">POST</span> <span style="color: #38BDF8;">/api/v1/lectures</span> — Create Scheduled Lecture</p>
                        <p class="mb-2"><span class="badge bg-warning text-dark">GET</span> <span style="color: #38BDF8;">/api/v1/attendance/records</span> — Fetch Geofenced Attendance</p>
                        <p class="mb-0"><span class="badge bg-info">POST</span> <span style="color: #38BDF8;">/api/v1/transcripts/webhook</span> — AI Speech Callback</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="slms-showcase-card">
                    <h3 style="color: white; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-key text-emerald"></i> API Token Authentication
                    </h3>
                    <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 20px;">
                        Authenticate requests using Bearer Tokens generated in the University Admin portal. Rate-limited to 1,000 requests per minute with HTTPS TLS 1.3 encryption.
                    </p>
                    <div style="background: #0F172A; border-radius: 12px; padding: 15px; font-family: monospace; font-size: 0.85rem; color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3);">
                        Authorization: Bearer slms_live_99f38a7c2101e4b9...
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
