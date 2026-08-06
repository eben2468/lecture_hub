<?php
/**
 * Nadics LectureHub — EdTech Insights Blog View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'EdTech Insights Blog — Nadics Solutions',
    'page_description' => 'Articles and engineering insights on classroom technology and bandwidth optimization.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-newspaper"></i> ENGINEERING & RESEARCH BLOG
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">EdTech Engineering Insights</h1>
        <p class="hero-subtitle">
            Exploring bandwidth optimization, acoustic coverage, and AI speech recognition for large-scale African universities.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0;">
    <div class="container">
        <div class="row g-4">
            
            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <span class="badge bg-primary mb-3">AUDIO STREAMING</span>
                    <h3 style="color: white; font-weight: 700; font-size: 1.25rem; margin-bottom: 12px;">Why 64kbps Audio Beats Video in African Lecture Halls</h3>
                    <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 20px;">Analyzing network latency, packet loss, and cellular data consumption across congested campus networks.</p>
                    <span style="color: #64748B; font-size: 0.8rem;">July 2026 • 6 min read</span>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <span class="badge bg-success mb-3">SECURITY & ATTENDANCE</span>
                    <h3 style="color: white; font-weight: 700; font-size: 1.25rem; margin-bottom: 12px;">Eliminating Attendance Fraud with Geofenced Tokens</h3>
                    <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 20px;">How dynamic rotating QR refresh hashes and mobile GPS validation stop remote proxy sign-ins.</p>
                    <span style="color: #64748B; font-size: 0.8rem;">June 2026 • 8 min read</span>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="slms-showcase-card">
                    <span class="badge bg-purple mb-3" style="background: #8B5CF6;">AI TRANSCRIPTION</span>
                    <h3 style="color: white; font-weight: 700; font-size: 1.25rem; margin-bottom: 12px;">Automating Lecture Notes with Custom Academic Whisper AI</h3>
                    <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 20px;">Fine-tuning automatic speech-to-text models to recognize STEM terminology and local accents.</p>
                    <span style="color: #64748B; font-size: 0.8rem;">May 2026 • 5 min read</span>
                </div>
            </div>

        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
