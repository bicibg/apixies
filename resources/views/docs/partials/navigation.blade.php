{{-- resources/views/docs/partials/navigation.blade.php --}}
<div class="docs-nav sticky top-4">
    <div class="nav-section bg-white p-4 rounded-lg shadow-sm">
        <h3 class="text-lg font-bold mb-4">Documentation</h3>

        <ul class="space-y-1">
            <li>
                <a href="{{ route('docs.index') }}"
                   class="block px-3 py-2 rounded transition-colors {{ request()->routeIs('docs.index') ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Overview
                </a>
            </li>
            <li>
                <a href="{{ route('docs.features') }}"
                   class="block px-3 py-2 rounded transition-colors {{ request()->routeIs('docs.features') ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Features
                </a>
            </li>
            <li>
                <a href="{{ route('docs.authentication') }}"
                   class="block px-3 py-2 rounded transition-colors {{ request()->routeIs('docs.authentication') ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Authentication
                </a>
            </li>
            <li>
                <a href="{{ route('docs.endpoints.index') }}"
                   class="block px-3 py-2 rounded transition-colors {{ request()->routeIs('docs.endpoints.*') ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    API Endpoints
                </a>
            </li>
            <li>
                <a href="{{ route('docs.responses') }}"
                   class="block px-3 py-2 rounded transition-colors {{ request()->routeIs('docs.responses') ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Response Format
                </a>
            </li>
            <li>
                <a href="{{ route('docs.examples') }}"
                   class="block px-3 py-2 rounded transition-colors {{ request()->routeIs('docs.examples') ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Code Examples
                </a>
            </li>
        </ul>
    </div>

    <!-- Endpoint Categories - Only show on endpoints pages -->
    @if(request()->routeIs('docs.endpoints.*'))
        <div class="nav-section bg-white p-4 rounded-lg shadow-sm mt-4">
            <h3 class="text-lg font-bold mb-4">Endpoint Categories</h3>

            @php
                $apiRoutes = config('api_examples');
                $categories = collect($apiRoutes)->groupBy('category');
            @endphp

            <ul class="space-y-1">
                @foreach($categories as $category => $endpoints)
                    <li>
                        <a href="{{ route('docs.endpoints.index') }}#{{ $category }}"
                           class="block px-3 py-2 rounded transition-colors text-gray-700 hover:bg-gray-100 {{ isset($apiRoute) && ($apiRoute['category'] ?? '') === $category ? 'font-medium text-[#0A2240]' : '' }}">
                            {{ ucfirst($category) }}
                            <span class="text-xs text-gray-500 ml-1">({{ count($endpoints) }})</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Popular Endpoints - Only show on main and endpoints pages -->
    @if(request()->routeIs('docs.index') || request()->routeIs('docs.endpoints.*'))
        <div class="nav-section bg-white p-4 rounded-lg shadow-sm mt-4">
            <h3 class="text-lg font-bold mb-4">Popular Endpoints</h3>

            @if(isset($popularEndpoints) && $popularEndpoints->count() > 0)
                <ul class="space-y-1">
                    @foreach($popularEndpoints as $endpoint)
                        @php
                            $key = \Illuminate\Support\Str::slug(str_replace(['api/v1/', '/'], ['', '-'], $endpoint->endpoint));
                        @endphp

                        <li>
                            <a href="{{ route('docs.endpoints.show', $key) }}"
                               class="block px-3 py-2 rounded transition-colors text-gray-700 hover:bg-gray-100 {{ request()->is('docs/endpoints/'.$key) ? 'font-medium text-[#0A2240]' : '' }}">
                                {{ $endpoint->endpoint }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500 text-sm px-3 py-2">No data available yet.</p>
            @endif
        </div>
    @endif
</div>
