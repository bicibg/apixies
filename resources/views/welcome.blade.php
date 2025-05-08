@extends('layouts.app')

@section('title','Apixies API Documentation')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Hero Section -->
        <div class="api-hero p-8 mb-10">
            <h1 class="text-3xl font-bold mb-3">Apixies API</h1>
            <p class="text-xl opacity-90 mb-6">Build powerful applications with our simple, reliable API</p>

            @auth
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('api-keys.index') }}" class="bg-white text-[#0A2240] px-5 py-2 rounded-md font-medium hover:bg-gray-100 transition cursor-pointer flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Manage API Keys
                    </a>
                </div>
            @else
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('login') }}" class="bg-white text-[#0A2240] px-5 py-2 rounded-md font-medium hover:bg-gray-100 transition cursor-pointer">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="bg-teal-500 text-white px-5 py-2 rounded-md font-medium hover:bg-teal-600 transition cursor-pointer">
                        Sign Up for API Access
                    </a>
                </div>
            @endauth
        </div>

        <!-- Main Content with Tabs -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Tab Navigation -->
            <div class="flex border-b overflow-x-auto">
                <button class="tab-btn active px-6 py-3 font-medium text-[#0A2240]" data-tab="endpoints">
                    API Endpoints
                </button>
                <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-[#0A2240]" data-tab="authentication">
                    Authentication
                </button>
                <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-[#0A2240]" data-tab="examples">
                    Examples
                </button>
                <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-[#0A2240]" data-tab="responses">
                    Response Format
                </button>
                <button class="tab-btn px-6 py-3 font-medium text-gray-500 hover:text-[#0A2240]" data-tab="features">
                    API Features
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="endpoints">
                <div class="p-6">
                    <div class="mb-4 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-[#0A2240]">Available Endpoints</h2>
                        <div class="relative">
                            <input type="text" id="endpoint-search" placeholder="Search endpoints..."
                                   class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute right-3 top-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto text-sm api-table">
                            <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="py-3 px-4 border-b">Method</th>
                                <th class="py-3 px-4 border-b">URI</th>
                                <th class="py-3 px-4 border-b">Description</th>
                                <th class="py-3 px-4 border-b">Route Parameters</th>
                                <th class="py-3 px-4 border-b">Query Parameters</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($routes as $route)
                                <tr class="endpoint-row border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                            <span class="method-badge {{ strtolower($route['method']) }}">
                                                {{ $route['method'] }}
                                            </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-sm">{{ $route['uri'] }}</td>
                                    <td class="py-3 px-4">{{ $route['description'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        @if(!empty($route['route_params']))
                                            @foreach($route['route_params'] as $param)
                                                <span class="param-badge">{{ $param }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-gray-400">None</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if(!empty($route['query_params']))
                                            @foreach($route['query_params'] as $param)
                                                <span class="param-badge">{{ $param }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-gray-400">None</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <!-- No results message -->
                        <div id="no-search-results" class="hidden py-8 text-center text-gray-500">
                            No endpoints match your search. Try different keywords.
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content hidden" id="authentication">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Authentication</h2>

                    @guest
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                            <div class="flex">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-blue-800">
                                    <strong>Want to use our API?</strong> <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-semibold">Sign up</a> or <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-semibold">log in</a> to get your API key.
                                </p>
                            </div>
                        </div>
                    @endguest

                    <div class="mb-6">
                        <p class="mb-4">All API requests require authentication using an API key. Include your API key in the request headers:</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                                <h3 class="font-medium text-base mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Bearer Authentication (Recommended)
                                </h3>
                                <div class="code-block">
                                    <code>Authorization: Bearer YOUR_API_KEY</code>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                                <h3 class="font-medium text-base mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Alternative Method
                                </h3>
                                <div class="code-block">
                                    <code>X-API-KEY: YOUR_API_KEY</code>
                                </div>
                            </div>
                        </div>

                        @auth
                            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-2">
                                <div class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-green-800">
                                        You can manage your API keys in the <a href="{{ route('api-keys.index') }}" class="text-green-700 hover:underline font-semibold">API Keys</a> section.
                                    </p>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="tab-content hidden" id="examples">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Example Requests</h2>

                    <div class="mb-6">
                        <h3 class="font-medium text-gray-800 mb-3">cURL Example</h3>
                        <div class="code-block">
                            <pre><code>curl -X GET \
  https://{{ request()->getHost() }}/v1/health \
  -H 'Authorization: Bearer YOUR_API_KEY'</code></pre>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-medium text-gray-800 mb-3">JavaScript Example</h3>
                        <div class="code-block">
                            <pre><code>// JavaScript fetch example
fetch('https://{{ request()->getHost() }}/v1/health', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));</code></pre>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-medium text-gray-800 mb-3">PHP Example</h3>
                        <div class="code-block">
                            <pre><code>// PHP cURL example
$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://{{ request()->getHost() }}/v1/health",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer YOUR_API_KEY",
    "Content-Type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error: " . $err;
} else {
  echo $response;
}</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content hidden" id="responses">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Response Format</h2>

                    <p class="mb-4">All API responses are returned in JSON format with a consistent structure:</p>

                    <div class="code-block mb-6">
                        <pre><code>{
  "status": "success", // or "error"
  "http_code": 200, // HTTP status code
  "code": "SUCCESS_CODE", // API-specific code identifier
  "message": "Operation successful", // A human-readable message
  "data": {} // The response data (object or array)
}</code></pre>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="font-medium text-base mb-3">Successful Response Example</h3>
                            <div class="code-block">
                                <pre><code>{
  "status": "success",
  "http_code": 200,
  "code": "API_KEY_CREATED",
  "message": "API key created",
  "data": {
    "id": 1,
    "name": "Production API Key",
    "token": "1|yQPrJKDIwCRrGSJQI5SpDPZvVAHnqkH4rGI3kYgF"
  }
}</code></pre>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="font-medium text-base mb-3">Error Response Example</h3>
                            <div class="code-block">
                                <pre><code>{
  "status": "error",
  "http_code": 400,
  "code": "VALIDATION_ERROR",
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content hidden" id="features">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">API Features</h2>

                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Request Tracking</h3>
                            <p class="mb-2">Each API request is assigned a unique request ID, which is returned in the <code class="bg-gray-100 px-1 py-0.5 rounded text-sm font-mono">X-Request-ID</code> header of the response. You can also provide your own request ID in the <code class="bg-gray-100 px-1 py-0.5 rounded text-sm font-mono">X-Request-ID</code> header of your request.</p>
                            <div class="code-block">
                                <pre><code>// Example response header
X-Request-ID: 550e8400-e29b-41d4-a716-446655440000</code></pre>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">CORS Support</h3>
                            <p>Our API supports Cross-Origin Resource Sharing (CORS) with the following settings:</p>
                            <ul class="list-disc ml-6 mb-2">
                                <li>Access-Control-Allow-Origin: *</li>
                                <li>Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS</li>
                                <li>Access-Control-Allow-Headers: Content-Type, Authorization</li>
                            </ul>
                            <p>This allows you to make requests from any origin, including browser-based applications.</p>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Input Sanitization</h3>
                            <p>All input fields in your request are automatically trimmed to remove leading and trailing whitespace.</p>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Security</h3>
                            <p>Our API implements several security measures:</p>
                            <ul class="list-disc ml-6">
                                <li>Strict security headers to protect against common web vulnerabilities</li>
                                <li>CSRF protection on web routes, with exemption for API routes</li>
                                <li>Rate limiting to protect against abuse</li>
                                <li>Exception handling to prevent sensitive information leakage</li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Error Handling</h3>
                            <p>Our API provides detailed error messages and consistent error formats to help you troubleshoot issues. All errors include:</p>
                            <ul class="list-disc ml-6">
                                <li>HTTP status code that reflects the nature of the error</li>
                                <li>Machine-readable error code for programmatic handling</li>
                                <li>Human-readable error message</li>
                                <li>Detailed validation errors when applicable</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
