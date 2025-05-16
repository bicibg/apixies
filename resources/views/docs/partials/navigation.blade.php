@php
    $sections = [
        'overview' => 'Overview',
        'features' => 'Features',
        'authentication' => 'Authentication',
        'endpoints' => 'API Endpoints',
        'responses' => 'Response Format',
        'code-examples' => 'Code Examples',
    ];
@endphp

<div x-data="{
    sidebarOpen: window.innerWidth >= 768,
    endpointsOpen: {{ $activeSection === 'endpoints' ? 'true' : 'false' }}
}">
    {{-- Mobile Navigation Toggle Button (only visible on small screens) --}}
    <div class="block md:hidden sticky top-14 z-20 mb-4">
        <button @click="sidebarOpen = !sidebarOpen"
                class="w-full flex items-center justify-between bg-white p-3 rounded-lg shadow-sm border border-gray-200 text-gray-700">
            <span class="font-medium">Navigation Menu</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform"
                 :class="{'rotate-180': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

    {{-- The actual sidebar navigation --}}
    <div x-show="sidebarOpen"
         class="docs-nav md:block md:max-h-[calc(100vh-5rem)] md:overflow-y-auto sticky top-14 md:top-16 z-10">
        <div class="nav-section rounded-lg overflow-hidden shadow-sm bg-white">
            <div class="p-3 md:p-4 bg-gradient-to-r from-navy to-teal text-white">
                <h3 class="font-bold text-lg">Documentation</h3>
            </div>

            <div class="p-3 md:p-4">
                <ul class="space-y-1 md:space-y-2">
                    <li>
                        <a href="{{ route('docs.index') }}" @click="endpointsOpen = false"
                           class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'overview' ? 'bg-navy text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            Overview
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('docs.features') }}" @click="endpointsOpen = false"
                           class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'features' ? 'bg-navy text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            Features
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('docs.authentication') }}" @click="endpointsOpen = false"
                           class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'authentication' ? 'bg-navy text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            Authentication
                        </a>
                    </li>
                    <li class="endpoints-section">
                        <!-- Mobile: Click anywhere on the heading to toggle submenu -->
                        <div class="md:hidden flex items-center {{ $activeSection === 'endpoints' ? 'bg-navy text-white font-medium' : 'text-gray-700' }} rounded">
                            <button @click.prevent="endpointsOpen = !endpointsOpen" class="w-full flex items-center justify-between px-3 py-2 text-left focus:outline-none">
                                <span>API Endpoints</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                                     :class="{'rotate-180': !endpointsOpen}" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <!-- Desktop only navigation link -->
                        <a href="{{ route('docs.endpoints.index') }}"
                           class="hidden md:block px-3 py-2 rounded transition-colors {{ $activeSection === 'endpoints' ? 'bg-navy text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            API Endpoints
                        </a>

                        <!-- Submenu content -->
                        <div x-show="endpointsOpen || (window.innerWidth >= 768 && {{ $activeSection === 'endpoints' ? 'true' : 'false' }})"
                             class="pl-3 mt-1 md:mt-2 space-y-1 border-l-2 border-gray-200">
                            <!-- Mobile "View All Endpoints" link - only visible on mobile -->
                            <div class="md:hidden mb-2 pl-1">
                                <a href="{{ route('docs.endpoints.index') }}"
                                   class="flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    View All Endpoints
                                </a>
                            </div>

                            @if(isset($categories))
                                @foreach($categories as $categoryName => $routes)
                                    <div class="mb-2">
                                        <div class="font-medium text-xs md:text-sm text-gray-500 uppercase tracking-wider mb-1 pl-1">
                                            {{ ucfirst($categoryName) }}
                                        </div>
                                        <ul class="space-y-1">
                                            @foreach($routes as $routeKey => $route)
                                                <li>
                                                    <a href="{{ route('docs.show', ['key' => $routeKey]) }}"
                                                       class="block pl-2 py-1 text-sm border-l-2 {{ isset($activeEndpoint) && $activeEndpoint === $routeKey ? 'border-blue-500 text-blue-600 font-medium' : 'border-transparent text-gray-600 hover:border-gray-300' }}">
                                                        {{ $route['title'] ?? $routeKey }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </li>
                    <li>
                        <a href="{{ route('docs.responses') }}" @click="endpointsOpen = false"
                           class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'responses' ? 'bg-navy text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            Response Format
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('docs.code-examples') }}" @click="endpointsOpen = false"
                           class="block px-3 py-2 rounded transition-colors {{ $activeSection === 'code-examples' ? 'bg-navy text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            Code Examples
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- API Key Management Section --}}
        @auth
            <div class="nav-section bg-white rounded-lg shadow-sm p-3 md:p-4 mt-3 md:mt-4">
                <h3 class="font-medium mb-2 text-sm md:text-base">Your API</h3>
                <a href="{{ route('api-keys.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Manage API Keys
                </a>
            </div>
        @endauth

        {{-- Suggest New Endpoint Button --}}
        <div class="nav-section bg-white rounded-lg shadow-sm p-3 md:p-4 mt-3 md:mt-4">
            <a href="{{ route('suggestions.board') }}" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Suggest New Endpoint
            </a>
        </div>
    </div>
</div>

{{-- Quick Feedback Modal --}}
<div id="quickFeedbackModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        {{-- Backdrop --}}
        <div id="quickFeedbackBackdrop" class="fixed inset-0 bg-black/70"></div>

        {{-- Modal content --}}
        <div class="relative max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden z-10">
            <div class="bg-gray-50 px-6 py-3 flex justify-between items-center border-b">
                <h3 class="text-lg font-medium text-gray-900">Share Your Feedback</h3>
                <button id="quickFeedbackCloseButton" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <x-suggest-modal />
            </div>
        </div>
    </div>
</div>
