@extends('docs.layout')

@section('docs-content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-3">{{ $apiRoute['title'] ?? $key }}</h1>
        <p class="text-lg text-gray-700">{{ $apiRoute['description'] ?? 'No description available' }}</p>

        <div class="flex items-center mt-4">
            <span class="method-badge {{ strtolower(explode('|', $apiRoute['method'] ?? 'GET')[0]) }} mr-3">
                {{ $apiRoute['method'] ?? 'GET' }}
            </span>
            <code class="bg-gray-100 px-3 py-1 rounded-md text-sm font-mono">
                {{ $apiRoute['uri'] ?? '/' }}
            </code>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <div class="border-b border-gray-200">
            <nav class="tab-nav flex">
                <button class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="overview">
                    Overview
                </button>
                <button class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="parameters">
                    Parameters
                </button>
                <button class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="responses">
                    Response
                </button>
                <button class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="examples">
                    Examples
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Tab contents here... -->
            <!-- Overview Tab -->
            <div class="tab-content" id="overview">
                <p class="mb-4">
                    {{ $apiRoute['description'] ?? 'No detailed description available.' }}
                </p>

                <h3 class="text-lg font-medium mb-2">Rate Limiting</h3>
                <p class="mb-4">
                    Standard rate limits apply to this endpoint.
                </p>

                <h3 class="text-lg font-medium mb-2">Authentication</h3>
                <p>
                    This endpoint can be accessed in sandbox mode without authentication, or via a valid API key in production.
                </p>
            </div>

            <!-- Parameters Tab -->
            <div class="tab-content hidden" id="parameters">
                <!-- Parameters content here... -->
                @if((isset($apiRoute['route_params']) && count($apiRoute['route_params']) > 0) || (isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0))
                    @if(isset($apiRoute['route_params']) && count($apiRoute['route_params']) > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-3">URL Parameters</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 api-table">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Parameter
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Required
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($apiRoute['route_params'] as $param)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <code>{{ $param }}</code>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                string
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                        Required
                                                    </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                The {{ $param }} parameter
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if(isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0)
                        <!-- Query parameters table here... -->
                    @endif
                @else
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-700">This endpoint does not require any parameters.</p>
                    </div>
                @endif
            </div>

            <!-- Responses Tab -->
            <div class="tab-content hidden" id="responses">
                <!-- Responses content here... -->
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-700">Response details for this API endpoint.</p>
                </div>
            </div>

            <!-- Examples Tab -->
            <div class="tab-content hidden" id="examples">
                <!-- Examples content here... -->
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-700">Examples for this API endpoint.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4">Try It Yourself</h2>
            <p class="mb-4">
                Test this endpoint directly from your browser using our API sandbox.
                No authentication required for testing.
            </p>

            <x-demo-modal :route="[
                'uri' => $apiRoute['uri'] ?? '',
                'method' => $apiRoute['method'] ?? 'GET',
                'route_params' => $apiRoute['route_params'] ?? [],
                'query_params' => $apiRoute['query_params'] ?? []
            ]" />
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Tab script loaded");

            // Query selectors for tab elements
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            console.log(`Found ${tabButtons.length} tab buttons and ${tabContents.length} tab contents`);

            // Function to activate a tab
            function activateTab(button) {
                const tabId = button.getAttribute('data-tab');
                console.log(`Activating tab: ${tabId}`);

                // Deactivate all tabs
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'text-[#0A2240]');
                    btn.classList.remove('border-[#0A2240]', 'border-b-2');
                    btn.classList.add('text-gray-500', 'border-transparent');
                });

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Activate selected tab
                button.classList.add('active', 'text-[#0A2240]', 'border-[#0A2240]', 'border-b-2');
                button.classList.remove('text-gray-500', 'border-transparent');

                // Show selected tab content
                const content = document.getElementById(tabId);
                if (content) {
                    content.classList.remove('hidden');
                    console.log(`Showing content for tab: ${tabId}`);
                } else {
                    console.error(`Tab content not found for ID: ${tabId}`);
                }
            }

            // Add click event listeners to tab buttons
            tabButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log(`Tab clicked: ${this.textContent.trim()}`);
                    activateTab(this);
                });
            });

            // Activate the first tab by default
            if (tabButtons.length > 0) {
                console.log('Activating first tab by default');
                activateTab(tabButtons[0]);
            }
        });
    </script>
@endpush
