<?php
/**
 * Nadics LectureHub — Data Governance Policy View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Data Governance Policy — Nadics LectureHub',
    'page_description' => 'Student data security, encryption standards, and institutional data retention rules.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-database"></i> DATA GOVERNANCE
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Data Governance & Encryption</h1>
        <p class="hero-subtitle">
            Enterprise data protection standards, AES-256 database encryption, and strict data retention controls.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0; color: #CBD5E1;">
    <div class="container max-w-800">
        <div class="slms-showcase-card">
            <h3 style="color: white; font-weight: 700; margin-bottom: 15px;">1. Encryption at Rest & Transit</h3>
            <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 25px;">
                All WebRTC audio streams use SRTP DTLS-SRTP encryption. API traffic is enforced over TLS 1.3 HTTPS, and database backups are encrypted with AES-256.
            </p>

            <h3 style="color: white; font-weight: 700; margin-bottom: 15px;">2. Retention & Purging</h3>
            <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 0;">
                Attendance logs and activity records are retained per institutional academic policy and can be purged upon university request.
            </p>
        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
