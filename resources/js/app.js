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

        // Lifecycle methods
        init() {
            const el = this.$el;
            this.uri = el.dataset.uri || '';
            this.method = el.dataset.method || 'get';

            // Get query parameters from dataset
            try {
                if (el.dataset.queryParams) {
                    this.queryParams = JSON.parse(el.dataset.queryParams);
                }
            } catch (e) {
                console.error('Error parsing query params:', e);
                this.queryParams = [];
            }

            // Initialize params object with each queryParam
            this.params = {};
            this.queryParams.forEach(param => {
                this.params[param] = '';
            });
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

        // Insert sample data based on parameter type
        insertSample(paramName) {
            const sampleData = {
                'email': 'john.doe@example.com',
                'user_agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'domain': 'example.com',
                'url': 'https://example.com',
                'url_to_check': 'https://example.com',
                'ip': '8.8.8.8',
                'html': this.sampleHtml
            };

            // If we have a sample for this param, use it
            if (sampleData[paramName]) {
                this.params[paramName] = sampleData[paramName];
            } else {
                // Default behavior for unknown parameters - use a generic value
                this.params[paramName] = `sample_${paramName}_value`;
            }
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

        insertSampleHtml() {
            this.params.html = this.sampleHtml;
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

        get needsUrlParam() {
            return this.uri.includes(':') || (this.uri.includes('{') && this.uri.includes('}'));
        },

        get paramLabel() {
            // Extract parameter name from URI
            const match = this.uri.match(/\:([a-zA-Z0-9_]+)/) || this.uri.match(/\{([a-zA-Z0-9_]+)\}/);
            return match ? `${match[1]} parameter` : 'Parameter';
        },

        get paramPlaceholder() {
            const match = this.uri.match(/\:([a-zA-Z0-9_]+)/) || this.uri.match(/\{([a-zA-Z0-9_]+)\}/);
            return match ? `Enter ${match[1]} value` : 'Enter parameter value';
        },

        get hasRequiredParams() {
            return this.params.url && this.params.url.trim() !== '';
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
    <p>This is a sample document for PDF conversion...</p>
    <!-- Rest of HTML -->
</body>
</html>`,

        // API request handling
        async submit() {
            this.loading = true;
            this.response = null;

            // Clean up any previous PDF response
            this.cleanupPdfViewer();

            let url = `${this.baseUrl}/${this.uri}`;

            // Replace URL parameters if needed
            if (this.needsUrlParam && this.params.url) {
                url = url.replace(/\:([a-zA-Z0-9_]+)|\{([a-zA-Z0-9_]+)\}/, this.params.url);
            }

            const headers = {
                'Accept': 'application/json',
                'X-API-Key': localStorage.getItem('sandbox_token') || ''
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

                // Add body for POST/PUT/PATCH requests
                if (['post', 'put', 'patch'].includes(this.method)) {
                    // Filter out empty parameters
                    const bodyParams = {};
                    for (const [key, value] of Object.entries(this.params)) {
                        if (value && key !== 'url') {
                            bodyParams[key] = value;
                        }
                    }

                    if (Object.keys(bodyParams).length > 0) {
                        requestOptions.body = JSON.stringify(bodyParams);
                    }
                } else if (this.method === 'get') {
                    // Handle GET parameters - build query string from all params
                    const queryParams = [];

                    for (const [key, value] of Object.entries(this.params)) {
                        if (value && key !== 'url') {
                            queryParams.push(`${key}=${encodeURIComponent(value)}`);
                        }
                    }

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
