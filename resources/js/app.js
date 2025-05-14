// resources/js/app.js

// Import Alpine.js and other dependencies
import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Register Alpine components before initializing
document.addEventListener('DOMContentLoaded', () => {
    // Register the demoModal component
    Alpine.data('demoModal', function() {
        return {
            open: false,
            token: null,
            params: {},
            response: '{ }',
            responseUrl: null,
            isLoading: false,
            fullscreen: false,

            init() {
                // Get token from localStorage
                this.token = localStorage.getItem('sandbox_token');

                // Migrate legacy token if exists
                if (!this.token && localStorage.getItem('apixies_sandbox')) {
                    this.token = localStorage.getItem('apixies_sandbox');
                    localStorage.setItem('sandbox_token', this.token);
                    localStorage.removeItem('apixies_sandbox');
                }

                // Initialize for debugging
                console.log("Demo modal initialized with token:", this.token);
                console.log("API endpoint:", this.$el.getAttribute('data-uri'));
            },

            getNewToken() {
                this.isLoading = true;
                console.log("Getting new token...");

                fetch('/sandbox/token', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'same-origin'
                })
                    .then(res => res.json())
                    .then(data => {
                        console.log("Token response:", data);
                        if (data.token) {
                            // Store only the token
                            this.token = data.token;
                            localStorage.setItem('sandbox_token', data.token);

                            // Remove legacy token if exists
                            if (localStorage.getItem('apixies_sandbox')) {
                                localStorage.removeItem('apixies_sandbox');
                            }

                            // Clear response data
                            this.response = '{ }';
                            if (this.responseUrl) {
                                URL.revokeObjectURL(this.responseUrl);
                                this.responseUrl = null;
                            }
                        } else {
                            this.response = 'Error getting sandbox token';
                        }
                        this.isLoading = false;
                    })
                    .catch(err => {
                        console.error("Token error:", err);
                        this.response = `Error: ${err.toString()}`;
                        this.isLoading = false;
                    });
            },

            toggleFullscreen() {
                this.fullscreen = !this.fullscreen;

                // Allow time for the DOM to update
                setTimeout(() => {
                    // If entering fullscreen, focus on the response area
                    if (this.fullscreen) {
                        const responseArea = document.querySelector('.response-area');
                        if (responseArea) responseArea.focus();
                    }
                }, 100);
            },

            submit() {
                if (!this.token) {
                    this.response = 'Please get a sandbox token first';
                    return;
                }

                this.isLoading = true;

                // Clean up previous response URL
                if (this.responseUrl) {
                    URL.revokeObjectURL(this.responseUrl);
                    this.responseUrl = null;
                }

                // Get endpoint details from data attributes
                const uri = this.$el.getAttribute('data-uri');
                const method = this.$el.getAttribute('data-method') || 'get';

                console.log("Submitting request to:", uri, "Method:", method);
                console.log("Parameters:", this.params);

                if (!uri) {
                    this.response = "Error: API endpoint URI is not defined";
                    this.isLoading = false;
                    return;
                }

                const isPostMethod = method.toLowerCase() === 'post';

                // Build URL and request options
                const url = uri;  // Don't add a slash prefix since the URI should already have it
                const headers = {
                    'Authorization': `Bearer ${this.token}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                };

                // For POST requests, set Content-Type to application/json
                if (isPostMethod) {
                    headers['Content-Type'] = 'application/json';
                }

                const options = {
                    method: method.toUpperCase(),
                    headers,
                    credentials: 'same-origin'
                };

                // Add body for POST requests
                if (isPostMethod) {
                    options.body = JSON.stringify(this.params);
                }

                // For GET requests, append query params to URL
                const requestUrl = isPostMethod ? url : `${url}${Object.keys(this.params).length > 0 ? '?' + new URLSearchParams(this.params) : ''}`;

                console.log("Final request URL:", requestUrl);

                fetch(requestUrl, options)
                    .then(res => {
                        console.log("Response status:", res.status);

                        // Check for token-related errors
                        if (res.status === 401 || res.status === 429) {
                            return res.json().then(errorData => {
                                // If token expired or quota exhausted, clear token and show message
                                if (errorData.message === 'Sandbox token expired' ||
                                    errorData.message === 'Sandbox quota exhausted') {
                                    localStorage.removeItem('sandbox_token');
                                    this.token = null;
                                    throw new Error(`${errorData.message}. Please get a new token.`);
                                }
                                return { error: errorData.message };
                            });
                        }

                        // Handle other error status codes
                        if (!res.ok) {
                            return res.text().then(text => {
                                try {
                                    return JSON.parse(text);
                                } catch {
                                    return { error: text };
                                }
                            });
                        }

                        // Check content type
                        const contentType = res.headers.get('Content-Type') || '';
                        console.log("Response content type:", contentType);

                        if (contentType.includes('application/pdf')) {
                            // For PDF responses, create a blob URL and use iframe
                            return res.blob().then(blob => {
                                this.responseUrl = URL.createObjectURL(blob);
                                return 'PDF document generated successfully.';
                            });
                        } else {
                            // For JSON or text responses
                            return res.text().then(text => {
                                try {
                                    return JSON.parse(text);
                                } catch {
                                    return text;
                                }
                            });
                        }
                    })
                    .then(data => {
                        console.log("Processed response:", data);
                        if (typeof data === 'string') {
                            this.response = data;
                        } else {
                            this.response = JSON.stringify(data, null, 2);
                        }
                    })
                    .catch(err => {
                        console.error("Request error:", err);
                        this.response = `Error: ${err.toString()}`;
                    })
                    .finally(() => {
                        this.isLoading = false;
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
