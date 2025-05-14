@props([
    'route'  // array with keys: uri, method, route_params, query_params
])

<div
    x-data="demoModal"
    data-uri="{{ $route['uri'] }}"
    data-method="{{ strtolower(explode('|', $route['method'])[0]) }}"
    x-cloak
>
    {{-- Trigger button --}}
    <button @click="open = true"
            class="w-full px-4 py-3 text-center rounded font-medium bg-white text-[#0A2240] hover:bg-gray-100 transition">
        Try this endpoint
    </button>

    {{-- Modal overlay --}}
    <div x-show="open"
         x-transition.opacity
         class="fixed inset-0 z-[9999]"
         @keydown.escape.window="closeModal()">

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-[#0A2240]/50 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal panel -->
            <div
                class="bg-white rounded-lg text-left shadow-xl transform transition-all w-full overflow-hidden"
                :class="fullscreen ? 'fixed inset-0 m-0 rounded-none' : 'relative max-w-4xl max-h-[90vh]'"
                @click.away="fullscreen ? toggleFullscreen() : closeModal()"
            >
                <!-- Modal header -->
                <div class="bg-white px-4 py-3 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-lg font-semibold text-[#0A2240]">
                        Try {{ strtoupper(explode('|', $route['method'])[0]) }} /{{ $route['uri'] }}
                    </h3>

                    <div class="flex items-center space-x-2">
                        <!-- Token status badge -->
                        <span x-show="token" class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-green-100 text-green-800">
                            <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            Sandbox Ready
                        </span>

                        <!-- Fullscreen toggle -->
                        <button
                            @click.stop="toggleFullscreen()"
                            type="button"
                            class="text-gray-500 hover:text-gray-700 p-1 rounded hover:bg-gray-100"
                            title="Toggle fullscreen"
                        >
                            <svg x-show="!fullscreen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                            </svg>
                            <svg x-show="fullscreen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Close button -->
                        <button
                            @click.stop="closeModal()"
                            type="button"
                            class="text-gray-500 hover:text-gray-700 p-1 rounded hover:bg-gray-100"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal body with scrollable content -->
                <div class="overflow-y-auto" :style="fullscreen ? 'height: calc(100vh - 125px);' : 'max-height: calc(90vh - 125px);'">
                    <div class="px-4 py-4 space-y-4">
                        <!-- Token warning message (only shown if no token) -->
                        <div x-show="!token" class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        You need a sandbox token to try the API.
                                        <button type="button" @click="getNewToken()" class="font-medium underline">
                                            Get token
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Input form -->
                        <form id="api-demo-form" @submit.prevent="submit" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($route['route_params'] as $p)
                                    <div>
                                        <label for="param-{{ $p }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $p }}</label>
                                        <input x-model="params['{{ $p }}']"
                                               id="param-{{ $p }}"
                                               type="text"
                                               placeholder="Enter {{ $p }}"
                                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-900 focus:ring-blue-500 focus:border-blue-500"/>
                                    </div>
                                @endforeach

                                @foreach($route['query_params'] as $p)
                                    <div>
                                        <label for="param-{{ $p }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $p }}</label>
                                        @if($p === 'html')
                                            <textarea x-model="params['{{ $p }}']"
                                                      id="param-{{ $p }}"
                                                      rows="6"
                                                      placeholder="Enter HTML content"
                                                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-900 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                        @else
                                            <input x-model="params['{{ $p }}']"
                                                   id="param-{{ $p }}"
                                                   type="text"
                                                   placeholder="Enter {{ $p }}"
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-900 focus:ring-blue-500 focus:border-blue-500"/>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </form>

                        <!-- Response section -->
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-gray-700">Response</h3>

                                <div class="flex items-center space-x-3">
                                    <!-- Download button for PDF responses -->
                                    <a
                                        x-show="responseUrl"
                                        x-bind:href="responseUrl"
                                        download="document.pdf"
                                        class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>

                            <!-- Response container with proper scrolling -->
                            <div class="border border-gray-200 bg-gray-50 rounded-md overflow-hidden response-area"
                                 :style="fullscreen ? 'height: calc(100vh - 280px);' : 'height: 350px;'">

                                <!-- Loading indicator -->
                                <div x-show="isLoading" class="flex items-center justify-center h-full">
                                    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-gray-300 border-r-blue-600"></div>
                                </div>

                                <!-- PDF Response in iframe -->
                                <div x-show="responseUrl && !isLoading" class="h-full w-full">
                                    <iframe
                                        x-bind:src="responseUrl"
                                        class="w-full h-full border-0">
                                    </iframe>
                                </div>

                                <!-- JSON/Text Response with proper scrolling -->
                                <div x-show="!responseUrl && !isLoading" class="h-full w-full overflow-auto">
                                    <pre x-text="response" class="whitespace-pre-wrap p-4 text-gray-900"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 flex justify-between sticky bottom-0 z-10">
                    <div>
                        <!-- Get token button (visible when there's no token) -->
                        <button x-show="!token" type="button" @click="getNewToken()"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Get API token
                        </button>

                        <!-- Refresh token button (visible when there's a token) -->
                        <button x-show="token" type="button" @click="getNewToken()"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh token
                        </button>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" @click="closeModal()"
                                class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-800 transition">
                            Cancel
                        </button>
                        <button type="submit" @click="submit()" class="px-4 py-2 rounded bg-[#10B981] text-white hover:bg-[#0DA271] transition" x-bind:disabled="isLoading || !token">
                            <span x-show="!isLoading">Send Request</span>
                            <span x-show="isLoading" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
