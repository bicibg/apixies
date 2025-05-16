@extends('docs.layout')

@section('docs-content')
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 md:p-6 border-b border-blue-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                <h1 class="text-2xl font-bold text-navy">
                    API Documentation
                </h1>
            </div>

            @if($activeSection === 'overview')
                <p class="text-gray-600">
                    Welcome to the Apixies API documentation. Here you'll find comprehensive guides and reference to help you start working with our API as quickly as possible.
                </p>
            @endif
        </div>
    </div>
    <!-- Promotional Banner -->
    <div class="bg-gradient-to-r from-navy/10 to-teal/10 border border-teal/20 rounded-lg p-4 mb-8 flex items-center justify-between">
        <div class="flex items-center">
            <div class="bg-teal/20 p-2 rounded-full mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-navy">Free API Access During Beta</h3>
                <p class="text-sm text-gray-600">Get full API access with generous rate limits during our beta period.</p>
            </div>
        </div>
        <a href="{{ route('register') }}" class="py-2 px-4 rounded-md text-white font-semibold shadow-md hidden sm:block"
           style="background-image: linear-gradient(135deg, var(--color-navy), var(--color-teal-600)); border: 1px solid var(--color-teal-700); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1);">
            Sign Up Free
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
        <!-- Getting Started Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-navy">
            <div class="flex items-center mb-3">
                <div class="bg-navy/10 p-2 rounded text-navy mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-navy">Getting Started</h2>
            </div>
            <p class="mb-4 text-gray-600">Learn the basics of Apixies API and get up and running quickly.</p>
            <div class="flex">
                <a href="{{ route('docs.authentication') }}" class="text-navy hover:text-navy-light flex items-center">
                    Authentication Guide
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Features Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-teal">
            <div class="flex items-center mb-3">
                <div class="bg-teal/10 p-2 rounded text-teal mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-teal">API Features</h2>
            </div>
            <p class="mb-4 text-gray-600">Explore the powerful features available through our API.</p>
            <div class="flex">
                <a href="{{ route('docs.features') }}" class="text-teal hover:text-teal-700 flex items-center">
                    View Features
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Endpoints Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-teal-700">
            <div class="flex items-center mb-3">
                <div class="bg-teal-700/10 p-2 rounded text-teal-700 mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-teal-700">API Endpoints</h2>
            </div>
            <p class="mb-4 text-gray-600">Complete reference of all available API endpoints with examples.</p>
            <div class="flex">
                <a href="{{ route('docs.endpoints.index') }}" class="text-teal-700 hover:text-teal-800 flex items-center">
                    Browse Endpoints
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Code Examples Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-navy-light">
            <div class="flex items-center mb-3">
                <div class="bg-navy-light/10 p-2 rounded text-navy-light mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-navy-light">Code Examples</h2>
            </div>
            <p class="mb-4 text-gray-600">Ready-to-use code snippets in various programming languages.</p>
            <div class="flex">
                <a href="{{ route('docs.code-examples') }}" class="text-navy-light hover:text-navy flex items-center">
                    View Examples
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="bg-navy/5 border border-navy/10 rounded-lg p-6 mb-8">
        <div class="flex flex-col md:flex-row items-start md:items-center md:justify-between">
            <div class="flex items-start mb-4 md:mb-0">
                <div class="bg-gradient-to-r from-navy to-teal p-3 rounded-full text-white mr-4 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold mb-2 text-navy">Try our API</h2>
                    <div class="text-gray-600 space-y-2">
                        <p>Get started with our API in seconds using our sandbox environment.</p>
                        <ul class="list-disc list-inside ml-1 text-sm">
                            <li>No authentication required for testing</li>
                            <li>Generous rate limits during beta period</li>
                            <li>Full access to all API endpoints</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex flex-col space-y-3">
                <a href="{{ route('docs.endpoints.index') }}"
                   class="py-2 px-4 rounded-md text-white font-semibold shadow-md text-center"
                   style="background-image: linear-gradient(135deg, var(--color-navy), var(--color-teal-600)); border: 1px solid var(--color-teal-700); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1);">
                    Explore API Endpoints
                </a>

                @guest
                    <a href="{{ route('register') }}"
                       class="py-2 px-4 rounded-md bg-white border border-navy text-navy hover:bg-blue-50 font-medium shadow-sm text-center">
                        Create Free Account
                    </a>
                @endguest
            </div>
        </div>
    </div>
@endsection
