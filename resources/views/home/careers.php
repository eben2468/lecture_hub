<?php
/**
 * Nadics LectureHub — Careers View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Careers at Nadics — Building Higher Education Tech',
    'page_description' => 'Join Nadics Solutions in engineering high-concurrency educational infrastructure for Africa.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-briefcase"></i> JOIN OUR MISSION
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Careers at Nadics Solutions</h1>
        <p class="hero-subtitle">
            Help us digitize university lecture halls across Africa and ensure every student gets a front-row educational experience.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0;">
    <div class="container">
        <h2 style="color: white; font-weight: 800; margin-bottom: 30px; text-center">Open Engineering & Product Roles</h2>
        <div class="row g-4">
            
            <div class="col-md-6">
                <div class="slms-showcase-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary px-3 py-1">Full-Time (Lagos / Remote)</span>
                        <span style="color: #38BDF8; font-size: 0.85rem; font-weight: 600;">Engineering</span>
                    </div>
                    <h3 style="color: white; font-weight: 700; font-size: 1.25rem;">Senior WebRTC / Audio Streaming Engineer</h3>
                    <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 20px;">Build low-latency SFU media transport pipelines and 64kbps Opus audio encoding engines.</p>
                    <a href="<?= url('/contact') ?>" class="btn-slms btn-outline-slms btn-sm" style="color: white; border: 1px solid rgba(255,255,255,0.2);">Apply Position</a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="slms-showcase-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-success px-3 py-1">Full-Time (Remote)</span>
                        <span style="color: #34D399; font-size: 0.85rem; font-weight: 600;">AI & Speech</span>
                    </div>
                    <h3 style="color: white; font-weight: 700; font-size: 1.25rem;">AI / Speech Recognition Specialist</h3>
                    <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 20px;">Optimize Whisper and local speech-to-text models for domain-specific university academic terminology.</p>
                    <a href="<?= url('/contact') ?>" class="btn-slms btn-outline-slms btn-sm" style="color: white; border: 1px solid rgba(255,255,255,0.2);">Apply Position</a>
                </div>
            </div>

        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
