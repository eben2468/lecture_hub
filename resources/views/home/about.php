<?php
/**
 * Nadics LectureHub — About View
 * Learn about the mission behind the Smart Lecture Management System.
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'About Us — Smart Lecture Management System',
    'page_description' => 'Learn about Nadics Solutions and the vision behind SLMS.',
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
                <i class="fas fa-university"></i> OUR MISSION & VISION
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">About Nadics LectureHub</h1>
        <p class="hero-subtitle">
            Empowering students and faculty across African higher education institutions with real-time audio streaming, dynamic attendance automation, and AI transcription.
        </p>
    </div>
</section>

<!-- ============================================================
     MISSION & VISION SECTION
     ============================================================ -->
<section style="background: #020617; padding: 90px 0;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(37, 99, 235, 0.15); border: 1px solid rgba(37, 99, 235, 0.3); color: #60A5FA; font-size: 0.85rem; font-weight: 600;">
                    <i class="fas fa-bullseye"></i> SOLVING CLASSROOM ACOUSTICS
                </div>
                <h2 style="font-size: 2.25rem; font-weight: 800; color: white; margin-bottom: 20px; line-height: 1.25;">
                    Every Student Hears.<br>Every Lecture Lives.
                </h2>
                <p style="color: #94A3B8; font-size: 1.05rem; line-height: 1.8; margin-bottom: 25px;">
                    In large university lecture halls with 500+ students, poor acoustic coverage and overcrowding mean students at the back struggle to hear clearly. <strong style="color: white;">Nadics Solutions</strong> engineered the Smart Lecture Management System (SLMS) to stream high-definition, low-latency audio directly to students' mobile phones.
                </p>
                <p style="color: #94A3B8; font-size: 1.05rem; line-height: 1.8;">
                    With automated geofenced QR attendance and AI-driven speech-to-text transcriptions, we ensure no student is left behind.
                </p>
            </div>
            
            <div class="col-lg-6">
                <div class="slms-showcase-card">
                    <h3 style="font-size: 1.4rem; font-weight: 700; color: white; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-shield-alt text-cyan"></i> Why Institutions Choose SLMS
                    </h3>
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex align-items-start gap-3">
                            <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.3); color: #38BDF8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 1.1rem; font-weight: 600; color: white; margin-bottom: 4px;">Bandwidth Efficient (64kbps)</h4>
                                <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 0; line-height: 1.6;">Optimized WebRTC streaming runs seamlessly over 2G, 3G, or congested campus Wi-Fi networks.</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34D399; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 1.1rem; font-weight: 600; color: white; margin-bottom: 4px;">Anti-Proxy Attendance Engine</h4>
                                <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 0; line-height: 1.6;">Dynamic QR refresh tokens and GPS geofence validation eliminate proxy sign-ins.</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); color: #A78BFA; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 1.1rem; font-weight: 600; color: white; margin-bottom: 4px;">AI Lecture Intelligence</h4>
                                <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 0; line-height: 1.6;">Converts live audio streams into searchable transcripts and revision guides automatically.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $__view->endSection(); ?>
