@props([
    'route' => [
        'uri' => '',
        'method' => 'GET',
        'route_params' => [],
        'query_params' => []
    ]
])

<div x-data="demoModal"
     x-init="init"
     data-uri="{{ $route['uri'] ?? '' }}"
     data-method="{{ strtolower(explode('|', $route['method'] ?? 'GET')[0]) }}"
     x-cloak>

    {{-- Trigger button - Updated with better color --}}
    <button @click="open = true" class="w-full px-4 py-3 text-center rounded font-medium bg-[#0A2240] text-white hover:bg-[#143462] transition">
        Try {{ $route['method'] ?? 'GET' }} {{ $route['uri'] ?? '/api/endpoint' }}
    </button>

    {{-- Modal dialog --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">

            {{-- Backdrop --}}
            <div x-show="open" @click="closeModal" class="fixed inset-0 bg-black/70 modal-backdrop"></div>

            {{-- Modal panel --}}
            <div x-show="open" @click.outside="closeModal"
                 class="relative max-w-xl w-full bg-white rounded-lg shadow-lg overflow-hidden z-10">

                {{-- Modal header --}}
                <div class="bg-gray-50 px-6 py-3 flex justify-between items-center border-b">
                    <h3 class="text-lg font-medium text-gray-900">
                        Try API Endpoint
                    </h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal body --}}
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            API Endpoint
                        </label>
                        <div class="flex items-center p-2 bg-gray-100 rounded">
                            <span class="px-2 py-1 text-xs font-semibold rounded mr-2 uppercase"
                                  :class="{
                                    'bg-blue-100 text-blue-800': method === 'get',
                                    'bg-green-100 text-green-800': method === 'post',
                                    'bg-yellow-100 text-yellow-800': method === 'put',
                                    'bg-red-100 text-red-800': method === 'delete'
                                  }">
                                <span x-text="method.toUpperCase()"></span>
                            </span>
                            <code class="text-sm" x-text="baseUrl"></code><code class="text-sm">/</code><code class="text-sm font-bold" x-text="uri"></code>
                        </div>
                    </div>

                    {{-- Endpoint-specific parameters --}}
                    <template x-if="needsUrlParam">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span x-text="paramLabel"></span>
                            </label>
                            <input type="text" x-model="params.url" :placeholder="paramPlaceholder"
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </template>

                    {{-- Domain input for SSL inspection --}}
                    <template x-if="uri.includes('inspect-ssl')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Domain to inspect
                            </label>
                            <input type="text" x-model="params.domain" placeholder="example.com"
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </template>

                    {{-- URL input for Security Headers inspection --}}
                    <template x-if="uri.includes('inspect-headers') || uri.includes('security-headers')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                URL to inspect
                            </label>
                            <input type="text" x-model="params.url_to_check" placeholder="https://example.com"
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </template>

                    {{-- Email input for Email inspection --}}
                    <template x-if="uri.includes('inspect-email')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email to inspect
                            </label>
                            <input type="email" x-model="params.email" placeholder="example@domain.com"
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </template>

                    {{-- Special handling for user-agent inspector --}}
                    <template x-if="uri.includes('inspect-user-agent')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                User Agent String
                            </label>
                            <input type="text" x-model="params.user_agent" placeholder="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36..."
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </template>

                    {{-- Special handling for HTML to PDF converter --}}
                    <template x-if="uri.includes('html-to-pdf')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                HTML Content
                            </label>
                            <textarea x-model="params.html" placeholder="<div>Your HTML content here</div>"
                                      class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                      rows="5"></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <div class="text-xs text-gray-500">
                                    Note: Do not use file:// or file:/ URLs in your HTML
                                </div>
                                <button @click="insertSampleHtml"
                                        class="text-xs text-blue-600 hover:text-blue-800 px-2 py-1 rounded border border-blue-200 hover:bg-blue-50">
                                    Insert Sample HTML
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Sandbox token info with expiration state --}}
                    <div class="mb-4 p-3 bg-gray-50 rounded-md">
                        <div class="flex items-center">
                            <div class="w-full">
                                <span class="text-sm font-medium text-gray-700">API Usage</span>
                                <div x-show="tokenInfo" class="text-xs mt-1">
                                    <span x-text="`${tokenInfo.remaining_calls || 0} calls remaining`" class="font-medium"></span>
                                    <template x-if="tokenInfo.expires_at">
                                        <span>
                                            <span x-text="` • Expires ${formatExpiryTime(tokenInfo.expires_at)}`"
                                                  :class="isExpired(tokenInfo.expires_at) ? 'text-red-500' : 'text-gray-500'"></span>
                                            <template x-if="isExpired(tokenInfo.expires_at)">
                                                <span class="text-red-500 font-medium"> (expired)</span>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                                <div x-show="!tokenInfo" class="text-xs mt-1 text-gray-500">
                                    No token information available
                                </div>
                                <div class="text-xs mt-1 text-gray-500">
                                    Limit: 1 token per day with 25 requests per token
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit button --}}
                    <div class="mt-6">
                        <button @click="submit"
                                :disabled="loading || (needsUrlParam && !hasRequiredParams) || (!isHealthOrReadinessEndpoint && !hasValidToken)"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed flex justify-center">
                            <span x-show="!loading">Send Request</span>
                            <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>

                        <div x-show="!hasValidToken && tokenInfo && !isHealthOrReadinessEndpoint" class="mt-2 text-center text-xs text-red-500">
                            Your token has expired or quota is exhausted. Please try again tomorrow.
                        </div>
                    </div>
                </div>

                {{-- Response area --}}
                <div class="border-t border-gray-200">
                    <div class="flex justify-between p-4 bg-gray-50">
                        <h4 class="text-sm font-medium text-gray-700">Response</h4>
                        <template x-if="hasPdfResponse">
                            <button @click="openPdfInNewWindow" class="text-xs text-blue-600 hover:text-blue-800 px-2 py-1 rounded border border-blue-200 hover:bg-blue-50">
                                Open in New Tab
                            </button>
                        </template>
                    </div>

                    <div x-ref="responseArea" class="border-t border-gray-200 bg-gray-50 rounded-md overflow-hidden response-area" style="height: 350px;">
                        <div x-show="!response && !hasPdfResponse" class="flex items-center justify-center h-full text-gray-500">
                            <span>Send a request to see the response</span>
                        </div>

                        <div x-show="response && !hasPdfResponse" class="p-4 overflow-auto h-full">
                            <pre x-text="typeof response === 'string' ? response : JSON.stringify(response, null, 2)" class="text-sm font-mono"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('demoModal', () => ({
            open: false,
            baseUrl: window.location.origin,
            uri: '',
            method: 'get',
            loading: false,
            response: null,
            params: {},
            tokenInfo: null,
            hasPdfResponse: false,
            pdfUrl: null,
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

            init() {
                const el = this.$el;
                this.uri = el.dataset.uri || '';
                this.method = el.dataset.method || 'get';
                this.checkTokenStatus();
            },

            closeModal() {
                this.open = false;

                // Clean up any iframe if the modal is closed
                if (this.hasPdfResponse && this.pdfUrl) {
                    this.cleanupPdfViewer();
                }
            },

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

            isExpired(expiryDate) {
                if (!expiryDate) return false;
                const expiry = new Date(expiryDate);
                return expiry < new Date();
            },

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
                return this.uri.includes(':') || this.uri.includes('{') && this.uri.includes('}');
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

            insertSampleHtml() {
                this.params.html = this.sampleHtml;
            },

            formatExpiryTime(expiryDate) {
                if (!expiryDate) return 'unknown';
                const date = new Date(expiryDate);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            async checkTokenStatus() {
                try {
                    const response = await fetch('/sandbox/token/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            token: localStorage.getItem('sandbox_token')
                        })
                    });

                    const data = await response.json();

                    // Store token info regardless of validity for display purposes
                    if (data.remaining_calls !== undefined) {
                        this.tokenInfo = {
                            remaining_calls: data.remaining_calls,
                            expires_at: data.expires_at,
                            expired: data.expired || false,
                            quota_exceeded: data.quota_exceeded || false
                        };
                    }

                    // If no valid token exists and we're not on a health/readiness endpoint, try to get a new one automatically
                    if (!data.valid && !localStorage.getItem('sandbox_token') && !this.isHealthOrReadinessEndpoint) {
                        await this.getNewToken();
                    }
                } catch (error) {
                    console.error('Error checking token status:', error);
                }
            },

            async getNewToken() {
                try {
                    const response = await fetch('/sandbox/token/create', {
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
                            expires_at: data.expires_at,
                            expired: false,
                            quota_exceeded: false
                        };
                    } else if (data.token_limit_reached) {
                        // Handle rate limiting
                        this.tokenInfo = {
                            remaining_calls: 0,
                            expired: false,
                            quota_exceeded: true
                        };
                    }
                } catch (error) {
                    console.error('Error getting new token:', error);
                }
            },

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

                // Handle special case for user-agent inspector
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

                    // Add body for POST/PUT/PATCH requests
                    if (['post', 'put', 'patch'].includes(this.method)) {
                        let body = {};

                        // Add appropriate parameters based on endpoint
                        if (this.uri.includes('html-to-pdf')) {
                            body.html = this.params.html || '';
                        }

                        if (this.uri.includes('inspect-ssl')) {
                            body.domain = this.params.domain || '';
                        }

                        if (this.uri.includes('inspect-headers') || this.uri.includes('security-headers')) {
                            body.url = this.params.url_to_check || '';
                        }

                        if (this.uri.includes('inspect-email')) {
                            body.email = this.params.email || '';
                        }

                        // Add the body to the request if not empty
                        if (Object.keys(body).length > 0) {
                            requestOptions.body = JSON.stringify(body);
                        }
                    } else if (this.method === 'get') {
                        // Handle GET parameters
                        const queryParams = [];

                        if (this.uri.includes('inspect-ssl') && this.params.domain) {
                            queryParams.push(`domain=${encodeURIComponent(this.params.domain)}`);
                        }

                        if ((this.uri.includes('inspect-headers') || this.uri.includes('security-headers')) && this.params.url_to_check) {
                            queryParams.push(`url=${encodeURIComponent(this.params.url_to_check)}`);
                        }

                        if (this.uri.includes('inspect-email') && this.params.email) {
                            queryParams.push(`email=${encodeURIComponent(this.params.email)}`);
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
                    console.error('Error sending request:', error);
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>
