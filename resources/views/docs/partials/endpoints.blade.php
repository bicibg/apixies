<div class="mt-6" id="api-endpoints-container">
    <!-- Search box -->
    <div class="relative mb-6">
        <input
            type="text"
            id="endpoint-search"
            placeholder="Search API endpoints..."
            class="search-input"
        >
        <div class="search-icon">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    <!-- No results message (hidden by default) -->
    <div id="no-search-results" class="text-center text-gray-500 p-4 hidden">
        No endpoints match your search. <a href="#" id="reset-search" class="text-accent-500 hover:text-accent-700 hover:underline">Clear search</a>
    </div>

    <!-- Endpoint categories -->
    @php
        $apiRoutes = config('api_endpoints');
        // Manually group by category while preserving original keys
        $categories = [];
        foreach ($apiRoutes as $key => $route) {
            $category = $route['category'] ?? 'general';
            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][$key] = $route;
        }
    @endphp

    <div id="searchable-endpoints-container">
        @foreach($categories as $category => $endpoints)
            <div class="mb-8 category-section">
                <h3 class="text-xl font-bold mb-4 capitalize text-primary-600">{{ $category }}</h3>

                <div class="space-y-4">
                    @foreach($endpoints as $key => $route)
                        <div class="endpoint-row"
                             data-endpoint-title="{{ $route['title'] ?? 'API Endpoint' }}"
                             data-endpoint-description="{{ $route['description'] ?? '' }}"
                             data-endpoint-uri="{{ $route['uri'] ?? '' }}">
                            <div class="md:flex md:flex-wrap md:justify-between md:items-start">
                                <div class="mb-3 md:mb-2 md:flex-1">
                                    <div class="flex items-center flex-wrap">
                                        <span class="method-badge {{ strtolower($route['method'] ?? 'GET') }}">
                                            {{ $route['method'] ?? 'GET' }}
                                        </span>
                                        <h4 class="font-bold ml-2">{{ $route['title'] ?? 'API Endpoint' }}</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $route['description'] ?? '' }}</p>
                                </div>

                                <div class="mb-2 md:mb-0">
                                    <a href="{{ route('docs.show', ['key' => $key]) }}" class="text-accent-500 hover:text-accent-700 text-sm font-medium">
                                        View Details
                                    </a>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-t border-gray-100 md:flex md:flex-wrap md:justify-between md:items-center">
                                <code class="text-sm bg-gray-100 p-1 rounded block mb-3 md:mb-0 overflow-x-auto">
                                    /{{ $route['uri'] ?? '' }}
                                </code>

                                <div class="flex justify-end">
                                    @if($route['demo'] ?? false)
                                        <div>
                                            <x-demo-modal :route="[
                                                'uri' => $route['uri'] ?? '',
                                                'method' => $route['method'] ?? 'GET',
                                                'route_params' => $route['route_params'] ?? [],
                                                'query_params' => $route['query_params'] ?? []
                                            ]" />
                                        </div>
                                    @else
                                        <button disabled class="px-4 py-2 rounded font-medium bg-gray-300 text-gray-500 cursor-not-allowed">
                                            Demo unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
