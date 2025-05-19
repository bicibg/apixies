<nav class="docs-nav">
    <div class="space-y-8">
        <div>
            <h5 class="docs-nav-title">Getting Started</h5>
            <ul>
                <li><a href="{{ route('docs.index') }}" class="{{ request()->routeIs('docs.index') ? 'active' : '' }}">Overview</a></li>
                <li><a href="{{ route('docs.authentication') }}" class="{{ request()->routeIs('docs.authentication') ? 'active' : '' }}">Authentication</a></li>
                <li><a href="{{ route('docs.features') }}" class="{{ request()->routeIs('docs.features') ? 'active' : '' }}">Features</a></li>
                <li><a href="{{ route('docs.responses') }}" class="{{ request()->routeIs('docs.responses') ? 'active' : '' }}">Response Format</a></li>
                <li><a href="{{ route('docs.code-examples') }}" class="{{ request()->routeIs('docs.code-examples') ? 'active' : '' }}">Code Examples</a></li>
                <li><a href="{{ url('api/documentation') }}" target="_blank" class="flex items-center">
                        OpenAPI Docs
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                        </svg>
                    </a></li>
            </ul>
        </div>

        <div>
            <h5 class="docs-nav-title">API Endpoints</h5>
            <ul>
                @php
                    $apiRoutes = config('api_endpoints');
                    // Group by category
                    $categories = [];
                    foreach ($apiRoutes as $key => $route) {
                        $category = $route['category'] ?? 'general';
                        if (!isset($categories[$category])) {
                            $categories[$category] = [];
                        }
                        $categories[$category][$key] = $route;
                    }
                @endphp

                @foreach($categories as $category => $endpoints)
                    <li class="category-item">
                        <span class="category-name">{{ Str::title($category) }}</span>
                        <ul class="pl-4">
                            @foreach($endpoints as $key => $route)
                                <li>
                                    <a href="{{ route('docs.show', ['key' => $key]) }}" class="{{ request()->route('key') === $key ? 'active' : '' }}">
                                        {{ $route['title'] ?? $key }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
