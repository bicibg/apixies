@php
    $sections = [
        'overview' => 'Overview',
        'features' => 'Features',
        'authentication' => 'Authentication',
        'endpoints' => 'Endpoints',
        'responses' => 'Responses',
        'code-examples' => 'Code Examples',
    ];
@endphp

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

    <div class="nav-section bg-white rounded-lg shadow-sm p-4 mt-4">
        <a href="{{ route('suggestions.board') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Suggest New Endpoint
        </a>
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

<script>
    // Simple modal functionality for Quick Feedback
    document.addEventListener('DOMContentLoaded', function() {
        const quickFeedbackButton = document.getElementById('quickFeedbackButton');
        const quickFeedbackModal = document.getElementById('quickFeedbackModal');
        const quickFeedbackCloseButton = document.getElementById('quickFeedbackCloseButton');
        const quickFeedbackBackdrop = document.getElementById('quickFeedbackBackdrop');

        if (!quickFeedbackButton || !quickFeedbackModal) return;

        function openModal() {
            quickFeedbackModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            quickFeedbackModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        quickFeedbackButton.addEventListener('click', openModal);

        if (quickFeedbackCloseButton) {
            quickFeedbackCloseButton.addEventListener('click', closeModal);
        }

        if (quickFeedbackBackdrop) {
            quickFeedbackBackdrop.addEventListener('click', closeModal);
        }
    });
</script>

<style>
    .method-indicator.get {
        @apply bg-blue-100 text-blue-800;
    }

    .method-indicator.post {
        @apply bg-green-100 text-green-800;
    }

    .method-indicator.put, .method-indicator.patch {
        @apply bg-yellow-100 text-yellow-800;
    }

    .method-indicator.delete {
        @apply bg-red-100 text-red-800;
    }
</style>
