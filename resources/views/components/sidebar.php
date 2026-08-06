<?php
/**
 * Nadics LectureHub — Sidebar Component
 * Collapsible sidebar navigation with role-based menu items.
 */
$auth = \Core\Auth::getInstance();
$currentUser = $auth_user ?? $auth->user();
$userRole = $auth->role() ?? ($currentUser['role'] ?? 'student');

// Get current URI — use multiple fallbacks so we NEVER get null.
// On XAMPP with mod_rewrite, REQUEST_URI may be the full path including
// the subfolder prefix, but str_contains checks are path-fragment based
// so this is fine (e.g. str_contains('/lecture_hub/dashboard', '/dashboard')).
$currentUri = (string) (
    $_SERVER['REQUEST_URI']
    ?? $_SERVER['PATH_INFO']
    ?? $_SERVER['REDIRECT_URL']
    ?? '/'
);

if (!function_exists('sidebar_active')) {
    function sidebar_active(string $path): string {
        global $currentUri;
        return str_contains((string) $currentUri, $path) ? ' active' : '';
    }
}
?>

<aside class="slms-sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-logo" style="background:transparent;"><img src="<?= asset('img/logo.png') ?>" alt="Logo" style="height:100%; width:100%; object-fit:contain; border-radius:inherit;"></div>
        <div class="brand-text">
            <span class="brand-name">Nadics LectureHub</span>
            <span class="brand-tagline">Smart Lecture System</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <!-- MAIN -->
        <div class="nav-section-title">Main</div>

        <a href="<?= url('/dashboard') ?>" class="sidebar-link<?= sidebar_active('/dashboard') ?>">
            <i class="fas fa-th-large"></i>
            <span class="link-text">Dashboard</span>
        </a>

        <!-- ACADEMIC -->
        <div class="nav-section-title">Academic</div>

        <a href="<?= url('/lectures') ?>" class="sidebar-link<?= sidebar_active('/lecture') ?>" data-submenu="sub-lectures">
            <i class="fas fa-chalkboard-teacher"></i>
            <span class="link-text">Lectures</span>
            <i class="fas fa-chevron-right submenu-arrow" style="margin-left:auto;font-size:10px;transition:transform 0.2s;"></i>
        </a>
        <div class="nav-submenu<?= str_contains((string) $currentUri, '/lecture') ? ' show' : '' ?>" id="sub-lectures">
            <a href="<?= url('/lectures') ?>" class="sidebar-link<?= sidebar_active('/lectures') ?>">
                <span class="link-text">All Lectures</span>
            </a>
            <?php if (in_array($userRole, ['lecturer', 'admin', 'super_admin'])): ?>
            <a href="<?= url('/lectures/create') ?>" class="sidebar-link<?= sidebar_active('/lectures/create') ?>">
                <span class="link-text">Schedule New</span>
            </a>
            <?php endif; ?>
        </div>

        <a href="<?= url('/courses') ?>" class="sidebar-link<?= sidebar_active('/courses') ?>">
            <i class="fas fa-book-open"></i>
            <span class="link-text">Courses</span>
        </a>

        <a href="<?= url('/materials') ?>" class="sidebar-link<?= sidebar_active('/materials') ?>">
            <i class="fas fa-file-alt"></i>
            <span class="link-text">Materials</span>
        </a>

        <a href="<?= url('/assignments') ?>" class="sidebar-link<?= sidebar_active('/assignments') ?>">
            <i class="fas fa-tasks"></i>
            <span class="link-text">Assignments</span>
        </a>

        <a href="<?= url('/analytics') ?>" class="sidebar-link<?= sidebar_active('/analytics') ?>">
            <i class="fas fa-clipboard-check"></i>
            <span class="link-text">Assessment</span>
        </a>

        <!-- ENGAGEMENT -->
        <div class="nav-section-title">Engagement</div>

        <a href="<?= url('/timetable') ?>" class="sidebar-link<?= sidebar_active('/timetable') ?>">
            <i class="fas fa-calendar-alt"></i>
            <span class="link-text">Timetable</span>
        </a>

        <a href="<?= url('/quizzes') ?>" class="sidebar-link<?= sidebar_active('/quizzes') ?>">
            <i class="fas fa-question-circle"></i>
            <span class="link-text">Quizzes</span>
        </a>

        <a href="<?= url('/ai-assistant') ?>" class="sidebar-link<?= sidebar_active('/ai-assistant') ?>">
            <i class="fas fa-robot"></i>
            <span class="link-text">AI Assistant</span>
        </a>

        <a href="<?= url('/attendance') ?>" class="sidebar-link<?= sidebar_active('/attendance') ?>">
            <i class="fas fa-qrcode"></i>
            <span class="link-text">Attendance</span>
        </a>

        <a href="<?= url('/lectures?status=live') ?>" class="sidebar-link<?= (str_contains((string) $currentUri, '/stream') || (str_contains((string) $currentUri, '/lectures') && str_contains((string) $currentUri, 'status=live'))) ? ' active' : '' ?>">
            <i class="fas fa-broadcast-tower"></i>
            <span class="link-text">Live Streaming</span>
        </a>

        <?php if (in_array($userRole, ['lecturer', 'admin', 'super_admin', 'university_admin', 'student'])): ?>
        <a href="<?= url('/stream/recordings') ?>" class="sidebar-link<?= sidebar_active('/stream/recordings') ?>">
            <i class="fas fa-compact-disc"></i>
            <span class="link-text">Recordings</span>
        </a>
        <?php endif; ?>

        <a href="<?= url('/notifications') ?>" class="sidebar-link<?= sidebar_active('/notifications') ?>">
            <i class="fas fa-bell"></i>
            <span class="link-text">Notifications</span>
            <span class="badge">3</span>
        </a>

        <!-- ANALYTICS -->
        <div class="nav-section-title">Analytics</div>

        <a href="<?= url('/reports') ?>" class="sidebar-link<?= sidebar_active('/reports') ?>">
            <i class="fas fa-chart-bar"></i>
            <span class="link-text">Reports</span>
        </a>

        <a href="<?= url('/analytics') ?>" class="sidebar-link<?= sidebar_active('/analytics') ?>">
            <i class="fas fa-chart-pie"></i>
            <span class="link-text">Analytics</span>
        </a>

        <!-- ADMINISTRATION (Admin/Super Admin only) -->
        <?php if (in_array($userRole, ['admin', 'super_admin', 'university_admin'])): ?>
        <div class="nav-section-title">Administration</div>

        <a href="<?= url('/admin/universities') ?>" class="sidebar-link<?= sidebar_active('/admin/universities') ?>">
            <i class="fas fa-university"></i>
            <span class="link-text">Universities</span>
        </a>

        <a href="<?= url('/admin/faculties') ?>" class="sidebar-link<?= sidebar_active('/admin/faculties') ?>">
            <i class="fas fa-cubes"></i>
            <span class="link-text">Faculties</span>
        </a>

        <a href="<?= url('/admin/departments') ?>" class="sidebar-link<?= sidebar_active('/admin/departments') ?>">
            <i class="fas fa-building"></i>
            <span class="link-text">Departments</span>
        </a>

        <a href="<?= url('/admin/programmes') ?>" class="sidebar-link<?= sidebar_active('/admin/programmes') ?>">
            <i class="fas fa-graduation-cap"></i>
            <span class="link-text">Programmes</span>
        </a>

        <a href="<?= url('/admin/courses') ?>" class="sidebar-link<?= sidebar_active('/admin/courses') ?>">
            <i class="fas fa-book"></i>
            <span class="link-text">Course Registry</span>
        </a>

        <a href="<?= url('/admin/course-allocations') ?>" class="sidebar-link<?= sidebar_active('/admin/course-allocations') ?>">
            <i class="fas fa-tasks"></i>
            <span class="link-text">Course Allocations</span>
        </a>

        <a href="<?= url('/admin/lecturers') ?>" class="sidebar-link<?= sidebar_active('/admin/lecturers') ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span class="link-text">Lecturer Management</span>
        </a>

        <a href="<?= url('/admin/students') ?>" class="sidebar-link<?= sidebar_active('/admin/students') ?>">
            <i class="fas fa-user-graduate"></i>
            <span class="link-text">Student Management</span>
        </a>

        <a href="<?= url('/admin/users') ?>" class="sidebar-link<?= sidebar_active('/admin/users') ?>">
            <i class="fas fa-users-cog"></i>
            <span class="link-text">All Users</span>
        </a>

        <a href="<?= url('/admin/audit-logs') ?>" class="sidebar-link<?= sidebar_active('/admin/audit-logs') ?>">
            <i class="fas fa-history"></i>
            <span class="link-text">Audit Trail</span>
        </a>

        <a href="<?= url('/admin/settings') ?>" class="sidebar-link<?= sidebar_active('/admin/settings') ?>">
            <i class="fas fa-cog"></i>
            <span class="link-text">Settings</span>
        </a>
        <?php endif; ?>

    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?= url('/profile') ?>" class="sidebar-link<?= sidebar_active('/profile') ?>" style="margin:0;">
            <i class="fas fa-user-circle"></i>
            <span class="link-text"><?= e($currentUser['first_name'] ?? 'Profile') ?></span>
        </a>
    </div>
</aside>
