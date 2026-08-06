<?php
/**
 * Nadics LectureHub — Privacy Policy View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Privacy Policy — Nadics LectureHub',
    'page_description' => 'User privacy standards and student data protection commitment.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-lock"></i> PRIVACY & DATA PROTECTION
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Privacy Policy</h1>
        <p class="hero-subtitle">
            Nadics Solutions is committed to protecting the personal data of university students, faculty, and administrators.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0; color: #CBD5E1;">
    <div class="container max-w-800">
        <div class="slms-showcase-card">
            <h3 style="color: white; font-weight: 700; margin-bottom: 15px;">1. Information Collection</h3>
            <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 25px;">
                We collect essential academic identifiers (matric/staff ID, institutional email, full name) and attendance telemetry (timestamp, geofenced GPS coordinates when logging attendance).
            </p>

            <h3 style="color: white; font-weight: 700; margin-bottom: 15px;">2. Use of Data</h3>
            <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 25px;">
                Data is exclusively utilized to verify classroom attendance, deliver audio broadcasts, process AI transcripts, and generate academic analytics for authorized university administrators.
            </p>

            <h3 style="color: white; font-weight: 700; margin-bottom: 15px;">3. Data Ownership & Rights</h3>
            <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 0;">
                All educational data belongs strictly to the client institution. We do not sell or monetize student data under any circumstances.
            </p>
        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
