/**
 * ============================================================
 * Nadics LectureHub — Sidebar Controller
 * ============================================================
 */

'use strict';

const Sidebar = {

    STORAGE_KEY: 'slms_sidebar_collapsed',

    init() {
        this.sidebar = document.querySelector('.slms-sidebar');
        this.toggleBtn = document.querySelector('.sidebar-toggle');
        this.overlay = document.querySelector('.sidebar-overlay');
        this.body = document.body;

        if (!this.sidebar) return;

        // Restore state from localStorage
        this.restoreState();

        // Toggle button click
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggle());
        }

        // Overlay click (mobile)
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.closeMobile());
        }

        // Active link highlighting
        this.highlightActiveLink();

        // Submenu toggles
        this.initSubmenus();

        // Handle window resize
        window.addEventListener('resize', SLMS.debounce(() => this.handleResize(), 200));
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
        localStorage.setItem(this.STORAGE_KEY, isCollapsed ? '1' : '0');
    },

    toggleMobile() {
        this.sidebar.classList.toggle('mobile-open');
        if (this.overlay) {
            this.overlay.classList.toggle('show');
        }
    },

    closeMobile() {
        this.sidebar.classList.remove('mobile-open');
        if (this.overlay) {
            this.overlay.classList.remove('show');
        }
    },

    restoreState() {
        if (window.innerWidth > 768) {
            const isCollapsed = localStorage.getItem(this.STORAGE_KEY) === '1';
            if (isCollapsed) {
                this.sidebar.classList.add('collapsed');
                this.body.classList.add('sidebar-collapsed');
            }
        }
    },

    handleResize() {
        if (window.innerWidth > 768) {
            this.sidebar.classList.remove('mobile-open');
            if (this.overlay) {
                this.overlay.classList.remove('show');
            }
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
