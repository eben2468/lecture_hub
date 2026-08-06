<?php
/**
 * Nadics LectureHub — Features View
 * Premium showcase of system modules, bandwidth efficiency, and AI features.
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Features — Smart Lecture Management System',
    'page_description' => 'Explore the advanced features of Nadics LectureHub.',
]);
?>

<?php $__view->section('content'); ?>

<!-- ============================================================
     HERO HEADER
     ============================================================ -->
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-sparkles"></i> CUTTING-EDGE MODULES
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">System Features</h1>
        <p class="hero-subtitle">
            Discover the high-performance modules engineered specifically to overcome infrastructure, acoustic, and bandwidth constraints in African higher education.
        </p>
    </div>
</section>

<!-- ============================================================
     FEATURES SHOWCASE GRID
     ============================================================ -->
<section style="background: #020617; padding: 90px 0; position: relative;">
    <div class="container">
        <div class="row g-4">
            
            <!-- Feature 1: Live Audio Streaming -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <div class="showcase-icon-box" style="background: rgba(6, 182, 212, 0.15); color: #38BDF8; border: 1px solid rgba(6, 182, 212, 0.3);">
                        <i class="fas fa-broadcast-tower"></i>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="showcase-card-title mb-0">Live Audio Streaming</h3>
                        <span class="showcase-badge-tag" style="background: rgba(6, 182, 212, 0.15); color: #38BDF8;">64 kbps WebRTC</span>
                    </div>
                    <p class="showcase-card-text">
                        Low-latency, ultra-low-bandwidth audio streaming optimized for 2G/3G campus networks. Every student in packed lecture halls hears crystal-clear audio on their phone.
                    </p>
                    <div class="d-flex align-items-center gap-2" style="font-size: 0.85rem; color: #38BDF8; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Sub-50ms latency audio pipeline
                    </div>
                </div>
            </div>

            <!-- Feature 2: Smart QR Attendance -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <div class="showcase-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3);">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="showcase-card-title mb-0">QR Attendance Tracker</h3>
                        <span class="showcase-badge-tag" style="background: rgba(16, 185, 129, 0.15); color: #34D399;">Geofenced</span>
                    </div>
                    <p class="showcase-card-text">
                        Geofenced, dynamic QR code attendance engine. Generates time-sensitive tokens and validates student coordinates to stop proxy logging and save 15+ minutes per session.
                    </p>
                    <div class="d-flex align-items-center gap-2" style="font-size: 0.85rem; color: #34D399; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Instant register export to Excel/PDF
                    </div>
                </div>
            </div>

            <!-- Feature 3: AI Transcripts & Summaries -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <div class="showcase-icon-box" style="background: rgba(139, 92, 246, 0.15); color: #A78BFA; border: 1px solid rgba(139, 92, 246, 0.3);">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="showcase-card-title mb-0">AI-Powered Transcripts</h3>
                        <span class="showcase-badge-tag" style="background: rgba(139, 92, 246, 0.15); color: #A78BFA;">Speech-to-Text</span>
                    </div>
                    <p class="showcase-card-text">
                        Converts live broadcast audio into searchable text transcripts, key takeaways, and flashcard quizzes automatically after each broadcast concludes.
                    </p>
                    <div class="d-flex align-items-center gap-2" style="font-size: 0.85rem; color: #A78BFA; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Automated revision flashcard generator
                    </div>
                </div>
            </div>

            <!-- Feature 4: Assignments & Grading -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <div class="showcase-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #FBBF24; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="showcase-card-title mb-0">Assignments & Grading</h3>
                        <span class="showcase-badge-tag" style="background: rgba(245, 158, 11, 0.15); color: #FBBF24;">Centralized</span>
                    </div>
                    <p class="showcase-card-text">
                        Complete assignment distribution and submission hub. Features strict deadline enforcement, file attachment inspection, and lecturer grading interfaces.
                    </p>
                    <div class="d-flex align-items-center gap-2" style="font-size: 0.85rem; color: #FBBF24; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Automated late submission protection
                    </div>
                </div>
            </div>

            <!-- Feature 5: Resource Library -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <div class="showcase-icon-box" style="background: rgba(244, 63, 94, 0.15); color: #FB7185; border: 1px solid rgba(244, 63, 94, 0.3);">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="showcase-card-title mb-0">Course Resource Library</h3>
                        <span class="showcase-badge-tag" style="background: rgba(244, 63, 94, 0.15); color: #FB7185;">Secure Vault</span>
                    </div>
                    <p class="showcase-card-text">
                        Centralized digital library for lecture notes, slides, syllabus documents, and supplementary reading. Enforces strict enrollment authorization.
                    </p>
                    <div class="d-flex align-items-center gap-2" style="font-size: 0.85rem; color: #FB7185; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Role-scoped authorization security
                    </div>
                </div>
            </div>

            <!-- Feature 6: Academic Analytics -->
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <div class="showcase-icon-box" style="background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="showcase-card-title mb-0">Academic Analytics</h3>
                        <span class="showcase-badge-tag" style="background: rgba(59, 130, 246, 0.15); color: #60A5FA;">Real-Time</span>
                    </div>
                    <p class="showcase-card-text">
                        Comprehensive institutional dashboards providing attendance trends, lecture engagement metrics, and student performance insights for department heads.
                    </p>
                    <div class="d-flex align-items-center gap-2" style="font-size: 0.85rem; color: #60A5FA; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Multi-tenant university reports
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     CALL TO ACTION
     ============================================================ -->
<section style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); padding: 80px 0; border-top: 1px solid rgba(255,255,255,0.08);">
    <div class="container text-center">
        <h2 style="font-size: 2.25rem; font-weight: 800; color: white; margin-bottom: 15px;">Ready to Digitized Your Lecture Halls?</h2>
        <p style="font-size: 1.1rem; color: #94A3B8; max-width: 600px; margin: 0 auto 30px;">
            Join forward-thinking African universities providing seamless front-row lecture access for every enrolled student.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= url('/register') ?>" class="btn-slms btn-accent btn-lg" style="background: linear-gradient(135deg, #2563EB, #06B6D4); border: none; color: white; padding: 12px 32px; border-radius: 12px; font-weight: 700;">
                Get Started Free <i class="fas fa-arrow-right ms-2"></i>
            </a>
            <a href="<?= url('/login') ?>" class="btn-slms btn-outline-slms btn-lg" style="color: white; border: 1px solid rgba(255,255,255,0.2); padding: 12px 32px; border-radius: 12px; font-weight: 600;">
                Portal Login
            </a>
        </div>
    </div>
</section>

<?php $__view->endSection(); ?>
