<?php
/**
 * Nadics LectureHub — Guest/Public Layout
 * Used for landing page, about, contact, and other public pages.
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="<?= e($page_description ?? 'Nadics LectureHub — Smart Lecture Management System. Every Student Hears. Every Lecture Lives.') ?>">
    <meta name="keywords" content="lecture management, university, streaming, education, Africa, LMS">
    <meta name="author" content="Nadics Solutions">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">

    <title><?= e($page_title ?? 'Welcome') ?> — <?= e(env('APP_NAME', 'Nadics LectureHub')) ?></title>

    <link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dark-mode.css') ?>" rel="stylesheet">

    <style>
        /* Guest-specific Navbar */
        .guest-navbar {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 14px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .guest-navbar .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-weight: 800;
            font-size: 1.25rem;
            text-decoration: none;
            letter-spacing: -0.3px;
        }

        .guest-navbar .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563EB 0%, #06B6D4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.1rem;
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .guest-navbar .nav-link {
            color: #CBD5E1 !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 8px 16px !important;
            transition: all 0.2s ease;
            border-radius: 8px;
        }

        .guest-navbar .nav-link:hover {
            color: #FFFFFF !important;
            background: rgba(255, 255, 255, 0.05);
        }

        /* Glassmorphism & High-Contrast Showcase Utilities */
        .hero-header-section {
            background: linear-gradient(135deg, #020617 0%, #0F172A 60%, #1E293B 100%);
            padding: 140px 0 90px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-header-section::before {
            content: '';
            position: absolute;
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            width: 900px;
            height: 450px;
            background: radial-gradient(ellipse at center, rgba(37, 99, 235, 0.22) 0%, rgba(6, 182, 212, 0.12) 45%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .hero-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 18px;
            border-radius: 50px;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(56, 189, 248, 0.35);
            color: #38BDF8;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 20px rgba(6, 182, 212, 0.15);
        }

        .hero-gradient-title {
            font-size: 3.25rem;
            font-weight: 900;
            line-height: 1.15;
            color: #FFFFFF !important;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            color: #CBD5E1 !important;
            font-size: 1.15rem;
            line-height: 1.7;
            max-width: 680px;
            margin: 0 auto;
        }

        /* Glassmorphic Cards */
        .slms-showcase-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.75), rgba(15, 23, 42, 0.85));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 32px;
            height: 100%;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .slms-showcase-card:hover {
            transform: translateY(-8px);
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 25px 45px -15px rgba(0, 0, 0, 0.7), 0 0 30px rgba(6, 182, 212, 0.15);
        }

        .showcase-icon-box {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 22px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .showcase-card-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #FFFFFF !important;
            margin-bottom: 12px;
        }

        .showcase-card-text {
            color: #94A3B8 !important;
            font-size: 0.95rem;
            line-height: 1.65;
            margin-bottom: 20px;
        }

        .guest-footer {
            background: #020617;
            color: #94A3B8;
            padding: 70px 0 35px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .guest-footer h5 {
            color: white;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }

        .guest-footer a {
            color: #94A3B8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .guest-footer a:hover {
            color: #38BDF8;
        }

        .guest-footer .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 25px;
            margin-top: 45px;
            font-size: 0.9rem;
            color: #64748B;
        }
    </style>
</head>
<body>

    <!-- ============================================================
         GUEST NAVBAR
         ============================================================ -->
    <nav class="guest-navbar">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <a href="<?= url('/') ?>" class="navbar-brand">
                    <div class="brand-icon">N</div>
                    <span>Nadics LectureHub</span>
                </a>

                <div class="d-flex align-items-center gap-2">
                    <a href="<?= url('/features') ?>" class="nav-link hide-mobile">Features</a>
                    <a href="<?= url('/about') ?>" class="nav-link hide-mobile">About</a>
                    <a href="<?= url('/contact') ?>" class="nav-link hide-mobile">Contact</a>
                    <a href="<?= url('/login') ?>" class="btn-slms btn-ghost" style="color: var(--gray-300);">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?= url('/register') ?>" class="btn-slms btn-primary btn-sm">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div id="flash-data" class="d-none"
        data-success="<?= e($flash_success ?? '') ?>"
        data-error="<?= e($flash_error ?? '') ?>">
    </div>

    <!-- Main Content -->
    <main>
        <?= $__view->yield('content') ?>
    </main>

    <!-- ============================================================
         GUEST FOOTER
         ============================================================ -->
    <footer class="guest-footer">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-icon" style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--secondary),var(--accent));display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:12px;">N</div>
                        <span style="color:white;font-weight:700;">Nadics LectureHub</span>
                    </div>
                    <p style="font-size:var(--text-sm);margin-bottom:var(--space-4);">
                        Smart Lecture Management System — Every Student Hears. Every Lecture Lives.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5>Product</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="<?= url('/features') ?>">Features</a></li>
                        <li><a href="<?= url('/pricing') ?>">Pricing</a></li>
                        <li><a href="<?= url('/demo') ?>">Demo</a></li>
                        <li><a href="<?= url('/api-docs') ?>">API</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5>Company</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="<?= url('/about') ?>">About Us</a></li>
                        <li><a href="<?= url('/careers') ?>">Careers</a></li>
                        <li><a href="<?= url('/contact') ?>">Contact</a></li>
                        <li><a href="<?= url('/blog') ?>">Blog</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5>Support</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="<?= url('/help') ?>">Help Center</a></li>
                        <li><a href="<?= url('/docs') ?>">Documentation</a></li>
                        <li><a href="<?= url('/status') ?>">Status</a></li>
                        <li><a href="<?= url('/community') ?>">Community</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5>Legal</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="<?= url('/privacy') ?>">Privacy Policy</a></li>
                        <li><a href="<?= url('/terms') ?>">Terms of Service</a></li>
                        <li><a href="<?= url('/data-policy') ?>">Data Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                <span>&copy; <?= date('Y') ?> Nadics Solutions. All rights reserved.</span>
                <span>Version <?= e(env('APP_VERSION', '1.0.0')) ?></span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/dark-mode.js') ?>"></script>
</body>
</html>
