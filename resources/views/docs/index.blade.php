{{-- resources/views/docs/index.blade.php --}}
@extends('docs.layout')

@section('docs-content')
    <!-- Hero section -->
    @include('docs.partials.hero', [
        'title'    => 'Apixies API',
        'subtitle' => 'Build powerful applications with our simple, reliable API',
        'showCta'  => true,
    ])

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left sidebar navigation -->
        <div class="lg:col-span-1">
            @include('docs.partials.navigation')
        </div>

        <!-- Right content area -->
        <div class="lg:col-span-3">
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-4">API Documentation</h1>
                <p class="text-lg text-gray-700 mb-6">
                    Welcome to the Apixies API documentation. Here you'll find comprehensive guides and reference to help you start working with our API as quickly as possible.
                </p>
            </div>

            <!-- Documentation sections cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <!-- Features Card -->
                <a href="{{ route('docs.features') }}" class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                    <h2 class="text-xl font-bold mb-2 text-[#0A2240]">API Features</h2>
                    <p class="text-gray-700 mb-4">Learn about the powerful features our API offers to help you build better applications.</p>
                    <span class="text-blue-600 font-medium flex items-center">
                        Learn more
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Authentication Card -->
                <a href="{{ route('docs.authentication') }}" class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                    <h2 class="text-xl font-bold mb-2 text-[#0A2240]">Authentication</h2>
                    <p class="text-gray-700 mb-4">Understand how authentication works and how to secure your API requests.</p>
                    <span class="text-blue-600 font-medium flex items-center">
                        Learn more
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Endpoints Card -->
                <a href="{{ route('docs.endpoints.index') }}" class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                    <h2 class="text-xl font-bold mb-2 text-[#0A2240]">API Endpoints</h2>
                    <p class="text-gray-700 mb-4">Explore all the available API endpoints and learn how to use them effectively.</p>
                    <span class="text-blue-600 font-medium flex items-center">
                        View endpoints
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Response Format Card -->
                <a href="{{ route('docs.responses') }}" class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                    <h2 class="text-xl font-bold mb-2 text-[#0A2240]">Response Format</h2>
                    <p class="text-gray-700 mb-4">Learn about the standard response format used across all API endpoints.</p>
                    <span class="text-blue-600 font-medium flex items-center">
                        Learn more
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>
            </div>

            <!-- Popular endpoints section -->
            <div class="bg-white rounded-lg p-6 shadow-sm mb-8">
                <h2 class="text-xl font-bold mb-4">Popular Endpoints</h2>

                @if(isset($popularEndpoints) && $popularEndpoints->count() > 0)
                    <div class="space-y-4">
                        @foreach($popularEndpoints as $endpoint)
                            @php
                                $key = \Illuminate\Support\Str::slug(str_replace(['api/v1/', '/'], ['', '-'], $endpoint->endpoint));
                                $apiRoutes = config('api_examples');
                                $route = $apiRoutes[$key] ?? null;
                            @endphp

                            @if($route)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-md {{ isset($route['method']) && $route['method'] === 'GET' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }} mr-2">
                                                    {{ $route['method'] ?? 'GET' }}
                                                </span>
                                                <h3 class="font-semibold">{{ $route['title'] ?? 'API Endpoint' }}</h3>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">{{ $route['description'] ?? '' }}</p>
                                        </div>

                                        <a href="{{ route('docs.endpoints.show', $key) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Details
                                        </a>
                                    </div>

                                    <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                                        <code class="text-sm bg-gray-100 p-1 rounded">/{{ $route['uri'] ?? '' }}</code>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No data available yet. As you use the API, we'll track the most popular endpoints and display them here.</p>
                @endif

                <div class="mt-6">
                    <a href="{{ route('docs.endpoints.index') }}" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                        View all endpoints
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Getting Started Guide -->
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h2 class="text-xl font-bold mb-4">Getting Started</h2>

                <ol class="space-y-6 ml-5 list-decimal">
                    <li class="pl-2">
                        <h3 class="text-lg font-semibold mb-2">Sign up for an API key</h3>
                        <p class="text-gray-700">
                            To use the Apixies API, you'll need an API key. You can get one by
                            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">signing up for an account</a>.
                        </p>
                    </li>

                    <li class="pl-2">
                        <h3 class="text-lg font-semibold mb-2">Authenticate your requests</h3>
                        <p class="text-gray-700 mb-3">
                            All API endpoints require authentication. Include your API key in the header of your requests:
                        </p>
                        <pre class="bg-gray-100 p-3 rounded overflow-x-auto text-sm">
Authorization: Bearer YOUR_API_KEY</pre>
                    </li>

                    <li class="pl-2">
                        <h3 class="text-lg font-semibold mb-2">Make your first API call</h3>
                        <p class="text-gray-700 mb-3">
                            Try our test endpoint to verify that your API key is working correctly:
                        </p>
                        <pre class="bg-gray-100 p-3 rounded overflow-x-auto text-sm">
curl -X GET "{{ config('app.url') }}/api/v1/test" \
     -H "Authorization: Bearer YOUR_API_KEY"</pre>
                    </li>

                    <li class="pl-2">
                        <h3 class="text-lg font-semibold mb-2">Explore the API</h3>
                        <p class="text-gray-700">
                            Now that you've verified your API key, you can explore the rest of our
                            <a href="{{ route('docs.endpoints.index') }}" class="text-blue-600 hover:text-blue-800">API endpoints</a>
                            and start building your application.
                        </p>
                    </li>
                </ol>
            </div>
        </div>
    </div>
@endsection
