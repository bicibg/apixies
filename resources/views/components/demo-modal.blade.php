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

    {{-- Trigger button with improved visibility --}}
    <button @click="open = true" class="w-full px-4 py-3 text-center rounded font-medium bg-gradient-to-r from-[#0A2240] to-[#007C91] text-white hover:shadow-md transition">
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
                        </div>
                    </template>

                    {{-- Sandbox token info --}}
                    <div class="mb-4 p-3 bg-gray-50 rounded-md">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-sm text-gray-500">API Usage</span>
                                <div x-show="tokenInfo" class="text-xs mt-1">
                                    <span x-text="`${tokenInfo.remaining_calls || 0} calls remaining`" class="font-medium"></span>
                                    <span x-show="tokenInfo.expires_at" x-text="` • Expires ${formatExpiryTime(tokenInfo.expires_at)}`" class="text-gray-500"></span>
                                </div>
                                <div x-show="!tokenInfo" class="text-xs mt-1 text-gray-500">
                                    No token information available
                                </div>
                            </div>
                            <button @click="refreshToken"
                                    class="text-xs text-blue-600 hover:text-blue-800 px-2 py-1 rounded border border-blue-200 hover:bg-blue-50"
                                    :disabled="refreshingToken">
                                <span x-show="!refreshingToken">Refresh Token</span>
                                <span x-show="refreshingToken">Refreshing...</span>
                            </button>
                        </div>
                    </div>

                    {{-- Submit button --}}
                    <div class="mt-6">
                        <button @click="submit"
                                :disabled="loading || (needsUrlParam && !hasRequiredParams)"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed flex justify-center">
                            <span x-show="!loading">Send Request</span>
                            <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Response area --}}
                <div class="border-t border-gray-200">
                    <div class="flex justify-between p-4 bg-gray-50">
                        <h4 class="text-sm font-medium text-gray-700">Response</h4>
                    </div>

                    <div class="border-t border-gray-200 bg-gray-50 rounded-md overflow-hidden response-area" style="height: 350px;">
                        <div x-show="!response" class="flex items-center justify-center h-full text-gray-500">
                            <span>Send a request to see the response</span>
                        </div>

                        <div x-show="response" class="p-4 overflow-auto h-full">
                            <pre x-text="typeof response === 'string' ? response : JSON.stringify(response, null, 2)" class="text-sm font-mono"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
