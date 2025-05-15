@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'API Endpoints', 'url' => route('docs.endpoints.index')],
            ['label' => $apiRoute['title'] ?? $endpointKey]
        ];
    @endphp

    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-3">{{ $apiRoute['title'] ?? $endpointKey }}</h1>
        <p class="text-lg text-gray-700">{{ $apiRoute['description'] ?? 'No description available' }}</p>

        <div class="flex items-center mt-4">
            <span class="method-badge {{ strtolower(explode('|', $apiRoute['method'] ?? 'GET')[0]) }} mr-3">
                {{ $apiRoute['method'] ?? 'GET' }}
            </span>
            <code class="bg-gray-100 px-3 py-1 rounded-md text-sm font-mono">
                {{ $apiRoute['uri'] ?? '/' }}
            </code>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <div class="border-b border-gray-200">
            <nav class="tab-nav">
                <button class="tab-btn active" data-tab="overview">
                    Overview
                </button>
                <button class="tab-btn" data-tab="parameters">
                    Parameters
                </button>
                <button class="tab-btn" data-tab="responses">
                    Response
                </button>
                <button class="tab-btn" data-tab="examples">
                    Examples
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Overview Tab -->
            <div class="tab-content active" id="overview">
                <p class="mb-4">
                    {{ $apiRoute['description'] ?? 'No detailed description available.' }}
                </p>

                <h3 class="text-lg font-medium mb-2">Rate Limiting</h3>
                <p class="mb-4">
                    Standard rate limits apply to this endpoint. See our <a href="#" class="text-blue-600 hover:underline">rate limiting documentation</a> for more details.
                </p>

                <h3 class="text-lg font-medium mb-2">Authentication</h3>
                <p>
                    This endpoint can be accessed in sandbox mode without authentication, or via a valid API key in production.
                </p>
            </div>

            <!-- Parameters Tab -->
            <div class="tab-content hidden" id="parameters">
                @if(
                    (isset($apiRoute['route_params']) && count($apiRoute['route_params']) > 0) ||
                    (isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0)
                )
                    @if(isset($apiRoute['route_params']) && count($apiRoute['route_params']) > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-3">URL Parameters</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 api-table">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Parameter
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Required
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($apiRoute['route_params'] as $param)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <code>{{ $param }}</code>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                string
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                        Required
                                                    </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                The {{ $param }} parameter
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if(isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-3">Query Parameters</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 api-table">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Parameter
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Required
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($apiRoute['query_params'] as $param)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <code>{{ $param }}</code>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                string
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                        Required
                                                    </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                The {{ $param }} parameter
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-700">This endpoint does not require any parameters.</p>
                    </div>
                @endif
            </div>

            <!-- Responses Tab -->
            <div class="tab-content hidden" id="responses">
                <h3 class="text-lg font-medium mb-3">Successful Response</h3>

                <div class="mb-6">
                    <div class="mb-2 flex">
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-md">
                            Status: 200 OK
                        </span>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="code-block language-json">{{ isset($apiRoute['response_example']) ? json_encode($apiRoute['response_example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '{ "status": "success", "data": {} }' }}</pre>
                    </div>
                </div>

                <h3 class="text-lg font-medium mb-3">Error Responses</h3>

                <div class="mb-6">
                    <div class="mb-2 flex">
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-md">
                            Status: 400 Bad Request
                        </span>
                    </div>

                    <p class="mb-2 text-gray-700">Invalid request parameters.</p>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="code-block language-json">{
  "status": "error",
  "http_code": 400,
  "code": "INVALID_REQUEST",
  "message": "Invalid request parameters",
  "errors": {
    "url": ["The url field is required."]
  }
}</pre>
                    </div>
                </div>
            </div>

            <!-- Examples Tab -->
            <div class="tab-content hidden" id="examples">
                <div class="border-b border-gray-200 mb-6">
                    <nav class="tab-nav">
                        <button class="tab-btn active" data-code="curl">
                            cURL
                        </button>
                        <button class="tab-btn" data-code="javascript">
                            JavaScript
                        </button>
                        <button class="tab-btn" data-code="php">
                            PHP
                        </button>
                    </nav>
                </div>

                <!-- cURL Example -->
                <div class="code-example active" id="curl-example">
                    <pre class="code-block language-bash">curl -X {{ $apiRoute['method'] ?? 'GET' }} "{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}@if(isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0)?{{ $apiRoute['query_params'][0] }}=example.com@endif" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY"</pre>
                </div>

                <!-- JavaScript Example -->
                <div class="code-example hidden" id="javascript-example">
                    <pre class="code-block language-javascript">// Using fetch API
async function callApi() {
  try {
    const response = await fetch('{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}@if(isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0)?{{ $apiRoute['query_params'][0] }}=example.com@endif', {
      method: '{{ $apiRoute['method'] ?? 'GET' }}',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_API_KEY'
      }
    });

    const data = await response.json();
    console.log(data);
  } catch (error) {
    console.error('Error:', error);
  }
}

callApi();</pre>
                </div>

                <!-- PHP Example -->
                <div class="code-example hidden" id="php-example">
                    <pre class="code-block language-php">&lt;?php

$url = '{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}@if(isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0)?{{ $apiRoute['query_params'][0] }}=example.com@endif';

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => '{{ $apiRoute['method'] ?? 'GET' }}',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer YOUR_API_KEY'
    ]
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo 'cURL Error: ' . $err;
} else {
    $data = json_decode($response, true);
    print_r($data);
}
</pre>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4">Try It Yourself</h2>
            <p class="mb-4">
                Test this endpoint directly from your browser using our API sandbox.
                No authentication required for testing.
            </p>

            <x-demo-modal :route="[
                'uri' => $apiRoute['uri'] ?? '',
                'method' => $apiRoute['method'] ?? 'GET',
                'route_params' => $apiRoute['route_params'] ?? [],
                'query_params' => $apiRoute['query_params'] ?? []
            ]" />
        </div>
    </div>
@endsection

@push('doc-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab navigation for the endpoint details
            var tabButtons = document.querySelectorAll('.tab-btn');
            var tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(function(btn) {
                        btn.classList.remove('active');
                    });

                    tabContents.forEach(function(content) {
                        content.classList.remove('active');
                        content.classList.add('hidden');
                    });

                    // Add active class to clicked button and corresponding content
                    button.classList.add('active');
                    var tabId = button.getAttribute('data-tab');
                    var tabContent = document.getElementById(tabId);
                    tabContent.classList.remove('hidden');
                    tabContent.classList.add('active');
                });
            });

            // Code example tabs
            var codeTabButtons = document.querySelectorAll('[data-code]');
            var codeExamples = document.querySelectorAll('.code-example');

            codeTabButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and examples
                    codeTabButtons.forEach(function(btn) {
                        btn.classList.remove('active');
                    });

                    codeExamples.forEach(function(example) {
                        example.classList.remove('active');
                        example.classList.add('hidden');
                    });

                    // Add active class to clicked button and corresponding example
                    button.classList.add('active');
                    var codeId = button.getAttribute('data-code');
                    var codeExample = document.getElementById(codeId + '-example');
                    codeExample.classList.remove('hidden');
                    codeExample.classList.add('active');
                });
            });
        });
    </script>
@endpush
