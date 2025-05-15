<div class="docs-nav sticky top-4">
    <div class="nav-section rounded-lg overflow-hidden shadow-sm bg-white">
        <div class="p-4 bg-gradient-to-r from-[#0A2240] to-[#007C91] text-white">
            <h3 class="font-bold text-lg">Documentation</h3>
        </div>

        <div class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('docs.index') }}"
                       class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'overview' ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        Overview
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.features') }}"
                       class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'features' ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        Features
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.authentication') }}"
                       class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'authentication' ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        Authentication
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.endpoints.index') }}"
                       class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'endpoints' ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        API Endpoints
                    </a>
                </li>

                @if($activeSection === 'endpoints' && isset($categories))
                    <div class="pl-4 mt-2 space-y-1 border-l-2 border-gray-200">
                        @foreach($categories as $categoryName => $routes)
                            <div class="mb-2">
                                <div class="font-medium text-sm text-gray-500 uppercase tracking-wider mb-1">
                                    {{ ucfirst($categoryName) }}
                                </div>
                                <ul class="space-y-1">
                                    @foreach($routes as $routeKey => $route)
                                        <li>
                                            <a href="{{ route('docs.show', ['key' => $routeKey]) }}"
                                               class="block pl-2 py-1 text-sm border-l-2 {{ isset($activeEndpoint) && $activeEndpoint === $routeKey ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600 hover:border-gray-300' }}">
                                                {{ $route['title'] ?? $routeKey }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif

                <li>
                    <a href="{{ route('docs.responses') }}"
                       class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'responses' ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        Response Format
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.code-examples') }}"
                       class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'code-examples' ? 'bg-[#0A2240] text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        Code Examples
                    </a>
                </li>
            </ul>
        </div>
    </div>

    @auth
        <div class="nav-section bg-white rounded-lg shadow-sm p-4 mt-4">
            <h3 class="font-medium mb-2">Your API</h3>
            <a href="{{ route('api-keys.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                Manage API Keys
            </a>
        </div>
    @endauth
</div>
