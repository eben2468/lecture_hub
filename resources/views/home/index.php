<?php
/**
 * Nadics LectureHub — Landing Page View
 * Premium SaaS landing page with hero banner, feature highlights, and CTA.
 */
$__view->layout('layouts.guest', [
    'page_title'       => 'Home — Smart Lecture Management System',
    'page_description' => 'Africa\'s most advanced Smart Lecture Management System.',
]);
?>

<?php $__view->section('content'); ?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section style="background: linear-gradient(135deg, #0F172A 0%, #1E3A5F 40%, #2563EB 100%); padding: 140px 0 100px; color: white; position: relative; overflow: hidden;">
    <!-- Floating background glow circles -->
    <div class="floating-glow-1" style="position: absolute; top: -100px; right: -100px; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(6, 182, 212, 0.2) 0%, transparent 70%); pointer-events: none;"></div>
    <div class="floating-glow-2" style="position: absolute; bottom: -150px; left: -100px; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, transparent 70%); pointer-events: none;"></div>

    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4 animate-fadeInUp delay-100" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); font-size: 13px;">
                    <span class="badge-slms badge-accent">NEW</span>
                    <span>Smart Lecture Management System for Universities</span>
                </div>

                <h1 class="animate-fadeInUp delay-200" style="font-size: 3.2rem; font-weight: 900; line-height: 1.15; color: white; margin-bottom: 20px;">
                    Every Student Hears.<br>
                    <span style="background: linear-gradient(90deg, #38BDF8, #818CF8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Every Lecture Lives.</span>
                </h1>

                <p class="animate-fadeInUp delay-300" style="font-size: 1.15rem; color: #94A3B8; max-width: 580px; margin-bottom: 35px; line-height: 1.7;">
                    Digitizing university lecture halls across Africa. Real-time audio streaming, automated QR attendance, AI-driven transcription, and seamless academic analytics.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-3 animate-fadeInUp delay-400">
                    <a href="<?= url('/register') ?>" class="btn-slms btn-accent btn-lg">
                        Get Started Free <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                    <a href="<?= url('/login') ?>" class="btn-slms btn-outline-slms btn-lg" style="color: white; border-color: rgba(255,255,255,0.4);">
                        <i class="fas fa-sign-in-alt me-2"></i> Portal Login
                    </a>
                </div>

                <!-- Stats summary -->
                <div class="row g-4 mt-4 pt-4 border-top animate-fadeInUp delay-500" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                    <div class="col-4">
                        <div style="font-size: 1.8rem; font-weight: 800; color: white;"><span class="stat-count" data-target="500">0</span>K+</div>
                        <div style="font-size: 12px; color: #94A3B8;">Students Supported</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size: 1.8rem; font-weight: 800; color: white;"><span class="stat-count" data-target="99.9" data-decimals="1">0</span>%</div>
                        <div style="font-size: 12px; color: #94A3B8;">Uptime SLA</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size: 1.8rem; font-weight: 800; color: white;">&lt; <span class="stat-count" data-target="50" data-start="150">150</span>ms</div>
                        <div style="font-size: 12px; color: #94A3B8;">Audio Latency</div>
                    </div>
                </div>
            </div>

            <!-- Hero Mockup Card -->
            <div class="col-lg-5 animate-fadeInUp delay-600">
                <div class="premium-mockup-card" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px); border-radius: 24px; padding: 25px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #EF4444;"></div>
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #F59E0B;"></div>
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #10B981;"></div>
                        </div>
                        <span style="font-size: 12px; color: #10B981; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                            <span style="position: relative; display: inline-block; width: 8px; height: 8px;">
                                <span class="radar-ring-1"></span>
                                <span class="radar-ring-2"></span>
                                <span style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; background: #10B981; display: block;"></span>
                            </span>
                            <i class="fas fa-broadcast-tower me-1"></i> LIVE STREAMING
                        </span>
                    </div>

                    <div style="background: #0F172A; border-radius: 16px; padding: 20px; margin-bottom: 15px;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span style="font-size: 12px; color: #94A3B8;">CSC 301 — Data Structures</span>
                            <span class="badge-slms badge-success">Active</span>
                        </div>
                        <div style="font-size: 1.1rem; font-weight: 700; color: white; margin-bottom: 8px;">Prof. O. Adebayo</div>
                        <!-- Audio Equalizer Mockup -->
                        <div class="d-flex align-items-end gap-1" style="height: 40px; margin: 15px 0;">
                            <div class="equalizer-bar" style="flex:1; background:#2563EB; height:40%; border-radius:4px; animation-duration: 0.7s; animation-delay: 0.1s;"></div>
                            <div class="equalizer-bar" style="flex:1; background:#06B6D4; height:80%; border-radius:4px; animation-duration: 0.9s; animation-delay: 0.4s;"></div>
                            <div class="equalizer-bar" style="flex:1; background:#2563EB; height:60%; border-radius:4px; animation-duration: 0.6s; animation-delay: 0.2s;"></div>
                            <div class="equalizer-bar" style="flex:1; background:#38BDF8; height:95%; border-radius:4px; animation-duration: 0.8s; animation-delay: 0.5s;"></div>
                            <div class="equalizer-bar" style="flex:1; background:#2563EB; height:50%; border-radius:4px; animation-duration: 0.7s; animation-delay: 0.3s;"></div>
                            <div class="equalizer-bar" style="flex:1; background:#06B6D4; height:90%; border-radius:4px; animation-duration: 0.9s; animation-delay: 0.1s;"></div>
                            <div class="equalizer-bar" style="flex:1; background:#2563EB; height:30%; border-radius:4px; animation-duration: 0.5s; animation-delay: 0.4s;"></div>
                            <div class="equalizer-bar" style="flex:1; background:#38BDF8; height:70%; border-radius:4px; animation-duration: 0.8s; animation-delay: 0.2s;"></div>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: 11px; color: #94A3B8;">
                            <span>342 Students Listening</span>
                            <span>Low-Latency Mode</span>
                        </div>
                    </div>

                    <div class="p-3 scanner-container" style="background: rgba(255, 255, 255, 0.05); border-radius: 12px; font-size: 13px; border: 1px solid rgba(255, 255, 255, 0.05);">
                        <div class="scanner-beam"></div>
                        <i class="fas fa-qrcode text-accent me-2 animate-pulse"></i>
                        <span>QR Code Attendance Active: <strong>94% Verified</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURES OVERVIEW
     ============================================================ -->
