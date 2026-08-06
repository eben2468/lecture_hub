<?php
/**
 * Nadics LectureHub — Institutional Pricing View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Institutional Pricing — Smart Lecture Management System',
    'page_description' => 'Flexible deployment plans for departments, faculties, and university institutions.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-tags"></i> TRANSPARENT INSTITUTIONAL PLANS
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Flexible Pricing for Every Campus</h1>
        <p class="hero-subtitle">
            Scale seamlessly from individual academic departments to full university-wide deployments serving 500,000+ students.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 90px 0;">
    <div class="container">
        <div class="row g-4 justify-content-center">
            
            <!-- Tier 1: Department Starter -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <div class="mb-3">
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-600">DEPARTMENT STARTER</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-3">
                        <h2 style="font-size: 2.5rem; font-weight: 800; color: white;">Free</h2>
                        <span style="color: #94A3B8;">/ 30-Day Trial</span>
                    </div>
                    <p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 25px;">Ideal for piloting live audio streaming and QR attendance in a single academic department.</p>
                    
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-4" style="color: #CBD5E1; font-size: 0.95rem;">
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Up to 5 Active Lecturers</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> 1,000 Enrolled Students</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> 64kbps WebRTC Streaming</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Dynamic QR Attendance</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Basic Assignment Grading</li>
                    </ul>

                    <a href="<?= url('/register') ?>" class="btn-slms btn-outline-slms w-100 py-3 text-center" style="color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-weight: 600; text-decoration: none; display: block;">
                        Start Pilot Trial
                    </a>
                </div>
            </div>

            <!-- Tier 2: Faculty Standard -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card" style="border-color: rgba(6, 182, 212, 0.5); box-shadow: 0 0 35px rgba(6, 182, 212, 0.2);">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <span class="badge bg-cyan text-dark px-3 py-2 rounded-pill fw-700" style="background: #06B6D4 !important; color: #020617 !important;">MOST POPULAR</span>
                        <span class="text-accent small"><i class="fas fa-star me-1"></i> Faculty Choice</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-3">
                        <h2 style="font-size: 2.5rem; font-weight: 800; color: white;">$499</h2>
                        <span style="color: #94A3B8;">/ month per faculty</span>
                    </div>
                    <p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 25px;">Designed for full faculty deployments across multiple departments and degree programmes.</p>
                    
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-4" style="color: #CBD5E1; font-size: 0.95rem;">
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Unlimited Lecturers & TAs</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Up to 25,000 Students</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Priority Low-Latency Audio</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> AI Whisper Speech-to-Text</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Automated PDF/Excel Reports</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Priority 24/7 Tech Support</li>
                    </ul>

                    <a href="<?= url('/register') ?>" class="btn-slms btn-accent w-100 py-3 text-center" style="background: linear-gradient(135deg, #2563EB, #06B6D4); border: none; color: white; border-radius: 12px; font-weight: 700; text-decoration: none; display: block;">
                        Deploy Faculty Plan <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Tier 3: University Enterprise -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <div class="mb-3">
                        <span class="badge bg-purple px-3 py-2 rounded-pill fw-600" style="background: rgba(139, 92, 246, 0.2); color: #A78BFA; border: 1px solid rgba(139, 92, 246, 0.3);">UNIVERSITY ENTERPRISE</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-3">
                        <h2 style="font-size: 2.5rem; font-weight: 800; color: white;">Custom</h2>
                        <span style="color: #94A3B8;">/ institutional contract</span>
                    </div>
                    <p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 25px;">Enterprise-grade solution for major public and private universities serving 500,000+ students.</p>
                    
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-4" style="color: #CBD5E1; font-size: 0.95rem;">
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Unlimited Students & Courses</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Dedicated On-Premise Audio Edge Nodes</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Custom ERP & Student Portal Integration</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Custom SLA & Disaster Recovery</li>
                        <li><i class="fas fa-check-circle text-accent me-2"></i> Dedicated University Account Manager</li>
                    </ul>

                    <a href="<?= url('/contact') ?>" class="btn-slms btn-outline-slms w-100 py-3 text-center" style="color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-weight: 600; text-decoration: none; display: block;">
                        Contact Enterprise Team
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
