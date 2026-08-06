<?php
/**
 * Nadics LectureHub — Terms of Service View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Terms of Service — Nadics LectureHub',
    'page_description' => 'Terms of service and institutional usage agreement for Nadics LectureHub.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-file-contract"></i> USAGE AGREEMENT
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Terms of Service</h1>
        <p class="hero-subtitle">
            Institutional terms governing the use of Nadics LectureHub software and streaming infrastructure.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0; color: #CBD5E1;">
    <div class="container max-w-800">
        <div class="slms-showcase-card">
            <h3 style="color: white; font-weight: 700; margin-bottom: 15px;">1. Service Authorization</h3>
            <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 25px;">
                Access is granted to registered students, faculty members, and institutional staff authorized by client universities.
            </p>

            <h3 style="color: white; font-weight: 700; margin-bottom: 15px;">2. Acceptable Use</h3>
            <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 0;">
                Users agree not to intercept, record without authorization, or attempt to subvert attendance geofencing or authentication controls.
            </p>
        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