<section style="padding: 100px 0; background: var(--bg-surface);">
    <div class="container">
        <div class="text-center max-w-600 mx-auto mb-5">
            <span class="badge-slms badge-primary mb-2">POWERFUL CAPABILITIES</span>
            <h2 style="font-size: 2.2rem; font-weight: 800; color: var(--primary);">Built for Modern Higher Education</h2>
            <p class="text-muted">Designed specifically to overcome large classroom constraints, poor acoustic coverage, and manual attendance tracking in African universities.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4 delay-100" data-animate="true">
                <div class="slms-card h-100 p-4 hover-lift-card">
                    <div class="stat-icon icon-primary mb-3" style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--secondary),var(--accent));color:white;">
                        <i class="fas fa-microphone-alt"></i>
                    </div>
                    <h4>Live Audio Broadcasting</h4>
                    <p class="text-muted" style="font-size: var(--text-sm);">Stream lecturer audio directly to students' mobile phones in real-time with sub-50ms latency over local campus Wi-Fi.</p>
                </div>
            </div>

            <div class="col-md-4 delay-200" data-animate="true">
                <div class="slms-card h-100 p-4 hover-lift-card-success">
                    <div class="stat-icon icon-success mb-3" style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--success),var(--accent));color:white;">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h4>Smart QR Attendance</h4>
                    <p class="text-muted" style="font-size: var(--text-sm);">Dynamic, fraud-proof QR codes with geolocation validation eliminate proxy attendance and save 15+ minutes per lecture.</p>
                </div>
            </div>

            <div class="col-md-4 delay-300" data-animate="true">
                <div class="slms-card h-100 p-4 hover-lift-card-info">
                    <div class="stat-icon icon-info mb-3" style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--info),var(--secondary));color:white;">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h4>AI Lecture Processing</h4>
                    <p class="text-muted" style="font-size: var(--text-sm);">Automatic speech-to-text transcribes every lecture into searchable notes, key takeaways, and interactive revision quizzes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     CALL TO ACTION
     ============================================================ -->
<section style="padding: 80px 0; background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: white;">
    <div class="container text-center">
        <h2 style="font-size: 2.2rem; font-weight: 800; color: white; margin-bottom: 15px;">Ready to Transform Your Campus?</h2>
        <p style="font-size: 1.1rem; color: #94A3B8; max-width: 600px; margin: 0 auto 30px;">Deploy Nadics LectureHub across your institution today and ensure every student has front-row access.</p>
        <a href="<?= url('/register') ?>" class="btn-slms btn-primary btn-lg">
            Deploy Now <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statsElements = document.querySelectorAll('.stat-count');
    
    const animateStat = (el) => {
        const target = parseFloat(el.getAttribute('data-target'));
        const start = parseFloat(el.getAttribute('data-start') || '0');
        const decimals = parseInt(el.getAttribute('data-decimals') || '0');
        const duration = 1500; // ms
        const startTime = performance.now();
        
        const update = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Cubic ease-out formula
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const value = start + (target - start) * easeProgress;
            
            el.textContent = value.toFixed(decimals);
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        };
        
        requestAnimationFrame(update);
    };

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStat(entry.target);
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    statsElements.forEach(el => statsObserver.observe(el));
});
</script>

<?php $__view->endSection(); ?>
