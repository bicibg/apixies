{{-- resources/views/docs/endpoints/index.blade.php --}}
@extends('docs.layout')

@section('docs-content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left sidebar navigation -->
        <div class="lg:col-span-1">
            @include('docs.partials.navigation')
        </div>

        <!-- Right content area -->
        <div class="lg:col-span-3">
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-4">API Endpoints</h1>
                <p class="text-lg text-gray-700 mb-6">
                    Explore all the available API endpoints and learn how to use them effectively.
                </p>
            </div>

            <!-- Search box -->
            <div class="relative mb-8">
                <input
                    type="text"
                    id="endpoint-search"
                    placeholder="Search API endpoints..."
                    class="w-full p-3 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <!-- No results message (hidden by default) -->
            <div id="no-search-results" class="text-center text-gray-500 p-4 hidden">
                No endpoints match your search.
            </div>

            <!-- Endpoint categories -->
            @foreach($categories as $category => $endpoints)
                <div class="mb-12 endpoint-category">
                    <h2 class="text-2xl font-bold mb-6 capitalize" id="{{ $category }}">{{ $category }}</h2>

                    <div class="space-y-4">
                        @foreach($endpoints as $key => $route)
                            <div class="endpoint-row bg-white p-5 rounded-lg border border-gray-200 hover:border-indigo-300 transition">
                                <div class="flex flex-wrap justify-between items-start">
                                    <div class="mb-3">
                                        <div class="flex items-center">
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ isset($route['method']) && $route['method'] === 'GET' ? 'method-badge get' : 'method-badge post' }} mr-2">
                                                {{ $route['method'] ?? 'GET' }}
                                            </span>
                                            <h3 class="text-lg font-bold">{{ $route['title'] ?? 'API Endpoint' }}</h3>
                                        </div>
                                        <p class="text-gray-600 mt-2">{{ $route['description'] ?? '' }}</p>
                                    </div>

                                    <div>
                                        <a href="{{ route('docs.endpoints.show', $key) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                            View Details
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap justify-between items-center">
                                    <code class="text-sm bg-gray-100 p-2 rounded mb-2 md:mb-0">/{{ $route['uri'] ?? '' }}</code>

                                    <div class="flex flex-wrap gap-3">
                                        @if(($route['demo'] ?? false) && isset($route['uri']))
                                            <x-demo-modal :route="[
                                                'uri' => $route['uri'] ?? '',
                                                'method' => $route['method'] ?? 'GET',
                                                'route_params' => $route['route_params'] ?? [],
                                                'query_params' => $route['query_params'] ?? []
                                            ]" />
                                        @else
                                            <button disabled class="px-4 py-2 rounded font-medium bg-gray-300 text-gray-500 cursor-not-allowed">
                                                Demo unavailable
                                            </button>
                                        @endif

                                        <a href="{{ route('docs.endpoints.show', $key) }}"
                                           class="px-4 py-2 rounded bg-[#0A2240] text-white hover:bg-[#051428] transition">
                                            Documentation
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('endpoint-search');
            if (!searchInput) return;

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const endpointRows = document.querySelectorAll('.endpoint-row');
                const categories = document.querySelectorAll('.endpoint-category');
                let anyVisible = false;

                categories.forEach(category => {
                    let categoryHasVisibleEndpoints = false;

                    // Check each endpoint in this category
                    const endpoints = category.querySelectorAll('.endpoint-row');
                    endpoints.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const isVisible = text.includes(query);
                        row.style.display = isVisible ? '' : 'none';

                        if (isVisible) {
                            categoryHasVisibleEndpoints = true;
                            anyVisible = true;
                        }
                    });

                    // Show/hide the category based on whether it has any visible endpoints
                    category.style.display = categoryHasVisibleEndpoints ? '' : 'none';
                });

                // Show/hide no results message
                const noResults = document.getElementById('no-search-results');
                if (noResults) {
                    noResults.style.display = query && !anyVisible ? 'block' : 'none';
                }
            });
        });
    </script>
@endsection
