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
    <button @click="openModal" class="w-full px-4 py-3 text-center rounded font-medium bg-navy text-white hover:bg-navy-light transition">
        Try {{ $route['method'] ?? 'GET' }} {{ $route['uri'] ?? '/api/endpoint' }}
    </button>

    {{-- Modal dialog --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" style="backdrop-filter: blur(4px);">
        <div class="flex items-center justify-center min-h-screen p-4">

            {{-- Backdrop --}}
            <div x-show="open" @click="closeModal" class="fixed inset-0 bg-navy/60 modal-backdrop"></div>

            {{-- Modal panel --}}
            <div x-show="open" @click.outside="closeModal"
                 class="relative max-w-xl w-full bg-white rounded-lg shadow-xl overflow-hidden z-10 border border-blue-100"
                 style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">

                {{-- Modal header --}}
                <div class="bg-gradient-to-r from-navy to-teal px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white">
                        Try API Endpoint
                    </h3>
                    <button @click="closeModal" class="text-white/80 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal body --}}
                <div class="p-6">
                    {{-- API Registration Banner --}}
                    <div class="mb-5 bg-blue-50 border-l-4 border-teal rounded-r-lg p-4 flex items-start space-x-3">
                        <div class="text-teal flex-shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-sm text-navy">
                            <p class="font-medium">This is a limited sandbox environment.</p>
                            <p class="mt-1">For full, unlimited API access, <a href="{{ route('register') }}" class="font-semibold text-teal underline hover:text-teal-700">register for a free account</a> during our beta period.</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            API Endpoint
                        </label>
                        <div class="flex items-center p-2 bg-blue-50 rounded">
                            <span class="px-2 py-1 text-xs font-semibold rounded mr-2 uppercase"
                                  :class="{
                                    'bg-info-100 text-info-700': method === 'get',
                                    'bg-success-100 text-success-700': method === 'post',
                                    'bg-warning-100 text-warning-700': method === 'put',
                                    'bg-danger-100 text-danger-700': method === 'delete'
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
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-teal focus:border-teal">
                        </div>
                    </template>

                    {{-- Domain input for SSL inspection --}}
                    <template x-if="uri.includes('inspect-ssl')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Domain to inspect
                            </label>
                            <div class="flex space-x-2">
                                <input type="text" x-model="params.domain" placeholder="example.com"
                                       class="flex-1 p-2 border border-gray-300 rounded-md focus:ring-teal focus:border-teal">
                                <button @click="insertSampleDomain"
                                        class="px-3 py-2 text-xs text-teal hover:text-teal-700 rounded border border-teal-200 hover:bg-blue-50 whitespace-nowrap">
                                    Insert Sample
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- URL input for Security Headers inspection --}}
                    <template x-if="uri.includes('inspect-headers') || uri.includes('security-headers')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                URL to inspect
                            </label>
                            <div class="flex space-x-2">
                                <input type="text" x-model="params.url_to_check" placeholder="https://example.com"
                                       class="flex-1 p-2 border border-gray-300 rounded-md focus:ring-teal focus:border-teal">
                                <button @click="insertSampleUrl"
                                        class="px-3 py-2 text-xs text-teal hover:text-teal-700 rounded border border-teal-200 hover:bg-blue-50 whitespace-nowrap">
                                    Insert Sample
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Email input for Email inspection --}}
                    <template x-if="uri.includes('inspect-email')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email to inspect
                            </label>
                            <div class="flex space-x-2">
                                <input type="email" x-model="params.email" placeholder="example@domain.com"
                                       class="flex-1 p-2 border border-gray-300 rounded-md focus:ring-teal focus:border-teal">
                                <button @click="insertSampleEmail"
                                        class="px-3 py-2 text-xs text-teal hover:text-teal-700 rounded border border-teal-200 hover:bg-blue-50 whitespace-nowrap">
                                    Insert Sample
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Special handling for user-agent inspector --}}
                    <template x-if="uri.includes('inspect-user-agent')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                User Agent String
                            </label>
                            <div class="flex space-x-2">
                                <input type="text" x-model="params.user_agent" placeholder="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36..."
                                       class="flex-1 p-2 border border-gray-300 rounded-md focus:ring-teal focus:border-teal">
                                <button @click="insertSampleUserAgent"
                                        class="px-3 py-2 text-xs text-teal hover:text-teal-700 rounded border border-teal-200 hover:bg-blue-50 whitespace-nowrap">
                                    Insert Sample
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Special handling for HTML to PDF converter --}}
                    <template x-if="uri.includes('html-to-pdf')">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                HTML Content
                            </label>
                            <textarea x-model="params.html" placeholder="<div>Your HTML content here</div>"
                                      class="w-full p-2 border border-gray-300 rounded-md focus:ring-teal focus:border-teal"
                                      rows="5"></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <div class="text-xs text-gray-500">
                                    Note: Do not use file:// or file:/ URLs in your HTML
                                </div>
                                <button @click="insertSampleHtml"
                                        class="text-xs text-teal hover:text-teal-700 px-2 py-1 rounded border border-teal-200 hover:bg-blue-50">
                                    Insert Sample HTML
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Sandbox token info with expiration state --}}
                    <div class="mb-4 p-3 bg-blue-50 rounded-md">
                        <div class="flex items-center">
                            <div class="w-full">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Sandbox API Usage</span>
                                    <a href="{{ route('register') }}" class="text-xs text-teal hover:text-teal-700 flex items-center">
                                        <span>Get full API access</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                                <div x-show="tokenInfo !== null" class="text-xs mt-1">
                                    <span x-text="`${tokenInfo?.remaining_calls || 0} calls remaining`"
                                          :class="tokenInfo && isExpired(tokenInfo.expires_at) ? 'text-danger-500' : 'font-medium'"></span>
                                    <template x-if="tokenInfo && tokenInfo.expires_at">
                                        <span>
                                            <span x-text="` • Expires ${formatExpiryTime(tokenInfo.expires_at)}`"
                                                  :class="tokenInfo && isExpired(tokenInfo.expires_at) ? 'text-danger-500' : 'text-gray-500'"></span>
                                            <template x-if="tokenInfo && isExpired(tokenInfo.expires_at)">
                                                <span class="text-danger-500 font-medium"> (expired)</span>
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
                                class="w-full py-3 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed flex justify-center text-white font-semibold shadow-md"
                                style="background-image: linear-gradient(135deg, var(--color-navy), var(--color-teal-600)); border: 1px solid var(--color-teal-700); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1);">
                            <span x-show="!loading" class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Send Request
                            </span>
                            <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>

                        <div x-show="!hasValidToken && tokenInfo && !isHealthOrReadinessEndpoint" class="mt-2 text-center">
                            <div x-show="tokenInfo && tokenInfo.expired" class="text-xs text-danger-500">Your session has expired. A new token will be created automatically.</div>
                            <div x-show="tokenInfo && tokenInfo.quota_exceeded" class="text-xs space-y-2">
                                <p class="text-xs text-danger-500">Your daily Sandbox API quota has been exhausted. Please try again tomorrow.</p>
                                <p class="text-xs text-gray-600">Need unlimited access? <a href="{{ route('register') }}" class="text-teal hover:text-teal-700 font-medium">Register for free</a> and get your own API key with higher limits.</p>
                            </div>
                            <div x-show="tokenInfo && !tokenInfo.expired && !tokenInfo.quota_exceeded" class="text-xs text-danger-500">Unable to validate your API token.</div>
                        </div>
                    </div>
                </div>

                {{-- Response area --}}
                <div class="border-t border-blue-100">
                    <div class="flex justify-between p-4 bg-navy-light/5">
                        <h4 class="text-sm font-medium text-navy">Response</h4>
                        <template x-if="hasPdfResponse">
                            <button @click="openPdfInNewWindow" class="text-xs text-teal hover:text-teal-700 px-2 py-1 rounded border border-teal-200 hover:bg-blue-50 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Open in New Tab
                            </button>
                        </template>
                    </div>

                    <div x-ref="responseArea" class="border-t border-blue-100 bg-blue-50 rounded-b-lg overflow-hidden response-area" style="height: 350px;">
                        <div x-show="!response && !hasPdfResponse" class="flex flex-col items-center justify-center h-full text-gray-500 p-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
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
