{{-- resources/views/docs/partials/endpoints.blade.php --}}
<div class="mt-6" id="api-endpoints-container">
    <h2 class="text-2xl font-bold mb-6">API Endpoints</h2>

    <!-- Search box -->
    <div class="relative mb-6">
        <input
            type="text"
            id="endpoint-search"
            placeholder="Search API endpoints..."
            class="w-full p-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    <!-- No results message (hidden by default) -->
    <div id="no-search-results" class="text-center text-gray-500 p-4 hidden">
        No endpoints match your search. <a href="#" id="reset-search" class="text-indigo-600 hover:underline">Clear search</a>
    </div>

    <!-- Endpoint categories -->
    @php
        $apiRoutes = config('api_examples');
        $categories = collect($apiRoutes)->groupBy('category');
    @endphp

    <div id="searchable-endpoints-container">
        @foreach($categories as $category => $endpoints)
            <div class="mb-8 category-section">
                <h3 class="text-xl font-bold mb-4 capitalize">{{ $category }}</h3>

                <div class="space-y-4">
                    @foreach($endpoints as $key => $route)
                        <div class="endpoint-row bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-300 transition"
                             data-endpoint-title="{{ $route['title'] ?? 'API Endpoint' }}"
                             data-endpoint-description="{{ $route['description'] ?? '' }}"
                             data-endpoint-uri="{{ $route['uri'] ?? '' }}">
                            <div class="md:flex md:flex-wrap md:justify-between md:items-start">
                                <div class="mb-3 md:mb-2 md:flex-1">
                                    <div class="flex items-center flex-wrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-md {{ isset($route['method']) && $route['method'] === 'GET' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }} mr-2 mb-1 md:mb-0">
                                            {{ $route['method'] ?? 'GET' }}
                                        </span>
                                        <h4 class="font-bold">{{ $route['title'] ?? 'API Endpoint' }}</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $route['description'] ?? '' }}</p>
                                </div>

                                <div class="mb-2 md:mb-0">
                                    <a href="{{ route('docs.show', ['key' => $key]) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
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
