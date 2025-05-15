// resources/js/app.js - Simplified Version

// Import Alpine.js and other dependencies
import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Global variable to track token creation
window.lastTokenCreation = 0;

// Register Alpine components before initializing
document.addEventListener('DOMContentLoaded', () => {
    // Register the demoModal component
    Alpine.data('demoModal', function() {
        return {
            open: false,
            loading: false,
            refreshingToken: false,
            baseUrl: window.location.origin,
            method: '',
            uri: '',
            params: {},
            token: null,
            response: null,
            responseUrl: null,
            tokenInfo: {
                remaining_calls: 0,
                expires_at: null
            },

            // Computed properties
            get needsUrlParam() {
                return this.uri.includes('inspect-ssl') ||
                    this.uri.includes('inspect-email') ||
                    this.uri.includes('inspect-headers');
            },

            get paramLabel() {
                if (this.uri.includes('inspect-email')) return 'Email to inspect';
                if (this.uri.includes('inspect-ssl')) return 'Domain to inspect';
                if (this.uri.includes('inspect-headers')) return 'URL to inspect';
                return 'Target URL to inspect';
            },

            get paramPlaceholder() {
                if (this.uri.includes('inspect-email')) return 'user@example.com';
                if (this.uri.includes('inspect-ssl')) return 'example.com';
                return 'https://example.com';
            },

            get hasRequiredParams() {
                if (this.needsUrlParam) {
                    return !!this.params.url;
                }
                if (this.uri.includes('inspect-user-agent')) {
                    return !!this.params.user_agent;
                }
                if (this.uri.includes('html-to-pdf')) {
                    return !!this.params.html;
                }
                return true;
            },

            init() {
                // Initialize component with URI and method
                this.uri = this.$el.getAttribute('data-uri') || '';
                this.method = this.$el.getAttribute('data-method') || 'get';

                // Initialize parameters object
                this.params = {};

                // Check if token exists in localStorage
                this.token = localStorage.getItem('sandbox_token') || '';

                // Default user agent is current browser's user agent if on user-agent endpoint
                if (this.uri.includes('inspect-user-agent')) {
                    this.params.user_agent = navigator.userAgent;
                }

                // Get token info if token exists, or create a new one
                if (this.token) {
                    this.getTokenInfo();
                } else {
                    this.refreshToken();
                }
            },

            formatExpiryTime(isoString) {
                if (!isoString) return 'unknown';

                try {
                    const expiryDate = new Date(isoString);
                    const now = new Date();
                    const diffMins = Math.round((expiryDate - now) / 60000);

                    if (diffMins < 0) return 'expired';
                    if (diffMins < 60) return `in ${diffMins} min`;

                    const hours = Math.floor(diffMins / 60);
                    const mins = diffMins % 60;
                    return `in ${hours}h ${mins}m`;
                } catch (e) {
                    return 'unknown';
                }
            },

            getTokenInfo() {
                if (!this.token) return;

                fetch('/sandbox/token/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({ token: this.token })
                })
                    .then(response => response.ok ? response.json() : Promise.reject('Invalid response'))
                    .then(data => {
                        if (data.valid) {
                            this.tokenInfo = {
                                remaining_calls: data.remaining_calls || 0,
                                expires_at: data.expires_at || null
                            };
                        } else {
                            localStorage.removeItem('sandbox_token');
                            this.token = '';
                            this.tokenInfo = { remaining_calls: 0, expires_at: null };
                            this.refreshToken();
                        }
                    })
                    .catch(() => {
                        this.tokenInfo = { remaining_calls: 0, expires_at: null };
                    });
            },

            refreshToken() {
                // Rate limit token creation
                const now = Date.now();
                if (now - window.lastTokenCreation < 2000) {
                    setTimeout(() => this.refreshToken(), 2000);
                    return;
                }

                window.lastTokenCreation = now;
                this.refreshingToken = true;

                fetch('/sandbox/token/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                })
                    .then(response => response.ok ? response.json() : Promise.reject('Failed to create token'))
                    .then(data => {
                        if (data.token) {
                            localStorage.setItem('sandbox_token', data.token);
                            this.token = data.token;
                            this.getTokenInfo();
                            this.response = {
                                status: "success",
                                message: "Token refreshed successfully",
                                data: { token: data.token }
                            };
                        } else {
                            this.response = {
                                status: "error",
                                message: "Failed to refresh token",
                                error: data.message || "Unknown error"
                            };
                        }
                    })
                    .catch(error => {
                        this.response = {
                            status: "error",
                            message: "Failed to refresh token",
                            error: error.message || "Unknown error"
                        };
                    })
                    .finally(() => {
                        this.refreshingToken = false;
                    });
            },

            submit() {
                // Input validation
                if (!this.uri) {
                    this.response = {
                        status: "error",
                        message: "API endpoint URI is not defined"
                    };
                    return;
                }

                if (this.needsUrlParam && !this.params.url) {
                    this.response = {
                        status: "error",
                        message: `${this.paramLabel} is required`
                    };
                    return;
                }

                if (this.uri.includes('inspect-user-agent') && !this.params.user_agent) {
                    this.response = {
                        status: "error",
                        message: `User agent string is required`
                    };
                    return;
                }

                if (this.uri.includes('html-to-pdf') && !this.params.html) {
                    this.response = {
                        status: "error",
                        message: `HTML content is required`
                    };
                    return;
                }

                this.loading = true;
                this.response = null;

                // Clean up previous response URL
                if (this.responseUrl) {
                    URL.revokeObjectURL(this.responseUrl);
                    this.responseUrl = null;
                }

                // Build request URL and params
                let requestUrl = this.uri;
                if (!requestUrl.startsWith('/') && !requestUrl.startsWith('http')) {
                    requestUrl = '/' + requestUrl;
                }

                // For GET requests with URL parameter
                if (this.method === 'get') {
                    const separator = requestUrl.includes('?') ? '&' : '?';

                    if (this.needsUrlParam && this.params.url) {
                        let paramName = 'url';
                        if (this.uri.includes('inspect-email')) paramName = 'email';
                        if (this.uri.includes('inspect-ssl')) paramName = 'domain';

                        requestUrl += `${separator}${paramName}=${encodeURIComponent(this.params.url)}`;
                    } else if (this.uri.includes('inspect-user-agent') && this.params.user_agent) {
                        requestUrl += `${separator}user_agent=${encodeURIComponent(this.params.user_agent)}`;
                    }
                }

                // For POST requests
                let requestBody = null;
                if (this.method === 'post') {
                    if (this.uri.includes('html-to-pdf') && this.params.html) {
                        requestBody = { html: this.params.html };
                    } else if (this.needsUrlParam && this.params.url) {
                        let bodyParam = 'url';
                        if (this.uri.includes('inspect-email')) bodyParam = 'email';
                        if (this.uri.includes('inspect-ssl')) bodyParam = 'domain';
                        requestBody = { [bodyParam]: this.params.url };
                    } else if (this.uri.includes('inspect-user-agent') && this.params.user_agent) {
                        requestBody = { user_agent: this.params.user_agent };
                    }
                }

                // Make the API request
                fetch(requestUrl, {
                    method: this.method.toUpperCase(),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Sandbox-Token': this.token || '',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: requestBody ? JSON.stringify(requestBody) : null
                })
                    .then(response => {
                        // Handle token-related errors
                        if (response.status === 401 || response.status === 429) {
                            return response.json().then(errorData => {
                                if (errorData.message === 'Sandbox token expired' ||
                                    errorData.message === 'Sandbox quota exhausted') {
                                    this.getTokenInfo();
                                }
                                throw new Error(errorData.message || 'Authentication error');
                            });
                        }

                        const contentType = response.headers.get('content-type') || '';

                        if (contentType.includes('application/json')) {
                            return response.json().then(data => ({
                                status: response.status,
                                ok: response.ok,
                                data: data
                            }));
                        } else if (contentType.includes('application/pdf')) {
                            return response.blob().then(blob => {
                                this.responseUrl = URL.createObjectURL(blob);
                                return {
                                    status: response.status,
                                    ok: response.ok,
                                    data: {
                                        message: "PDF generated successfully",
                                        pdf_url: this.responseUrl,
                                        type: "pdf"
                                    }
                                };
                            });
                        } else {
                            return response.text().then(text => ({
                                status: response.status,
                                ok: response.ok,
                                data: { message: text }
                            }));
                        }
                    })
                    .then(({ status, ok, data }) => {
                        if (ok) {
                            this.response = {
                                status: "success",
                                message: "Request successful",
                                http_status: status,
                                data: data
                            };
                            this.getTokenInfo();

                            // Open PDF in new tab if applicable
                            if (data?.type === "pdf") {
                                window.open(this.responseUrl, '_blank');
                            }
                        } else {
                            this.response = {
                                status: "error",
                                message: "Request failed",
                                http_status: status,
                                error: data.message || "Unknown error"
                            };
                        }
                    })
                    .catch(error => {
                        this.response = {
                            status: "error",
                            message: "Request failed",
                            error: error.message || "Unknown error"
                        };
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            closeModal() {
                if (this.responseUrl) {
                    URL.revokeObjectURL(this.responseUrl);
                    this.responseUrl = null;
                }
                this.open = false;
            }
        };
    });

    // Register the suggestModal component if it exists
    if (document.querySelector('.suggest-modal-trigger')) {
        Alpine.data('suggestModal', function() {
            return {
                open: false,

                init() {
                    // Initialize if needed
                },

                openModal() {
                    this.open = true;
                },

                closeModal() {
                    this.open = false;
                }
            };
        });
    }

    // Setup copy functionality for API tokens
    const copyButton = document.querySelector('.copy-token-btn');
    if (copyButton) {
        copyButton.addEventListener('click', () => {
            const tokenElement = document.querySelector('.token-display');
            if (!tokenElement) return;

            navigator.clipboard.writeText(tokenElement.textContent.trim())
                .then(() => {
                    const originalText = copyButton.textContent;
                    copyButton.textContent = 'Copied!';
                    setTimeout(() => {
                        copyButton.textContent = originalText;
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy text: ', err);
                });
        });
    }

    // Start Alpine
    Alpine.start();
});
