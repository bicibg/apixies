@props([
    'route'  // array with keys: uri, method, route_params, query_params
])

<div x-data="demoModal()" x-cloak>
    {{-- Trigger button --}}
    <button @click="open = true"
            class="w-full px-4 py-3 text-center rounded font-medium bg-white text-[#0A2240] hover:bg-gray-100 transition">
        Try this endpoint
    </button>

    {{-- Modal overlay --}}
    <div x-show="open"
         x-transition.opacity
         class="fixed inset-0 flex items-center justify-center z-[9999] p-4 overflow-hidden
                bg-[#0A2240]/50 backdrop-blur-sm"
         @keydown.escape.window="closeModal()">

        {{-- Modal dialog --}}
        <div @click.away="closeModal()"
             x-transition.scale
             :class="isPdf && fullscreen ? 'fixed inset-0 bg-white flex flex-col' : 'bg-white w-full max-w-4xl rounded-lg shadow-xl relative flex flex-col'"
             style="max-height: 90vh;">

            {{-- Modal header --}}
            <div class="p-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
                <h2 class="text-xl font-semibold text-[#0A2240]">
                    Try {{ strtoupper(explode('|', $route['method'])[0]) }} /{{ $route['uri'] }}
                </h2>

                <div class="flex items-center">
                    {{-- Fullscreen toggle for PDF --}}
                    <button
                        x-show="isPdf && pdfUrl"
                        @click="fullscreen = !fullscreen"
                        type="button"
                        class="mr-2 text-gray-500 hover:text-gray-700"
                    >
                        <svg x-show="!fullscreen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                        </svg>
                        <svg x-show="fullscreen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- Close button --}}
                    <button @click="closeModal()" type="button" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="overflow-y-auto flex-grow"
                 :class="fullscreen ? 'p-0' : 'p-4'">

                {{-- Input form (hidden in fullscreen PDF mode) --}}
                <div x-show="!fullscreen || !isPdf">
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
                </div>

                {{-- Response section --}}
                <div :class="fullscreen ? 'flex-grow' : 'mt-6'" class="flex flex-col">
                    <div x-show="!fullscreen" class="flex items-center justify-between mb-2 sticky top-0 bg-white z-10">
                        <h3 class="text-sm font-medium text-gray-700">Response</h3>

                        {{-- Download button for PDF responses --}}
                        <a
                            x-show="isPdf && pdfUrl"
                            x-bind:href="pdfUrl"
                            download="document.pdf"
                            class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download PDF
                        </a>
                    </div>

                    {{-- Response display area - adjustable height with proper scrolling --}}
                    <div class="border border-gray-200 overflow-hidden relative"
                         :class="fullscreen ? 'h-full' : isPdf ? 'h-[500px]' : 'max-h-[400px] bg-gray-50 rounded-md'">

                        {{-- Loading indicator --}}
                        <div x-show="isLoading" class="flex items-center justify-center h-full bg-gray-50">
                            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-gray-300 border-r-blue-600"></div>
                        </div>

                        {{-- PDF Response - direct embed with object tag --}}
                        <template x-if="isPdf && pdfUrl && !isLoading">
                            <object
                                x-bind:data="pdfUrl"
                                type="application/pdf"
                                class="w-full h-full"
                            >
                                <div class="flex items-center justify-center h-full p-4 text-center">
                                    <p>Your browser doesn't support PDF preview.
                                        <a x-bind:href="pdfUrl" download="document.pdf" class="text-blue-600 underline">
                                            Download the PDF
                                        </a> instead.
                                    </p>
                                </div>
                            </object>
                        </template>

                        {{-- JSON/Text Response with scrolling --}}
                        <pre x-show="!isPdf && !isLoading" x-text="response"
                             class="whitespace-pre-wrap p-4 text-gray-900 overflow-auto h-full"></pre>
                    </div>
                </div>
            </div>

            {{-- Modal footer with buttons (hidden in fullscreen mode) --}}
            <div x-show="!fullscreen" class="p-4 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white z-10">
                <button type="button" @click="closeModal()"
                        class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-800 transition">
                    Cancel
                </button>
                <button type="submit" @click="submit()" class="px-4 py-2 rounded bg-[#10B981] text-white hover:bg-[#0DA271] transition" x-bind:disabled="isLoading">
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

    <script>
        function demoModal() {
            return {
                open: false,
                params: {},
                response: '{ }',
                isPdf: false,
                pdfUrl: null,
                isLoading: false,
                fullscreen: false,

                async submit() {
                    this.isLoading = true;
                    this.isPdf = false;

                    // Clean up previous PDF URL if exists
                    if (this.pdfUrl) {
                        URL.revokeObjectURL(this.pdfUrl);
                        this.pdfUrl = null;
                    }

                    const method = '{{ strtolower(explode('|', $route['method'])[0]) }}';
                    const isPostMethod = method.toLowerCase() === 'post';

                    // Build URL and request options
                    const url = `/${ '{{ $route['uri'] }}' }`;
                    const headers = {
                        'Authorization': `Bearer ${window.sandbox.token}`,
                        'X-XSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    };

                    // For POST to html-to-pdf endpoint, set Content-Type to application/json
                    if (isPostMethod) {
                        headers['Content-Type'] = 'application/json';
                    }

                    try {
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
                        const requestUrl = isPostMethod ? url : `${url}?${new URLSearchParams(this.params)}`;

                        const res = await fetch(requestUrl, options);

                        // Handle different status codes
                        if (!res.ok) {
                            const text = await res.text();
                            try {
                                this.response = JSON.stringify(JSON.parse(text), null, 2);
                            } catch {
                                this.response = text;
                            }
                            this.isPdf = false;
                            return;
                        }

                        // Check content type to handle different response types
                        const contentType = res.headers.get('Content-Type');

                        if (contentType && contentType.includes('application/pdf')) {
                            // Handle PDF response by directly passing through
                            const blob = await res.blob();
                            this.pdfUrl = URL.createObjectURL(blob);
                            this.isPdf = true;
                            this.response = 'PDF document received';
                        } else {
                            // Handle regular JSON response
                            const text = await res.text();
                            try {
                                this.response = JSON.stringify(JSON.parse(text), null, 2);
                            } catch {
                                this.response = text;
                            }
                            this.isPdf = false;
                        }

                        // Update quota
                        if (window.sandbox && window.sandbox.quota) {
                            window.sandbox.quota--;
                            localStorage.setItem('apixies_sandbox', JSON.stringify(window.sandbox));
                            const quotaElement = document.getElementById('sandbox-quota');
                            if (quotaElement) {
                                quotaElement.innerText = window.sandbox.quota;
                            }
                        }
                    } catch (err) {
                        this.response = `Error: ${err.toString()}`;
                        this.isPdf = false;
                    } finally {
                        this.isLoading = false;
                    }
                },

                // Clean up resources when the modal is closed
                closeModal() {
                    if (this.pdfUrl) {
                        URL.revokeObjectURL(this.pdfUrl);
                        this.pdfUrl = null;
                    }
                    this.open = false;
                }
            }
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('demoModal', demoModal);
        });
    </script>
</div>
