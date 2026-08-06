<?php
/**
 * Nadics LectureHub — Community Hub View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Academic Community Hub — Nadics LectureHub',
    'page_description' => 'Connect with educators, researchers, and university administrators using SLMS.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-users"></i> ACADEMIC COMMUNITY HUB
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Academic & Educator Community</h1>
        <p class="hero-subtitle">
            Join hundreds of university lecturers, department heads, and EdTech researchers transforming classroom learning.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0;">
    <div class="container text-center">
        <div class="slms-showcase-card max-w-600 mx-auto p-5">
            <i class="fas fa-comments text-cyan mb-3" style="font-size: 3rem;"></i>
            <h3 style="color: white; font-weight: 700; mb-3">Join the Nadics Educator Forum</h3>
            <p style="color: #94A3B8; font-size: 0.95rem; margin-bottom: 25px;">Share lecture management strategies, request new features, and connect with academic peers.</p>
            <a href="<?= url('/contact') ?>" class="btn-slms btn-accent py-3 px-4" style="background: linear-gradient(135deg, #2563EB, #06B6D4); border: none; color: white; border-radius: 12px; font-weight: 700;">
                Join Community Forum <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
