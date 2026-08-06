<?php
/**
 * Nadics LectureHub — Navbar Component
 * Sticky navbar with search, notifications, dark mode toggle, and user menu.
 */
$auth = \Core\Auth::getInstance();
$currentUser = $auth_user ?? $auth->user();
$userName = ($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '');
$rawRole = $auth->role() ?? ($currentUser['role'] ?? 'User');
$userRole = ucfirst(str_replace('_', ' ', $rawRole));
$userInitial = strtoupper(substr($currentUser['first_name'] ?? 'U', 0, 1));
?>

<header class="slms-navbar">
    <!-- Left Side -->
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Search -->
        <div class="navbar-search hide-mobile">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search lectures, courses, students..." aria-label="Search" id="global-search">
        </div>
    </div>

    <!-- Right Side -->
    <div class="navbar-right">
        <!-- Dark Mode Toggle -->
        <button class="navbar-icon-btn" data-theme-toggle aria-label="Toggle Dark Mode" title="Toggle Theme">
            <i class="fas fa-moon"></i>
        </button>

        <!-- Notifications -->
        <div class="position-relative">
            <button class="navbar-icon-btn" id="notifications-btn" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-dot"></span>
            </button>

            <!-- Notification Dropdown -->
            <div class="notification-dropdown" id="notifications-dropdown">
                <div class="dropdown-header">
                    <h6>Notifications</h6>
                    <a href="<?= url('/notifications') ?>" class="text-secondary" style="font-size:var(--text-xs);">View all</a>
                </div>

                <div style="max-height:320px;overflow-y:auto;">
                    <div class="notification-item unread">
                        <div class="notif-icon" style="background:rgba(37,99,235,0.1);color:var(--secondary);">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-text">New lecture scheduled: <strong>Introduction to AI</strong></div>
                            <div class="notif-time">2 minutes ago</div>
                        </div>
                    </div>

                    <div class="notification-item unread">
                        <div class="notif-icon" style="background:rgba(16,185,129,0.1);color:var(--success);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-text">Assignment <strong>"Data Structures"</strong> has been graded</div>
                            <div class="notif-time">15 minutes ago</div>
                        </div>
                    </div>

                    <div class="notification-item">
                        <div class="notif-icon" style="background:rgba(245,158,11,0.1);color:var(--warning);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-text">Attendance reminder for <strong>CSC 301</strong></div>
                            <div class="notif-time">1 hour ago</div>
                        </div>
                    </div>
                </div>

                <div class="dropdown-footer">
                    <a href="<?= url('/notifications') ?>" class="text-secondary" style="font-size:var(--text-sm);font-weight:600;">
                        View All Notifications
                    </a>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="navbar-user" id="user-profile-toggle">
            <div class="d-flex align-items-center gap-2" style="cursor:pointer;">
                <div class="user-info hide-mobile">
                    <div class="user-name"><?= e(trim($userName) ?: 'User') ?></div>
                    <div class="user-role"><?= e($userRole) ?></div>
                </div>
                <div class="avatar avatar-placeholder">
                    <?= e($userInitial) ?>
                </div>
                <i class="fas fa-chevron-down hide-mobile" style="font-size:10px; color:var(--text-muted); transition:transform 0.2s;" id="user-dropdown-arrow"></i>
            </div>

            <!-- User Dropdown Menu -->
            <div class="user-dropdown-menu" id="user-dropdown-menu">
                <div class="user-dropdown-header">
                    <div class="fw-600" style="font-size:var(--text-sm);"><?= e(trim($userName) ?: 'User') ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?= e($currentUser['email'] ?? '') ?></div>
                </div>
                <div class="user-dropdown-body">
                    <a class="user-dropdown-item" href="<?= url('/profile') ?>">
                        <i class="fas fa-user me-2 text-muted"></i> My Profile
                    </a>
                    <a class="user-dropdown-item" href="<?= url('/notifications') ?>">
                        <i class="fas fa-bell me-2 text-muted"></i> Notifications
                    </a>
                    <a class="user-dropdown-item" href="<?= url('/profile') ?>">
                        <i class="fas fa-cog me-2 text-muted"></i> Settings
                    </a>
                    <a class="user-dropdown-item" href="<?= url('/contact') ?>">
                        <i class="fas fa-question-circle me-2 text-muted"></i> Help & Support
                    </a>
                </div>
                <div class="user-dropdown-divider"></div>
                <div class="user-dropdown-body">
                    <form method="POST" action="<?= url('/logout') ?>" style="margin:0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="user-dropdown-item user-dropdown-logout">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
