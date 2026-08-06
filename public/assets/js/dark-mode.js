/**
 * ============================================================
 * Nadics LectureHub — Dark Mode Theme Switcher
 * ============================================================
 */

'use strict';

const ThemeSwitcher = {

    STORAGE_KEY: 'slms_theme',

    init() {
        this.html = document.documentElement;
        this.toggleBtns = document.querySelectorAll('[data-theme-toggle]');

        // Restore theme preference
        this.restoreTheme();

        // Toggle button clicks
        this.toggleBtns.forEach(btn => {
            btn.addEventListener('click', () => this.toggle());
        });

        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)')
            .addEventListener('change', (e) => {
                if (!localStorage.getItem(this.STORAGE_KEY)) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
    },

    toggle() {
        const currentTheme = this.html.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
        localStorage.setItem(this.STORAGE_KEY, newTheme);
    },

    setTheme(theme) {
        this.html.setAttribute('data-theme', theme);

        // Update toggle button icons
        this.toggleBtns.forEach(btn => {
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark'
                    ? 'fas fa-sun'
                    : 'fas fa-moon';
            }
        });
    },

    restoreTheme() {
        const saved = localStorage.getItem(this.STORAGE_KEY);

        if (saved) {
            this.setTheme(saved);
        } else {
            // Follow system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.setTheme(prefersDark ? 'dark' : 'light');
        }
    },

    isDark() {
        return this.html.getAttribute('data-theme') === 'dark';
    }
};

document.addEventListener('DOMContentLoaded', () => ThemeSwitcher.init());
window.ThemeSwitcher = ThemeSwitcher;
