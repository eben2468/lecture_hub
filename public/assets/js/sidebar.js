/**
 * ============================================================
 * Nadics LectureHub — Sidebar Controller
 * ============================================================
 */

'use strict';

const Sidebar = {

    STORAGE_KEY: 'slms_sidebar_collapsed',

    init() {
        // Support lookup by both class and ID for maximum reliability
        this.sidebar   = document.getElementById('sidebar') || document.querySelector('.slms-sidebar');
        this.toggleBtn = document.getElementById('sidebar-toggle') || document.querySelector('.sidebar-toggle');
        this.overlay   = document.getElementById('sidebar-overlay') || document.querySelector('.sidebar-overlay');
        this.body      = document.body;

        if (!this.sidebar) return;

        // Restore state from localStorage (desktop only)
        this.restoreState();

        // Toggle button click — attach directly so it always works on every page
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggle();
            });
        }

        // Overlay click closes sidebar on mobile
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.closeMobile());
        }

        // Active link highlighting
        this.highlightActiveLink();

        // Submenu toggles
        this.initSubmenus();

        // Handle window resize — guard against SLMS not being available yet
        const debouncedResize = (typeof window.SLMS !== 'undefined' && typeof window.SLMS.debounce === 'function')
            ? window.SLMS.debounce(() => this.handleResize(), 200)
            : () => this.handleResize();

        window.addEventListener('resize', debouncedResize);
    },

    toggle() {
        if (window.innerWidth <= 768) {
            this.toggleMobile();
        } else {
            this.toggleDesktop();
        }
    },

    toggleDesktop() {
        this.sidebar.classList.toggle('collapsed');
        this.body.classList.toggle('sidebar-collapsed');

        const isCollapsed = this.sidebar.classList.contains('collapsed');
        try { localStorage.setItem(this.STORAGE_KEY, isCollapsed ? '1' : '0'); } catch(e) {}
    },

    toggleMobile() {
        this.sidebar.classList.toggle('mobile-open');
        if (this.overlay) {
            this.overlay.classList.toggle('show');
        }
        // Prevent body scroll while sidebar is open
        this.body.style.overflow = this.sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    },

    closeMobile() {
        this.sidebar.classList.remove('mobile-open');
        if (this.overlay) {
            this.overlay.classList.remove('show');
        }
        this.body.style.overflow = '';
    },

    restoreState() {
        if (window.innerWidth > 768) {
            try {
                const isCollapsed = localStorage.getItem(this.STORAGE_KEY) === '1';
                if (isCollapsed) {
                    this.sidebar.classList.add('collapsed');
                    this.body.classList.add('sidebar-collapsed');
                }
            } catch(e) {}
        }
    },

    handleResize() {
        if (window.innerWidth > 768) {
            this.sidebar.classList.remove('mobile-open');
            if (this.overlay) {
                this.overlay.classList.remove('show');
            }
            this.body.style.overflow = '';
        }
    },

    highlightActiveLink() {
        const currentPath = window.location.pathname;

        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.classList.remove('active');

            const href = link.getAttribute('href');
            if (href && currentPath.endsWith(href)) {
                link.classList.add('active');

                // Open parent submenu if in nested menu
                const submenu = link.closest('.nav-submenu');
                if (submenu) {
                    submenu.classList.add('show');
                    const parentToggle = submenu.previousElementSibling;
                    if (parentToggle) {
                        parentToggle.classList.add('active');
                    }
                }
            }
        });
    },

    initSubmenus() {
        document.querySelectorAll('[data-submenu]').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = toggle.dataset.submenu;
                const submenu = document.getElementById(targetId);

                if (submenu) {
                    submenu.classList.toggle('show');
                    toggle.classList.toggle('active');

                    // Rotate chevron icon
                    const chevron = toggle.querySelector('.submenu-arrow');
                    if (chevron) {
                        chevron.style.transform = submenu.classList.contains('show')
                            ? 'rotate(90deg)' : 'rotate(0deg)';
                    }
                }
            });
        });
    }
};

document.addEventListener('DOMContentLoaded', () => Sidebar.init());

