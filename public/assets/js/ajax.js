/**
 * ============================================================
 * Nadics LectureHub — AJAX Utility Module
 * ============================================================
 *
 * Provides a clean wrapper around Fetch API with:
 * - Automatic CSRF token injection
 * - JSON parsing
 * - Error handling
 * - Loading state management
 *
 * @author  Nadics Solutions
 * @version 1.0.0
 * ============================================================
 */

'use strict';

const Ajax = {

    /**
     * Get the CSRF token from the meta tag.
     *
     * @returns {string|null}
     */
    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || null;
    },

    /**
     * Build default headers for requests.
     *
     * @param {boolean} isJson Whether to include JSON content type
     * @returns {Object}
     */
    getHeaders(isJson = true) {
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        };

        const token = this.getCSRFToken();
        if (token) {
            headers['X-CSRF-TOKEN'] = token;
        }

        if (isJson) {
            headers['Content-Type'] = 'application/json';
        }

        return headers;
    },

    /**
     * Perform a GET request.
     *
     * @param {string} url       Request URL
     * @param {Object} params    Query parameters
     * @param {Object} options   Additional fetch options
     * @returns {Promise<Object>}
     */
    async get(url, params = {}, options = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;

        return this.request(fullUrl, {
            method: 'GET',
            ...options
        });
    },

    /**
     * Perform a POST request.
     *
     * @param {string} url  Request URL
     * @param {Object} data Request body data
     * @param {Object} options Additional options
     * @returns {Promise<Object>}
     */
    async post(url, data = {}, options = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data),
            ...options
        });
    },

    /**
     * Perform a PUT request.
     *
     * @param {string} url  Request URL
     * @param {Object} data Request body data
     * @returns {Promise<Object>}
     */
    async put(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },

    /**
     * Perform a PATCH request.
     *
     * @param {string} url  Request URL
     * @param {Object} data Request body data
     * @returns {Promise<Object>}
     */
    async patch(url, data = {}) {
        return this.request(url, {
            method: 'PATCH',
            body: JSON.stringify(data),
        });
    },

    /**
     * Perform a DELETE request.
     *
     * @param {string} url Request URL
     * @returns {Promise<Object>}
     */
    async delete(url) {
        return this.request(url, {
            method: 'DELETE',
        });
    },

    /**
     * Upload a file using FormData.
     *
     * @param {string}   url      Upload URL
     * @param {FormData} formData FormData object with file
     * @param {Function} onProgress Progress callback (0-100)
     * @returns {Promise<Object>}
     */
    async upload(url, formData, onProgress = null) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);

            // Set CSRF header
            const token = this.getCSRFToken();
            if (token) {
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            // Progress tracking
            if (onProgress && xhr.upload) {
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        onProgress(percent);
                    }
                });
            }

            xhr.onload = () => {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(response);
                    } else {
                        reject(response);
                    }
                } catch (e) {
                    reject({ message: 'Invalid server response' });
                }
            };

            xhr.onerror = () => reject({ message: 'Network error' });
            xhr.send(formData);
        });
    },

    /**
     * Core request method.
     *
     * @param {string} url     Request URL
     * @param {Object} options Fetch options
     * @returns {Promise<Object>}
     */
    async request(url, options = {}) {
        const isFormData = options.body instanceof FormData;

        const config = {
            ...options,
            headers: {
                ...this.getHeaders(!isFormData),
                ...(options.headers || {}),
            },
            credentials: 'same-origin',
        };

        try {
            const response = await fetch(url, config);

            // Handle non-JSON responses
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                if (!response.ok) {
                    throw { message: `HTTP error ${response.status}`, status: response.status };
                }
                return { success: true, data: await response.text() };
            }

            const data = await response.json();

            if (!response.ok) {
                // Show validation errors
                if (response.status === 422 && data.errors) {
                    this.showValidationErrors(data.errors);
                }

                // Handle session expiry
                if (response.status === 401 || response.status === 419) {
                    SLMS.toast('Your session has expired. Please log in again.', 'warning');
                    setTimeout(() => window.location.reload(), 2000);
                    return data;
                }

                throw data;
            }

            return data;

        } catch (error) {
            if (error.message === 'Failed to fetch') {
                SLMS.toast('Network error. Please check your connection.', 'error');
            }
            throw error;
        }
    },

    /**
     * Display validation errors on form fields.
     *
     * @param {Object} errors Validation errors object
     */
    showValidationErrors(errors) {
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });

        // Display new errors
        for (const [field, messages] of Object.entries(errors)) {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');

                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = Array.isArray(messages) ? messages[0] : messages;
                input.parentNode.appendChild(feedback);
            }
        }
    },

    /**
     * Show a loading state on a button.
     *
     * @param {HTMLElement} button The button element
     * @param {boolean} loading Whether to show loading
     */
    setLoading(button, loading = true) {
        if (loading) {
            button.dataset.originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner spinner-sm me-2"></span> Processing...';
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || button.innerHTML;
        }
    }
};

// Make Ajax globally accessible
window.Ajax = Ajax;
