/**
 * ============================================================
 * Nadics LectureHub — Main Application JavaScript
 * ============================================================
 *
 * Core JS module providing:
 * - CSRF token injection for all forms
 * - Toast notification system
 * - Form validation UI
 * - Utility functions (debounce, throttle, format)
 * - Bootstrap component initialization
 *
 * @author  Nadics Solutions
 * @version 1.0.0
 * ============================================================
 */

'use strict';

/**
 * SLMS Application Namespace
 */
const SLMS = {

    /**
     * Initialize the application.
     * Called on DOMContentLoaded.
     */
    init() {
        this.injectCSRFTokens();
        this.initTooltips();
        this.initDropdowns();
        this.showFlashMessages();
        this.initFormValidation();
        this.initDeleteConfirmation();
        this.initScrollAnimations();

        console.log('%c🎓 Nadics LectureHub v' + (document.querySelector('meta[name="app-version"]')?.content || '1.0.0'),
            'color: #2563EB; font-size: 14px; font-weight: bold;');
    },

    // ========================================================
    // CSRF TOKEN INJECTION
    // ========================================================

    /**
     * Automatically inject CSRF tokens into all forms
     * and set up the token for AJAX requests.
     */
    injectCSRFTokens() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!token) return;

        // Inject hidden field into all forms without a CSRF token
        document.querySelectorAll('form').forEach(form => {
            if (!form.querySelector('input[name="_csrf_token"]')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_csrf_token';
                input.value = token;
                form.appendChild(input);
            }
        });
    },

    // ========================================================
    // TOAST NOTIFICATION SYSTEM
    // ========================================================

    /**
     * Show a toast notification.
     *
     * @param {string} message  The notification message
     * @param {string} type     Type: success, error, warning, info
     * @param {number} duration Duration in ms (default: 5000)
     */
    toast(message, type = 'info', duration = 5000) {
        let container = document.getElementById('toast-container');

        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.className = `toast-slms toast-${type}`;
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info}"></i>
            <span>${this.escapeHtml(message)}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        `;

        container.appendChild(toast);

        // Auto-dismiss
        if (duration > 0) {
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    },

    /**
     * Display flash messages set by the server.
     */
    showFlashMessages() {
        const flashData = document.getElementById('flash-data');
        if (!flashData) return;

        const types = ['success', 'error', 'warning', 'info'];
        types.forEach(type => {
            const message = flashData.dataset[type];
            if (message) {
                this.toast(message, type);
            }
        });
    },

    // ========================================================
    // FORM VALIDATION UI
    // ========================================================

    /**
     * Initialize client-side form validation feedback.
     */
    initFormValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                let isValid = true;

                form.querySelectorAll('[required]').forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                // Email validation
                form.querySelectorAll('[type="email"]').forEach(input => {
                    if (input.value && !this.isValidEmail(input.value)) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    this.toast('Please fill in all required fields correctly.', 'error');
                }
            });

            // Clear validation on input
            form.querySelectorAll('.form-control-slms, .form-control').forEach(input => {
                input.addEventListener('input', () => {
                    input.classList.remove('is-invalid');
                });
            });
        });
    },

    /**
     * Initialize delete confirmation dialogs.
     */
    initDeleteConfirmation() {
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', (e) => {
                const message = element.dataset.confirm || 'Are you sure you want to delete this item?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    },

    // ========================================================
    // BOOTSTRAP COMPONENT INITIALIZATION
    // ========================================================

    /**
     * Initialize Bootstrap tooltips.
     */
    initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        }
    },

    /**
     * Initialize dropdown click-outside behavior.
     * NOTE: Navbar dropdowns (user profile + notifications) are now fully
     * self-managed in navbar.php's inline <script>. This method is kept
     * as a no-op for backwards compatibility with the init() call chain.
     */
    initDropdowns() {
        // Handled by navbar.php inline script — no duplicate listeners needed.
    },

    // ========================================================
    // SCROLL ANIMATIONS
    // ========================================================

    /**
     * Initialize scroll-triggered animations using IntersectionObserver.
     */
    initScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fadeInUp');
                    entry.target.style.opacity = '1';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('[data-animate]').forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    },

    // ========================================================
    // UTILITY FUNCTIONS
    // ========================================================

    /**
     * Escape HTML to prevent XSS.
     *
     * @param {string} text Raw text
     * @returns {string} Escaped HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Validate email format.
     *
     * @param {string} email
     * @returns {boolean}
     */
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    /**
     * Debounce a function call.
     *
     * @param {Function} func Function to debounce
     * @param {number} wait Wait time in ms
     * @returns {Function}
     */
    debounce(func, wait = 300) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Throttle a function call.
     *
     * @param {Function} func Function to throttle
     * @param {number} limit Limit in ms
     * @returns {Function}
     */
    throttle(func, limit = 300) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    /**
     * Format a number with commas.
     *
     * @param {number} num
     * @returns {string}
     */
    formatNumber(num) {
        return new Intl.NumberFormat().format(num);
    },

    /**
     * Format a date string.
     *
     * @param {string} dateStr Date string
     * @param {object} options Intl.DateTimeFormat options
     * @returns {string}
     */
    formatDate(dateStr, options = {}) {
        const defaults = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('en-US', { ...defaults, ...options });
    },

    /**
     * Copy text to clipboard.
     *
     * @param {string} text Text to copy
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.toast('Copied to clipboard!', 'success', 2000);
        } catch (err) {
            this.toast('Failed to copy', 'error');
        }
    },

    /**
     * Animate a number counting up (for dashboard stats).
     *
     * @param {HTMLElement} element Target element
     * @param {number} target Target number
     * @param {number} duration Animation duration in ms
     */
    countUp(element, target, duration = 1000) {
        const start = 0;
        const startTime = performance.now();

        const update = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * target);

            element.textContent = this.formatNumber(current);

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        };

        requestAnimationFrame(update);
    }
};

// ============================================================
// INITIALIZE ON DOM READY
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    SLMS.init();
});

// Make SLMS globally accessible
window.SLMS = SLMS;