/* ============================================================
   USER PROFILE DROPDOWN — Standalone (no Bootstrap dependency)
   ============================================================ */
.user-dropdown-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 260px;
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 14px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.04);
    z-index: 99999;
    display: none;
    animation: userDropFadeIn 0.18s ease-out;
    overflow: hidden;
}

.user-dropdown-menu.show {
    display: block !important;
}

@keyframes userDropFadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.user-dropdown-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-hover, rgba(0,0,0,0.02));
}

.user-dropdown-body {
    padding: 6px 0;
}

.user-dropdown-item {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 10px 18px;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary, #475569);
    text-decoration: none;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s ease;
    font-family: inherit;
    text-align: left;
}

.user-dropdown-item:hover {
    background: var(--bg-hover, rgba(99, 102, 241, 0.06));
    color: var(--text-primary, #1e293b);
}

.user-dropdown-logout {
    color: #ef4444 !important;
    font-weight: 600;
}

.user-dropdown-logout:hover {
    background: rgba(239, 68, 68, 0.08) !important;
    color: #dc2626 !important;
}

.user-dropdown-divider {
    height: 1px;
    background: var(--border-color, #e2e8f0);
    margin: 0;
}

/* Rotate chevron when open */
#user-profile-toggle.dropdown-open #user-dropdown-arrow {
    transform: rotate(180deg);
}
</style>

<script>
(function() {
    'use strict';

    // === User Profile Dropdown ===
    const userToggle = document.getElementById('user-profile-toggle');
    const userMenu = document.getElementById('user-dropdown-menu');
    const notifBtn = document.getElementById('notifications-btn');
    const notifDropdown = document.getElementById('notifications-dropdown');

    if (userToggle && userMenu) {
        userToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = userMenu.classList.contains('show');
            
            // Close notification dropdown if open
            if (notifDropdown) notifDropdown.classList.remove('show');
            
            // Toggle user menu
            if (isOpen) {
                userMenu.classList.remove('show');
                userToggle.classList.remove('dropdown-open');
            } else {
                userMenu.classList.add('show');
                userToggle.classList.add('dropdown-open');
            }
        });

        // Prevent clicks inside the dropdown from closing it
        userMenu.addEventListener('click', function(e) {
            // Allow form submissions and link clicks to proceed
            const isLink = e.target.closest('a');
            const isButton = e.target.closest('button[type="submit"]');
            if (!isLink && !isButton) {
                e.stopPropagation();
            }
        });
    }

    // === Notification Dropdown ===
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = notifDropdown.classList.contains('show');
            
            // Close user menu if open
            if (userMenu) {
                userMenu.classList.remove('show');
                if (userToggle) userToggle.classList.remove('dropdown-open');
            }
            
            if (isOpen) {
                notifDropdown.classList.remove('show');
            } else {
                notifDropdown.classList.add('show');
            }
        });
    }

    // === Click outside to close both ===
    document.addEventListener('click', function(e) {
        if (userMenu && !userToggle.contains(e.target)) {
            userMenu.classList.remove('show');
            if (userToggle) userToggle.classList.remove('dropdown-open');
        }
        if (notifDropdown && !notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
            notifDropdown.classList.remove('show');
        }
    });
})();
</script>
