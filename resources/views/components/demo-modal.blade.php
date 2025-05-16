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

    {{-- Trigger button --}}
    <button @click="openModal" class="w-full px-4 py-3 text-center rounded font-medium bg-navy text-white hover:bg-[#143462] transition">
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
                    {{-- API Registration Banner --}}
                    <div class="mb-5 bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-start space-x-3">
                        <div class="text-blue-500 flex-shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-xs text-blue-800">
                            <p class="font-medium">This is a limited sandbox environment.</p>
                            <p class="mt-1">For production use with higher rate limits, <a href="{{ route('register') }}" class="font-semibold underline hover:text-blue-600">register for a free account</a> and get your own API key.</p>
                        </div>
                    </div>

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
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Sandbox API Usage</span>
                                    <a href="{{ route('register') }}" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                        <span>Get full API access</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                                <div x-show="tokenInfo !== null" class="text-xs mt-1">
                                    <span x-text="`${tokenInfo?.remaining_calls || 0} calls remaining`"
                                          :class="tokenInfo && isExpired(tokenInfo.expires_at) ? 'text-red-500' : 'font-medium'"></span>
                                    <template x-if="tokenInfo && tokenInfo.expires_at">
                                        <span>
                                            <span x-text="` • Expires ${formatExpiryTime(tokenInfo.expires_at)}`"
                                                  :class="tokenInfo && isExpired(tokenInfo.expires_at) ? 'text-red-500' : 'text-gray-500'"></span>
                                            <template x-if="tokenInfo && isExpired(tokenInfo.expires_at)">
                                                <span class="text-red-500 font-medium"> (expired)</span>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                                <div x-show="!tokenInfo" class="text-xs mt-1 text-gray-500">
                                    No token information available
                                </div>
                                <div class="text-xs mt-1 text-gray-500">
                                    Sandbox limit: 1 token per day with 25 requests per token
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

                        <div x-show="!hasValidToken && tokenInfo && !isHealthOrReadinessEndpoint" class="mt-2 text-center">
                            <div x-show="tokenInfo && tokenInfo.expired" class="text-xs text-red-500">Your session has expired. A new token will be created automatically.</div>
                            <div x-show="tokenInfo && tokenInfo.quota_exceeded" class="space-y-2">
                                <p class="text-xs text-red-500">Your daily Sandbox API quota has been exhausted. Please try again tomorrow.</p>
                                <p class="text-xs text-gray-600">Need unlimited access? <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-medium">Register for free</a> and get your own API key with higher limits.</p>
                            </div>
                            <div x-show="tokenInfo && !tokenInfo.expired && !tokenInfo.quota_exceeded" class="text-xs text-red-500">Unable to validate your API token.</div>
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
