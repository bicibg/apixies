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

<header class="bg-white shadow-sm sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4 md:justify-start md:space-x-10">
            <div class="flex justify-start lg:w-0 lg:flex-1">
                <a href="{{ route('docs.index') }}" class="flex items-center">
                    <span class="text-lg font-bold text-[#0A2240]">
                        Apixies Developer API
                    </span>
                </a>
            </div>

            <div class="hidden md:flex items-center justify-end md:flex-1 lg:w-0 space-x-4">
                @auth
                    <a href="{{ route('filament.admin.home') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                        Admin
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Register
                    </a>
                @endauth

                <a href="{{ route('api-keys.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Get API Key
                </a>
            </div>
        </div>
    </div>
</header>

<aside class="w-56 flex-shrink-0 hidden lg:block">
    <div class="sticky top-20">
        <nav class="space-y-1">
            @foreach($sections as $key => $name)
                <a href="{{ route('docs.' . $key) }}" class="nav-link {{ $activeSection === $key ? 'active' : '' }}">
                    {{ $name }}
                </a>
            @endforeach
        </nav>

        @if (isset($categories) && count($categories) > 0)
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mt-8 mb-2">
                API Endpoints
            </h3>

            <nav class="space-y-1">
                @foreach($categories as $category => $endpoints)
                    <div class="py-2">
                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            {{ ucfirst($category) }}
                        </h4>
                        <div class="space-y-1 mt-2">
                            @foreach($endpoints as $key => $endpoint)
                                <a href="{{ route('docs.show', ['key' => $key]) }}"
                                   class="endpoint-link block px-3 py-2 text-sm rounded-md hover:bg-gray-50 {{ (isset($activeEndpoint) && $activeEndpoint === $key) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700' }}">
                                    <span class="method-indicator text-xs px-1.5 py-0.5 rounded mr-1 {{ strtolower(explode('|', $endpoint['method'] ?? 'GET')[0]) }}">
                                        {{ explode('|', $endpoint['method'] ?? 'GET')[0] }}
                                    </span>
                                    {{ $endpoint['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        @endif

        <div class="mt-8 border-t pt-4">
            <a href="{{ route('suggestions.board') }}" class="px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md flex items-center hover:bg-blue-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Suggest New Endpoint
            </a>

            <div class="mt-4">
                <button
                    type="button"
                    id="quickFeedbackButton"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md flex items-center w-full hover:bg-gray-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Quick Feedback
                </button>
            </div>
        </div>
    </div>
</aside>

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
    .nav-link {
        @apply block px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-50;
    }

    .nav-link.active {
        @apply bg-blue-50 text-blue-700;
    }

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
