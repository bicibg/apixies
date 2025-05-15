// resources/js/app.js

// Import Alpine.js and other dependencies
import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Register Alpine components before initializing
document.addEventListener('DOMContentLoaded', () => {
    // Register the demoModal component
    // Inside the Alpine.data('demoModal') function in app.js
    Alpine.data('demoModal', function() {
        return {
            open: false,
            fullscreen: false,
            loading: false,
            refreshingToken: false,
            baseUrl: window.location.origin,
            method: '',
            uri: '',
            params: {},
            token: '',
            response: null,
            responseUrl: null,
            tokenInfo: null,

            // Computed properties
            get needsUrlParam() {
                return this.uri.includes('inspect') ||
                    this.uri.includes('email') ||
                    this.uri.includes('ssl') ||
                    this.uri.includes('headers');
            },

            get paramLabel() {
                if (this.uri.includes('email')) return 'Email to inspect';
                if (this.uri.includes('ssl')) return 'Domain to inspect';
                if (this.uri.includes('headers')) return 'URL to inspect';
                return 'Target URL to inspect';
            },

            get paramPlaceholder() {
                if (this.uri.includes('email')) return 'user@example.com';
                if (this.uri.includes('ssl')) return 'example.com';
                return 'https://example.com';
            },

            get hasRequiredParams() {
                if (this.needsUrlParam) {
                    return !!this.params.url;
                }
                return true;
            },

            init() {
                // Initialize component with URI and method
                this.uri = this.$el.getAttribute('data-uri') || '';
                this.method = this.$el.getAttribute('data-method') || 'get';

                // Check if token already exists in localStorage
                this.token = localStorage.getItem('sandbox_token');

                // Migrate legacy token if exists
                if (!this.token && localStorage.getItem('apixies_sandbox')) {
                    this.token = localStorage.getItem('apixies_sandbox');
                    localStorage.setItem('sandbox_token', this.token);
                    localStorage.removeItem('apixies_sandbox');
                }

                // Initialize parameters object
                this.params = {};

                // Get token info
                if (this.token) {
                    this.getTokenInfo();
                }

                // Initialize for debugging
                console.log("Demo modal initialized with token:", this.token);
                console.log("API endpoint:", this.uri);
            },

            formatExpiryTime(isoString) {
                try {
                    const expiryDate = new Date(isoString);
                    const now = new Date();

                    // Calculate time difference in minutes
                    const diffMs = expiryDate - now;
                    const diffMins = Math.round(diffMs / 60000);

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
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            this.tokenInfo = {
                                remaining_calls: data.remaining_calls,
                                expires_at: data.expires_at
                            };
                        } else {
                            // Token is invalid, clear it
                            localStorage.removeItem('sandbox_token');
                            this.token = null;
                            this.tokenInfo = null;
                        }
                    })
                    .catch(error => {
                        console.error('Error checking token info:', error);
                        this.tokenInfo = null;
                    });
            },

            refreshToken() {
                this.refreshingToken = true;

                fetch('/sandbox/token/create', {  // Changed from /refresh to /create
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                })
                    .then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                    })
                    .then(data => {
                        if (data.token) {
                            localStorage.setItem('sandbox_token', data.token);
                            this.token = data.token;

                            // Remove legacy key if exists
                            if (localStorage.getItem('apixies_sandbox')) {
                                localStorage.removeItem('apixies_sandbox');
                            }

                            // Update token info
                            this.getTokenInfo();

                            // Show success message
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
                        console.error('Error refreshing token:', error);
                        this.response = {
                            status: "error",
                            message: "Failed to refresh token",
                            error: error.message
                        };
                    })
                    .finally(() => {
                        this.refreshingToken = false;
                    });
            },

            toggleFullscreen() {
                this.fullscreen = !this.fullscreen;
            },

            submit() {
                if (!this.uri) {
                    this.response = {
                        status: "error",
                        message: "API endpoint URI is not defined",
                        error: "The URI for this API endpoint is missing. Please check the configuration."
                    };
                    return;
                }

                // Check if URL parameter is required
                if (this.needsUrlParam && !this.params.url) {
                    this.response = {
                        status: "error",
                        message: `${this.paramLabel} is required`,
                        error: `Please provide a ${this.paramLabel.toLowerCase()}`
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
                let requestParams = {};

                // For GET requests with URL parameter
                if (this.method === 'get' && this.params.url) {
                    // Check if the URI already has query parameters
                    const separator = requestUrl.includes('?') ? '&' : '?';

                    // Add the appropriate parameter based on endpoint type
                    let paramName = 'url';
                    if (this.uri.includes('email')) paramName = 'email';
                    if (this.uri.includes('ssl')) paramName = 'domain';

                    requestUrl += `${separator}${paramName}=${encodeURIComponent(this.params.url)}`;
                }

                // For POST requests
                let requestBody = null;
                if (this.method === 'post' && this.params.url) {
                    // Create the appropriate request body based on endpoint type
                    let bodyParam = 'url';
                    if (this.uri.includes('email')) bodyParam = 'email';
                    if (this.uri.includes('ssl')) bodyParam = 'domain';
                    if (this.uri.includes('html-to-pdf')) bodyParam = 'html';

                    requestBody = { [bodyParam]: this.params.url };
                }

                // Make the API request
                fetch(requestUrl, {
                    method: this.method.toUpperCase(),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Sandbox-Token': this.token || ''
                    },
                    body: requestBody ? JSON.stringify(requestBody) : null
                })
                    .then(response => {
                        // Handle token-related errors
                        if (response.status === 401 || response.status === 429) {
                            return response.json().then(errorData => {
                                // If token expired or quota exhausted, update token info
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

                            // Update token info after successful request
                            this.getTokenInfo();

                            // Special handling for PDF responses
                            if (data?.type === "pdf") {
                                // Open PDF in new tab
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
                        console.error('API request error:', error);
                        this.response = {
                            status: "error",
                            message: "Request failed",
                            error: error.message
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
                this.fullscreen = false;
                this.open = false;
            }
        };
    });

    // Other components or initialization code
    setupLogoutForm();
    initApiDocumentationTabs();
    initApiEndpointSearch();
    setupCopyApiToken();

    // Start Alpine
    Alpine.start();
});

/**
 * Handle logout form submission
 */
function setupLogoutForm() {
    const logoutForm = document.querySelector('form[action*="logout"]');
    if (!logoutForm) return;

    logoutForm.addEventListener('submit', () => {
        localStorage.removeItem('APIToken');
    });
}

/**
 * Set up copy functionality for API tokens
 */
function setupCopyApiToken() {
    const copyButton = document.querySelector('.copy-token-btn');
    if (!copyButton) return;

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

/**
 * Set up tab functionality for API documentation
 */
function initApiDocumentationTabs() {
    // Don't initialize tabs on the single endpoint page
    if (document.querySelector('.tab-nav')) {
        // Skip the docs/show.blade.php page which has its own tab initialization
        const endpointDetailPage = document.querySelector('h1 + p + div .method-badge');
        if (endpointDetailPage) {
            console.log("Skipping tab initialization on endpoint detail page");
            return;
        }
    }

    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (!tabButtons.length) return;

    // Function to activate a given tab
    function activateTab(button) {
        // Deactivate all tabs
        tabButtons.forEach(btn => {
            btn.classList.remove('active', 'text-[#0A2240]', 'border-b-2', 'border-[#0A2240]');
            btn.classList.add('text-gray-500');
        });
        // Hide all panes
        tabContents.forEach(content => {
            content.classList.add('hidden');
        });

        // Activate this tab
        button.classList.add('active', 'text-[#0A2240]', 'border-b-2', 'border-[#0A2240]');
        button.classList.remove('text-gray-500');

        // Show its pane
        const tabId = button.dataset.tab;
        const pane = document.getElementById(tabId);
        if (pane) {
            pane.classList.remove('hidden');
        }
    }

    // Bind click handlers
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            activateTab(button);
        });
    });

    // On load, activate the first tab
    activateTab(tabButtons[0]);
}

/**
 * Set up search functionality for API endpoints
 */
function initApiEndpointSearch() {
    const searchInput = document.getElementById('endpoint-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('.endpoint-row');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });

        const noResults = document.getElementById('no-search-results');
        if (noResults) {
            const anyVisible = [...rows].some(r => r.style.display !== 'none');
            if (query && !anyVisible) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }
    });

    // Clear button (if you have one)
    const clearButton = document.getElementById('clear-search');
    if (clearButton) {
        clearButton.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        });
    }
}

// Initialize sandbox token on page load
document.addEventListener('alpine:initialized', () => {
    console.log("Alpine initialized - checking sandbox token");

    // Check if we have a token
    let token = localStorage.getItem('sandbox_token');

    // If legacy token exists but not the standard one, migrate it
    if (!token && localStorage.getItem('apixies_sandbox')) {
        token = localStorage.getItem('apixies_sandbox');
        localStorage.setItem('sandbox_token', token);
        localStorage.removeItem('apixies_sandbox');
    }

    console.log("Sandbox token status:", token ? "Found" : "Not found");
});
