@extends('docs.layout')

@section('docs-content')
    <h1 class="text-3xl font-bold mb-6">Apixies API Documentation</h1>

    <p class="text-lg mb-8">
        Welcome to the Apixies API documentation. Our API allows you to build powerful applications
        with our simple, reliable services. This documentation will help you get started
        and provide reference for all available endpoints.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
        <!-- Getting Started Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-blue-500">
            <h2 class="text-xl font-bold mb-3">Getting Started</h2>
            <p class="mb-4">Learn the basics of Apixies API and get up and running quickly.</p>
            <div class="flex">
                <a href="{{ route('docs.authentication') }}" class="text-blue-600 hover:text-blue-800">
                    Authentication Guide →
                </a>
            </div>
        </div>

        <!-- Features Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-green-500">
            <h2 class="text-xl font-bold mb-3">API Features</h2>
            <p class="mb-4">Explore the powerful features available through our API.</p>
            <div class="flex">
                <a href="{{ route('docs.features') }}" class="text-green-600 hover:text-green-800">
                    View Features →
                </a>
            </div>
        </div>

        <!-- Endpoints Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-purple-500">
            <h2 class="text-xl font-bold mb-3">API Endpoints</h2>
            <p class="mb-4">Complete reference of all available API endpoints with examples.</p>
            <div class="flex">
                <a href="{{ route('docs.endpoints.index') }}" class="text-purple-600 hover:text-purple-800">
                    Browse Endpoints →
                </a>
            </div>
        </div>

        <!-- Code Examples Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-yellow-500">
            <h2 class="text-xl font-bold mb-3">Code Examples</h2>
            <p class="mb-4">Ready-to-use code snippets in various programming languages.</p>
            <div class="flex">
                <a href="{{ route('docs.code-examples') }}" class="text-yellow-700 hover:text-yellow-900">
                    View Examples →
                </a>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Try our API</h2>
        <p class="mb-4">
            Get started with our API in seconds using our sandbox environment.
            No authentication required - just try out the endpoints directly from your browser.
        </p>

        <div class="flex flex-wrap">
            <a href="{{ route('docs.endpoints.index') }}"
               class="px-4 py-2 bg-[#0A2240] text-white rounded hover:bg-[#153458] transition mr-4 mb-2">
                Explore Endpoints
            </a>

            @guest
                <a href="{{ route('register') }}"
                   class="px-4 py-2 bg-white border border-[#0A2240] text-[#0A2240] rounded hover:bg-gray-50 transition mb-2">
                    Sign Up for API Access
                </a>
            @endguest
        </div>
    </div>
@endsection
