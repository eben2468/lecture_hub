<?php
/**
 * Nadics LectureHub — Help Center View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Help Center & Support — Nadics LectureHub',
    'page_description' => 'Guides, FAQs, and support channels for lecturers, students, and campus admins.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-life-ring"></i> SUPPORT & KNOWLEDGEBASE
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">Help Center & Support</h1>
        <p class="hero-subtitle">
            Find setup guides, troubleshooting articles, and direct support channels for Nadics LectureHub.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0;">
    <div class="container">
        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="slms-showcase-card">
                    <div style="font-size: 2rem; color: #38BDF8; mb-3"><i class="fas fa-microphone-alt"></i></div>
                    <h3 style="color: white; font-weight: 700; font-size: 1.2rem;">Lecturer Broadcasting Guide</h3>
                    <p style="color: #94A3B8; font-size: 0.9rem;">How to initialize microphone audio streaming, schedule lectures, and manage live student Q&A.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="slms-showcase-card">
                    <div style="font-size: 2rem; color: #34D399; mb-3"><i class="fas fa-mobile-alt"></i></div>
                    <h3 style="color: white; font-weight: 700; font-size: 1.2rem;">Student Listener & QR Guide</h3>
                    <p style="color: #94A3B8; font-size: 0.9rem;">How to join live broadcasts, scan classroom attendance QR codes, and download course materials.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="slms-showcase-card">
                    <div style="font-size: 2rem; color: #A78BFA; mb-3"><i class="fas fa-user-shield"></i></div>
                    <h3 style="color: white; font-weight: 700; font-size: 1.2rem;">University Admin Portal Setup</h3>
                    <p style="color: #94A3B8; font-size: 0.9rem;">Managing faculties, departments, courses, lecturer assignments, and attendance exports.</p>
                </div>
            </div>

        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
