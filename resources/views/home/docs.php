<?php
/**
 * Nadics LectureHub — System Technical Documentation View
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'System Documentation — Nadics LectureHub',
    'page_description' => 'Complete technical user guides and architecture documentation.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <div class="mb-3">
            <span class="hero-badge-pill">
                <i class="fas fa-book"></i> TECHNICAL DOCUMENTATION
            </span>
        </div>
        <h1 class="hero-gradient-title mb-3">System Documentation</h1>
        <p class="hero-subtitle">
            Complete technical documentation, architecture specifications, and API integration guides.
        </p>
    </div>
</section>

<section style="background: #020617; padding: 80px 0;">
    <div class="container">
        <div class="row g-4">
            
            <div class="col-lg-6">
                <div class="slms-showcase-card">
                    <h3 style="color: white; font-weight: 700; margin-bottom: 15px;"><i class="fas fa-cubes text-cyan me-2"></i> Core Framework Architecture</h3>
                    <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7;">
                        Nadics LectureHub is powered by a high-performance custom PHP 8 MVC core engine featuring clean Dependency Injection, fluent PDO QueryBuilder with parameter binding, and regex routing with middleware group stack support.
                    </p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="slms-showcase-card">
                    <h3 style="color: white; font-weight: 700; margin-bottom: 15px;"><i class="fas fa-server text-emerald me-2"></i> Deployment Requirements</h3>
                    <p style="color: #94A3B8; font-size: 0.95rem; line-height: 1.7;">
                        Requires PHP >= 8.0 with PDO extension, MariaDB 10.4 / MySQL 8.0, and Apache with mod_rewrite enabled. All uploads reside in protected storage volumes.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
