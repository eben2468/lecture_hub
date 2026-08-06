<?php
/**
 * Nadics LectureHub — Contact View
 * Support channel and contact interface.
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Contact Us — Smart Lecture Management System',
    'page_description' => 'Get in touch with Nadics Solutions support team.',
]);
?>

<?php $__view->section('content'); ?>
<section class="hero-header-section">
    <div class="container text-center position-relative" style="z-index: 1;">
        <h1 class="hero-gradient-title mb-3">Contact Us</h1>
        <p class="hero-subtitle">
            Have questions about onboarding your university? Get in touch with the Nadics Solutions team.
        </p>
    </div>
</section>

<section style="background: #090D16; padding: 80px 0; color: #E2E8F0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div style="background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.05); padding: 40px; border-radius: 24px;">
                    <h3 class="fw-700 text-white mb-4">Send us a message</h3>
                    <form onsubmit="event.preventDefault(); alert('Message sent successfully. We will get back to you shortly.');">
                        <div class="form-group mb-3">
                            <label class="form-label text-muted">Full Name</label>
                            <input type="text" class="form-control-slms" placeholder="e.g. John Doe" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label text-muted">Email Address</label>
                            <input type="email" class="form-control-slms" placeholder="e.g. john@university.edu.ng" required>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label text-muted">Message</label>
                            <textarea class="form-control-slms" rows="4" placeholder="How can we help your institution?" required></textarea>
                        </div>
                        <button type="submit" class="btn-slms btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__view->endSection(); ?>
