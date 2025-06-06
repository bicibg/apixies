// resources/js/app.js

// Import Alpine.js and other dependencies
import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Register Alpine components before initializing
document.addEventListener('DOMContentLoaded', () => {

    toggleHeaderShadow();
    window.addEventListener('scroll', toggleHeaderShadow);

    // Initialize endpoint search functionality
    initializeEndpointSearch();

    // Register the mobile menu component
    Alpine.data('mobileMenu', function() {
        return {
            open: false,

            toggleMenu() {
                this.open = !this.open;

                // Toggle body overflow to prevent scrolling when menu is open
                if (this.open) {
                    document.body.classList.add('overflow-hidden');
                } else {
                    document.body.classList.remove('overflow-hidden');
                }
            },

            closeMenu() {
                this.open = false;
                document.body.classList.remove('overflow-hidden');
            },

            init() {
                // Close menu when pressing ESC key
                this.$watch('open', value => {
                    if (value) {
                        window.addEventListener('keydown', e => {
                            if (e.key === 'Escape') {
                                this.closeMenu();
                            }
                        }, { once: true });
                    }
                });

                // Close menu when window resizes to desktop
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 768 && this.open) {
                        this.closeMenu();
                    }
                });
            }
        };
    });

    // Register the demoModal component
    Alpine.data('demoModal', function() {
        return {
            // State properties
            open: false,
            baseUrl: window.location.origin,
            uri: '',
            method: 'get',
            loading: false,
            response: null,
            params: {},
            routeParams: [],
            queryParams: [],
            tokenInfo: {
                // Initialize with default values to prevent null errors
                remaining_calls: 0,
                expires_at: null,
                expired: false,
                quota_exceeded: false
            },
            hasPdfResponse: false,
            pdfUrl: null,

            // Sample data for different parameter types
            sampleData: {
                // Email-related
                email: 'john.doe@example.com',
                mail: 'john.doe@example.com',

                // User-Agent related
                user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                useragent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',

                // Domain-related
                domain: 'example.com',
                hostname: 'example.com',

                // URL-related
                url: 'https://example.com',
                url_to_check: 'https://example.com',
                website: 'https://example.com',
                link: 'https://example.com',

                // IP-related
                ip: '8.8.8.8',
                ipv4: '8.8.8.8',
                ipv6: '2001:4860:4860::8888',

                // HTML-related is handled separately via the sampleHtml variable
                html: null, // Uses the sampleHtml variable with full formatting

                // Others
                text: 'Sample text for testing',
                number: '42',
                phone: '+1234567890',
                date: '2025-05-19'
            },

            // Lifecycle methods
            init() {
                const el = this.$el;
                this.uri = el.dataset.uri || '';
                this.method = el.dataset.method || 'get';

                // Parse route and query parameters from data attributes
                try {
                    this.routeParams = JSON.parse(el.dataset.routeParams || '[]');
                    this.queryParams = JSON.parse(el.dataset.params || '[]');
                } catch (e) {
                    console.error('Error parsing parameters:', e);
                    this.routeParams = [];
                    this.queryParams = [];
                }

                // Initialize params object with empty values for all parameters
                this.routeParams.forEach(param => {
                    this.params[param] = '';
                });

                this.queryParams.forEach(param => {
                    this.params[param] = '';
                });

                // Special handling for HTML to PDF endpoint
                if (this.uri.includes('html-to-pdf')) {
                    this.params.html = '';
                }
            },

            async openModal() {
                this.open = true;
                // Check token status when modal is opened
                await this.checkTokenStatus();
            },

            closeModal() {
                this.open = false;
                // Clean up any iframe if the modal is closed
                if (this.hasPdfResponse && this.pdfUrl) {
                    this.cleanupPdfViewer();
                }
            },

            // Insert sample data for a parameter based on its name
            insertSample(paramName) {
                // First check if we have an exact match
                if (this.sampleData[paramName]) {
                    this.params[paramName] = this.sampleData[paramName];
                    return;
                }

                // If no exact match, try to intelligently determine the type
                const paramLower = paramName.toLowerCase();

                // Email-related parameters
                if (paramLower.includes('email') || paramLower.includes('mail')) {
                    this.params[paramName] = this.sampleData.email;
                }
                // URL or website related parameters
                else if (paramLower.includes('url') ||
                    paramLower.includes('website') ||
                    paramLower.includes('site') ||
                    paramLower.includes('link')) {
                    this.params[paramName] = this.sampleData.url;
                }
                // IP address related parameters
                else if (paramLower.includes('ip')) {
                    if (paramLower.includes('ipv6')) {
                        this.params[paramName] = this.sampleData.ipv6;
                    } else {
                        this.params[paramName] = this.sampleData.ip;
                    }
                }
                // Domain related parameters
                else if (paramLower.includes('domain') ||
                    paramLower.includes('host') ||
                    paramLower.includes('hostname')) {
                    this.params[paramName] = this.sampleData.domain;
                }
                // User agent related parameters
                else if (paramLower.includes('agent') ||
                    paramLower.includes('browser') ||
                    paramLower.includes('ua')) {
                    this.params[paramName] = this.sampleData.user_agent;
                }
                // HTML related parameters
                else if (paramLower.includes('html') ||
                    paramLower.includes('markup') ||
                    paramLower.includes('content')) {
                    this.params[paramName] = this.sampleHtml;
                }
                // Phone number related parameters
                else if (paramLower.includes('phone') ||
                    paramLower.includes('tel') ||
                    paramLower.includes('mobile')) {
                    this.params[paramName] = this.sampleData.phone;
                }
                // Text related parameters
                else if (paramLower.includes('text') ||
                    paramLower.includes('message') ||
                    paramLower.includes('content')) {
                    this.params[paramName] = this.sampleData.text;
                }
                // Numeric parameters
                else if (paramLower.includes('number') ||
                    paramLower.includes('value') ||
                    paramLower.includes('id') ||
                    paramLower.includes('count')) {
                    this.params[paramName] = this.sampleData.number;
                }
                // Date related parameters
                else if (paramLower.includes('date') ||
                    paramLower.includes('time') ||
                    paramLower.includes('day')) {
                    this.params[paramName] = this.sampleData.date;
                }
                // Default fallback for unknown parameter types
                else {
                    // For unknown parameters, provide a generic sample
                    this.params[paramName] = `sample_${paramName}`;
                }
            },

            // Legacy insertion methods (for backward compatibility)
            insertSampleEmail() {
                this.params.email = this.sampleData.email;
            },

            insertSampleUserAgent() {
                this.params.user_agent = this.sampleData.user_agent;
            },

            insertSampleDomain() {
                this.params.domain = this.sampleData.domain;
            },

            insertSampleUrl() {
                this.params.url_to_check = this.sampleData.url;
            },

            insertSampleIp() {
                this.params.ip = this.sampleData.ip;
            },

            insertSampleHtml() {
                this.params.html = this.sampleHtml;
            },

            // Token management
            async checkTokenStatus() {
                try {
                    const storedToken = localStorage.getItem('sandbox_token');

                    if (!storedToken) {
                        await this.getNewToken();
                        return;
                    }

                    const response = await fetch('/sandbox/token/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            token: storedToken
                        })
                    });

                    const data = await response.json();

                    // Store token info regardless of validity for display purposes
                    if (data.remaining_calls !== undefined) {
                        this.tokenInfo = {
                            remaining_calls: data.remaining_calls,
                            expires_at: data.expires_at || null,
                            expired: data.expired || false,
                            quota_exceeded: data.quota_exceeded || false
                        };
                    }

                    // Get a new token if needed based on server response
                    if (data.needs_new_token || data.expired || !data.valid) {
                        await this.getNewToken();
                    } else if (data.needs_refresh || data.new_day) {
                        await this.getNewToken(); // Will use refresh endpoint since token is still valid
                    }
                } catch (error) {
                    console.error('Error checking token status:', error);
                    await this.getNewToken();
                }
            },

            async getNewToken() {
                try {
                    // Always use create endpoint for expired tokens
                    const storedToken = localStorage.getItem('sandbox_token');
                    let endpoint = '/sandbox/token/create';

                    // Only use refresh endpoint for existing tokens that aren't known to be expired
                    if (storedToken && this.tokenInfo && !this.tokenInfo.expired) {
                        endpoint = '/sandbox/token/refresh';
                    }

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (data.token) {
                        localStorage.setItem('sandbox_token', data.token);

                        // Update token info
                        this.tokenInfo = {
                            remaining_calls: data.remaining_calls || data.quota || 25,
                            expires_at: data.expires_at || null,
                            expired: false,
                            quota_exceeded: false
                        };
                    } else if (data.token_limit_reached) {
                        // Handle rate limiting
                        this.tokenInfo = {
                            remaining_calls: 0,
                            expires_at: null,
                            expired: false,
                            quota_exceeded: true
                        };
                    }
                } catch (error) {
                    console.error('Error getting token:', error);
                    // Ensure tokenInfo has valid defaults even after errors
                    this.tokenInfo = this.tokenInfo || {
                        remaining_calls: 0,
                        expires_at: null,
                        expired: false,
                        quota_exceeded: false
                    };
                }
            },

            // UI helpers
            isExpired(expiryDate) {
                if (!expiryDate) return false;
                const expiry = new Date(expiryDate);
                return expiry < new Date();
            },

            formatExpiryTime(expiryDate) {
                if (!expiryDate) return 'unknown';
                const date = new Date(expiryDate);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            // PDF handling
            cleanupPdfViewer() {
                // Clean up PDF viewer and blob URL when no longer needed
                if (this.pdfUrl) {
                    URL.revokeObjectURL(this.pdfUrl);
                    this.pdfUrl = null;
                }

                this.hasPdfResponse = false;

                // Remove iframe if present
                const responseArea = this.$refs.responseArea;
                if (responseArea) {
                    const iframe = responseArea.querySelector('iframe');
                    if (iframe) {
                        iframe.remove();
                    }
                }
            },

            openPdfInNewWindow() {
                if (this.pdfUrl) {
                    window.open(this.pdfUrl, '_blank');
                }
            },

            // Computed properties
            get isHealthOrReadinessEndpoint() {
                return this.uri === 'api/v1/health' ||
                    this.uri === 'api/v1/ready' ||
                    this.uri === 'health' ||
                    this.uri === 'ready';
            },

            get hasValidToken() {
                // For health and readiness endpoints, no token needed
                if (this.isHealthOrReadinessEndpoint) {
                    return true;
                }

                // Otherwise, check if we have a valid, non-expired token with remaining calls
                return this.tokenInfo &&
                    !this.isExpired(this.tokenInfo.expires_at) &&
                    this.tokenInfo.remaining_calls > 0;
            },

            get hasRouteParams() {
                return this.routeParams && this.routeParams.length > 0;
            },

            get hasRequiredParams() {
                return (this.routeParams && this.routeParams.length > 0) ||
                    (this.queryParams && this.queryParams.length > 0);
            },

            get allRequiredParamsFilled() {
                // Check if all required parameters have values
                let allFilled = true;

                // Check route parameters - these are always required
                this.routeParams.forEach(param => {
                    if (!this.params[param] || this.params[param].trim() === '') {
                        allFilled = false;
                    }
                });

                // For query parameters, check if they're required
                // For now, let's assume all query parameters are required
                // In the future, you could enhance this to mark some as optional in your API config
                this.queryParams.forEach(param => {
                    if (!this.params[param] || this.params[param].trim() === '') {
                        allFilled = false;
                    }
                });

                return allFilled;
            },

            // Sample HTML for PDF testing
            sampleHtml: `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample PDF Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #0066cc;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 30px 0;
        }
        .card {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: white;
        }
        .card h2 {
            margin-top: 0;
            color: #444;
            font-size: 18px;
        }
        .card.primary {
            border-top: 4px solid #0066cc;
        }
        .card.success {
            border-top: 4px solid #28a745;
        }
        .card.warning {
            border-top: 4px solid #ffc107;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Sample Document for PDF Conversion</h1>

    <p>This is an example document that demonstrates HTML to PDF conversion. It includes various HTML elements with styling that should be properly rendered in the PDF output.</p>

    <div class="container">
        <div class="card primary">
            <h2>Card One</h2>
            <p>This is a styled card with a primary color accent.</p>
        </div>
        <div class="card success">
            <h2>Card Two</h2>
            <p>This card has a success color accent for variation.</p>
        </div>
        <div class="card warning">
            <h2>Card Three</h2>
            <p>This one has a warning color to show different styling options.</p>
        </div>
    </div>

    <h2>Data Table Example</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Availability</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Product A</td>
                <td>$19.99</td>
                <td>In Stock</td>
            </tr>
            <tr>
                <td>Product B</td>
                <td>$29.99</td>
                <td>Limited</td>
            </tr>
            <tr>
                <td>Product C</td>
                <td>$39.99</td>
                <td>Out of Stock</td>
            </tr>
            <tr>
                <td>Product D</td>
                <td>$49.99</td>
                <td>In Stock</td>
            </tr>
        </tbody>
    </table>

    <p>The PDF converter should properly render this HTML with all styles and formatting intact.</p>
</body>
</html>`,

            // API request handling
            async submit() {
                this.loading = true;
                this.response = null;

                // Clean up any previous PDF response
                this.cleanupPdfViewer();

                let url = `${this.baseUrl}/${this.uri}`;

                // Replace route parameters if needed
                this.routeParams.forEach(param => {
                    if (this.params[param]) {
                        // Replace both :param and {param} formats
                        url = url.replace(`:${param}`, this.params[param])
                            .replace(`{${param}}`, this.params[param]);
                    }
                });

                const headers = {
                    'Accept': 'application/json',
                    'X-Sandbox-Token': localStorage.getItem('sandbox_token') || ''
                };

                // Only add Content-Type if we're sending a body
                if (['post', 'put', 'patch'].includes(this.method)) {
                    headers['Content-Type'] = 'application/json';
                }

                // Special case for user-agent inspector
                if (this.uri.includes('inspect-user-agent') && this.params.user_agent) {
                    headers['User-Agent'] = this.params.user_agent;
                }

                try {
                    const requestOptions = {
                        method: this.method.toUpperCase(),
                        headers: headers
                    };

                    // Handle different HTTP methods
                    if (['post', 'put', 'patch'].includes(this.method)) {
                        // For request body methods
                        let body = {};

                        // Add all parameters from the params object
                        for (const [key, value] of Object.entries(this.params)) {
                            if (value !== null && value !== undefined && value.toString().trim() !== '') {
                                body[key] = value;
                            }
                        }

                        // Special handling for HTML to PDF
                        if (this.uri.includes('html-to-pdf') && this.params.html) {
                            body.html = this.params.html;
                        }

                        // Add the body to the request if not empty
                        if (Object.keys(body).length > 0) {
                            requestOptions.body = JSON.stringify(body);
                        }
                    } else if (this.method === 'get') {
                        // For GET requests, add query parameters to URL
                        const queryParams = [];

                        // Add all query parameters that have values
                        this.queryParams.forEach(param => {
                            if (this.params[param] && this.params[param].toString().trim() !== '') {
                                queryParams.push(`${param}=${encodeURIComponent(this.params[param])}`);
                            }
                        });

                        if (queryParams.length > 0) {
                            url = `${url}?${queryParams.join('&')}`;
                        }
                    }

                    const response = await fetch(url, requestOptions);

                    // Handle non-JSON responses (like PDF)
                    const contentType = response.headers.get('content-type');

                    if (contentType && contentType.includes('application/pdf')) {
                        // For PDFs, create a blob URL
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);

                        // Store URL for potential download
                        this.pdfUrl = url;
                        this.hasPdfResponse = true;

                        // Clear the existing response data structure
                        this.response = null;

                        // Get response area
                        const responseArea = this.$refs.responseArea;

                        if (responseArea) {
                            // Clear response area
                            while (responseArea.firstChild) {
                                responseArea.removeChild(responseArea.firstChild);
                            }

                            // Create iframe
                            const iframe = document.createElement('iframe');
                            iframe.src = url;
                            iframe.style.width = '100%';
                            iframe.style.height = '100%';
                            iframe.style.border = 'none';

                            // Add PDF viewer
                            responseArea.appendChild(iframe);
                        }
                    } else {
                        // Handle JSON responses
                        const data = await response.json();
                        this.response = data;
                    }

                    // Update token status after request
                    await this.checkTokenStatus();
                } catch (error) {
                    this.response = {
                        error: 'Request failed',
                        message: error.message
                    };
                } finally {
                    this.loading = false;
                }
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

function toggleHeaderShadow() {
    const header = document.querySelector('header');
    if (!header) return;

    if (window.scrollY > 10) {
        header.classList.add('header-shadow');
    } else {
        header.classList.remove('header-shadow');
    }
}

function initializeEndpointSearch() {
    const searchInput = document.getElementById('endpoint-search');
    if (!searchInput) return; // Exit if search input doesn't exist on this page

    const endpointRows = document.querySelectorAll('.endpoint-row');
    const noResults = document.getElementById('no-search-results');
    const categorySections = document.querySelectorAll('.category-section');
    const resetSearch = document.getElementById('reset-search');

    // Add the clear search functionality
    if (resetSearch) {
        resetSearch.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.value = '';
            // Trigger the input event to refresh the search
            searchInput.dispatchEvent(new Event('input'));
            // Focus back on the search input
            searchInput.focus();
        });
    }

    // Add the event listener for search
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let foundAny = false;

        // By default, hide the "no results" message
        if (noResults) {
            noResults.style.display = 'none';
        }

        // If search is empty, show everything and exit early
        if (searchTerm === '') {
            endpointRows.forEach(row => row.style.display = '');
            categorySections.forEach(section => section.style.display = '');
            return;
        }

        // Loop through each category
        categorySections.forEach(category => {
            let categoryHasVisible = false;

            // Find endpoints in this category
            const categoryEndpoints = category.querySelectorAll('.endpoint-row');

            categoryEndpoints.forEach(endpoint => {
                // Get all searchable content from data attributes
                const title = endpoint.dataset.endpointTitle?.toLowerCase() || '';
                const description = endpoint.dataset.endpointDescription?.toLowerCase() || '';
                const uri = endpoint.dataset.endpointUri?.toLowerCase() || '';

                if (title.includes(searchTerm) || description.includes(searchTerm) || uri.includes(searchTerm)) {
                    endpoint.style.display = '';
                    categoryHasVisible = true;
                    foundAny = true;
                } else {
                    endpoint.style.display = 'none';
                }
            });

            // Hide/show category based on whether it has visible endpoints
            category.style.display = categoryHasVisible ? '' : 'none';
        });

        // Show/hide no results message
        if (noResults) {
            noResults.style.display = foundAny ? 'none' : 'block';
        }
    });
}
