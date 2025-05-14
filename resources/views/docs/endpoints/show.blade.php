{{-- resources/views/docs/endpoints/show.blade.php --}}
@extends('docs.layout')

@section('docs-content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left sidebar for navigation -->
        <div class="lg:col-span-1">
            @include('docs.partials.navigation')
        </div>

        <!-- Main content area -->
        <div class="lg:col-span-3">
            <div class="mb-6">
                <!-- Back button -->
                <a href="{{ route('docs.endpoints.index') }}"
                   class="text-blue-600 hover:text-blue-800 flex items-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to API endpoints
                </a>

                <div class="bg-gradient-to-r from-[#0A2240] to-[#007C91] text-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-3">
                        <span class="px-3 py-1 text-xs font-semibold rounded-md bg-white {{ isset($apiRoute['method']) && $apiRoute['method'] === 'GET' ? 'text-green-800' : 'text-blue-800' }} mr-3">
                            {{ $apiRoute['method'] ?? 'GET' }}
                        </span>
                        <h1 class="text-2xl font-bold">{{ $apiRoute['title'] ?? 'API Endpoint' }}</h1>
                    </div>
                    <p class="text-xl opacity-90 mb-4">{{ $apiRoute['description'] ?? '' }}</p>
                    <code class="bg-white/10 rounded px-3 py-2 text-white font-mono">/{{ $apiRoute['uri'] ?? '' }}</code>
                </div>
            </div>

            <!-- API route details -->
            <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                <h2 class="text-xl font-bold mb-4">Endpoint Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">METHOD</h3>
                        <p class="mt-1 font-medium">{{ $apiRoute['method'] ?? 'GET' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">ENDPOINT</h3>
                        <p class="mt-1 font-medium">/{{ $apiRoute['uri'] ?? '' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">CATEGORY</h3>
                        <p class="mt-1 font-medium capitalize">{{ $apiRoute['category'] ?? 'system' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">DEMO AVAILABLE</h3>
                        <p class="mt-1 font-medium">{{ ($apiRoute['demo'] ?? false) ? 'Yes' : 'No' }}</p>
                    </div>
                </div>

                <!-- Try it out button -->
                @if(($apiRoute['demo'] ?? false) && isset($apiRoute['uri']))
                    <div class="mb-6">
                        <x-demo-modal :route="[
                            'uri' => $apiRoute['uri'] ?? '',
                            'method' => $apiRoute['method'] ?? 'GET',
                            'route_params' => $apiRoute['route_params'] ?? [],
                            'query_params' => $apiRoute['query_params'] ?? []
                        ]" />
                    </div>
                @endif

                <!-- Endpoint parameters -->
                @if(isset($apiRoute['route_params']) || isset($apiRoute['query_params']))
                    <h3 class="font-semibold text-lg mt-8 mb-4">Parameters</h3>

                    @if(isset($apiRoute['route_params']) && count($apiRoute['route_params']) > 0)
                        <h4 class="font-medium text-gray-700 mt-4 mb-2">Route Parameters</h4>
                        <div class="overflow-x-auto mb-6">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Parameter</th>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Required</th>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                @foreach($apiRoute['route_params'] as $param)
                                    <tr>
                                        <td class="py-3 px-4 font-mono text-sm text-blue-600">{{ $param }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-900">Yes</td>
                                        <td class="py-3 px-4 text-sm text-gray-900">Route parameter for {{ $param }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if(isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0)
                        <h4 class="font-medium text-gray-700 mt-4 mb-2">Query Parameters</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Parameter</th>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Required</th>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                @foreach($apiRoute['query_params'] as $param)
                                    <tr>
                                        <td class="py-3 px-4 font-mono text-sm text-blue-600">{{ $param }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-900">Yes</td>
                                        <td class="py-3 px-4 text-sm text-gray-900">
                                            @if($param === 'url')
                                                The URL to inspect
                                            @elseif($param === 'email')
                                                The email address to inspect
                                            @elseif($param === 'user_agent')
                                                The user agent string to inspect
                                            @elseif($param === 'html')
                                                The HTML content to convert to PDF
                                            @else
                                                Parameter description
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif

                <!-- Response example -->
                @if(isset($apiRoute['response_example']))
                    <h3 class="font-semibold text-lg mt-8 mb-4">Example Response</h3>
                    <div class="bg-gray-50 p-4 rounded">
                        <pre class="text-sm overflow-x-auto">{{ is_array($apiRoute['response_example']) ? json_encode($apiRoute['response_example'], JSON_PRETTY_PRINT) : $apiRoute['response_example'] }}</pre>
                    </div>
                @endif
            </div>

            <!-- Implementation examples -->
            <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                <h2 class="text-xl font-bold mb-4">Implementation Examples</h2>

                <!-- Example tabs -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 tab-nav">
                        <button class="tab-btn active" data-tab="curl">cURL</button>
                        <button class="tab-btn" data-tab="php">PHP</button>
                        <button class="tab-btn" data-tab="javascript">JavaScript</button>
                        <button class="tab-btn" data-tab="python">Python</button>
                    </nav>
                </div>

                <!-- Example panes -->
                <div class="py-4">
                    <!-- cURL Example -->
                    <div id="curl" class="tab-content">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto text-sm">
@if(isset($apiRoute['method']) && $apiRoute['method'] === 'GET')
                                curl -X GET "{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}@if(isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0)?{{ implode('=example&', $apiRoute['query_params']) }}=example@endif" \
                                -H "Authorization: Bearer YOUR_API_KEY"
                            @else
                                curl -X POST "{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}" \
                                -H "Authorization: Bearer YOUR_API_KEY" \
                                -H "Content-Type: application/json" \
                                -d '{
                                @if(isset($apiRoute['query_params']))
                                    @foreach($apiRoute['query_params'] as $param)
                                        "{{ $param }}": "example"@if(!$loop->last),@endif
                                    @endforeach
                                @endif
                                }'
                            @endif</pre>
                    </div>

                    <!-- PHP Example -->
                    <div id="php" class="tab-content hidden">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto text-sm">
$apiKey = 'YOUR_API_KEY';

$client = new \GuzzleHttp\Client();
@if(isset($apiRoute['method']) && $apiRoute['method'] === 'GET')
                                $response = $client->request('GET', '{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}', [
                                'headers' => [
                                'Authorization' => 'Bearer ' . $apiKey,
                                ],
                                'query' => [
                                @if(isset($apiRoute['query_params']))
                                    @foreach($apiRoute['query_params'] as $param)
                                        '{{ $param }}' => 'example',
                                    @endforeach
                                @endif
                                ],
                                ]);
                            @else
                                $response = $client->request('POST', '{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}', [
                                'headers' => [
                                'Authorization' => 'Bearer ' . $apiKey,
                                'Content-Type' => 'application/json',
                                ],
                                'json' => [
                                @if(isset($apiRoute['query_params']))
                                    @foreach($apiRoute['query_params'] as $param)
                                        '{{ $param }}' => 'example',
                                    @endforeach
                                @endif
                                ],
                                ]);
                            @endif

$data = json_decode($response->getBody(), true);
print_r($data);</pre>
                    </div>

                    <!-- JavaScript Example -->
                    <div id="javascript" class="tab-content hidden">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto text-sm">
const apiKey = 'YOUR_API_KEY';

@if(isset($apiRoute['method']) && $apiRoute['method'] === 'GET')
                                // Build the URL with query parameters
                                const params = new URLSearchParams({
                                @if(isset($apiRoute['query_params']))
                                    @foreach($apiRoute['query_params'] as $param)
                                        '{{ $param }}': 'example',
                                    @endforeach
                                @endif
                                });

                                fetch(`{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}?${params}`, {
                                method: 'GET',
                                headers: {
                                'Authorization': `Bearer ${apiKey}`,
                                },
                                })
                            @else
                                fetch('{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}', {
                                method: 'POST',
                                headers: {
                                'Authorization': `Bearer ${apiKey}`,
                                'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                @if(isset($apiRoute['query_params']))
                                    @foreach($apiRoute['query_params'] as $param)
                                        '{{ $param }}': 'example',
                                    @endforeach
                                @endif
                                }),
                                })
                            @endif
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));</pre>
                    </div>

                    <!-- Python Example -->
                    <div id="python" class="tab-content hidden">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto text-sm">
import requests
import json

api_key = 'YOUR_API_KEY'
@if(isset($apiRoute['method']) && $apiRoute['method'] === 'GET')
                                params = {
                                @if(isset($apiRoute['query_params']))
                                    @foreach($apiRoute['query_params'] as $param)
                                        '{{ $param }}': 'example',
                                    @endforeach
                                @endif
                                }

                                response = requests.get(
                                '{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}',
                                headers={'Authorization': f'Bearer {api_key}'},
                                params=params
                                )
                            @else
                                data = {
                                @if(isset($apiRoute['query_params']))
                                    @foreach($apiRoute['query_params'] as $param)
                                        '{{ $param }}': 'example',
                                    @endforeach
                                @endif
                                }

                                response = requests.post(
                                '{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}',
                                headers={
                                'Authorization': f'Bearer {api_key}',
                                'Content-Type': 'application/json'
                                },
                                data=json.dumps(data)
                                )
                            @endif

print(response.json())</pre>
                    </div>
                </div>
            </div>

            <!-- Related endpoints section -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h2 class="text-xl font-bold mb-4">Related Endpoints</h2>

                @php
                    $apiRoutes = config('api_examples');
                    $currentCategory = $apiRoute['category'] ?? null;
                    $relatedEndpoints = collect($apiRoutes)
                        ->filter(function ($route, $key) use ($currentCategory, $apiRoute) {
                            return ($route['category'] ?? '') === $currentCategory &&
                                   ($route['uri'] ?? '') !== ($apiRoute['uri'] ?? '');
                        })
                        ->take(3);
                @endphp

                @if($relatedEndpoints->count() > 0)
                    <div class="space-y-4">
                        @foreach($relatedEndpoints as $key => $route)
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
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('docs.endpoints.index') }}#{{ $currentCategory }}" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                            View all {{ $currentCategory }} endpoints
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                @else
                    <p class="text-gray-500">No related endpoints found.</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Deactivate all tabs
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    // Hide all panes
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Activate this tab
                    button.classList.add('active');

                    // Show its pane
                    const tabId = button.dataset.tab;
                    const pane = document.getElementById(tabId);
                    if (pane) {
                        pane.classList.remove('hidden');
                    }
                });
            });
        });
    </script>
@endsection
