@extends('layouts.app')

@section('title', config('app.name') . ' - ' . ($pageTitle ?? config('app.tagline')))

@section('content')
    <div class="container mx-auto px-4 py-4 md:py-8">
        <div class="flex flex-col md:flex-row gap-6 md:gap-8">
            <!-- Sidebar Navigation -->
            <div class="w-full md:w-1/4 mb-4 md:mb-0">
                @include('docs.partials.navigation', [
                    'activeSection' => $activeSection ?? 'overview',
                    'activeCategory' => $activeCategory ?? null,
                    'activeEndpoint' => $activeEndpoint ?? null,
                    'categories' => $categories ?? null
                ])
            </div>

            <!-- Main Content -->
            <div class="w-full md:w-3/4 md:max-h-[calc(100vh-6rem)] md:overflow-y-auto">
                @if(isset($breadcrumbs))
                    <nav class="text-sm mb-4 overflow-x-auto whitespace-nowrap md:whitespace-normal">
                        <ol class="flex">
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="flex items-center">
                                    @if(!$loop->first)
                                        <svg class="h-4 w-4 text-gray-400 mx-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                  clip-rule="evenodd"></path>
                                        </svg>
                                    @endif

                                    @if(isset($breadcrumb['url']))
                                        <a href="{{ $breadcrumb['url'] }}" class="text-blue-600 hover:text-blue-800 truncate max-w-xs">
                                            {{ $breadcrumb['label'] }}
                                        </a>
                                    @else
                                        <span class="text-gray-700 truncate max-w-xs">{{ $breadcrumb['label'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                    @yield('docs-content')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @stack('doc-styles')
@endpush

@push('scripts')
    <!-- Mobile navigation toggle functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('mobileSidebarToggle');
            const menuIcon = document.getElementById('menuIcon');
            const sidebar = document.getElementById('docsSidebar');

            if (toggleButton && sidebar) {
                // Set initial state
                if (window.innerWidth < 768) {
                    sidebar.classList.add('hidden');
                } else {
                    sidebar.classList.remove('hidden');
                }

                // Toggle sidebar visibility on button click
                toggleButton.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                    menuIcon.classList.toggle('rotate-180');
                });

                // Handle window resize
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 768) {
                        sidebar.classList.remove('hidden');
                    } else if (!toggleButton.classList.contains('active')) {
                        sidebar.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    <!-- Fix for numeric URLs -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Find all links in the navigation that have numeric IDs
            const numericLinks = document.querySelectorAll('a[href^="/docs/"]');
            numericLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (/\/docs\/\d+$/.test(href)) {
                    // Get the corresponding endpoint name from the link text
                    const linkText = link.textContent.trim().toLowerCase();

                    // Map of common endpoint names to their keys
                    const endpointMap = {
                        'health check': 'health',
                        'test endpoint': 'test',
                        'ssl health inspector': 'ssl',
                        'security headers inspector': 'headers',
                        'email inspector': 'email',
                        'user agent inspector': 'user-agent',
                        'html to pdf converter': 'html-to-pdf'
                    };

                    // Try to find a matching endpoint key
                    let newKey = null;

                    // Exact match
                    if (endpointMap[linkText]) {
                        newKey = endpointMap[linkText];
                    } else {
                        // Partial match
                        for (const [text, key] of Object.entries(endpointMap)) {
                            if (linkText.includes(text) || text.includes(linkText)) {
                                newKey = key;
                                break;
                            }
                        }
                    }

                    if (newKey) {
                        link.setAttribute('href', `/docs/${newKey}`);
                    }
                }
            });
        });
    </script>
    @stack('doc-scripts')
@endpush
