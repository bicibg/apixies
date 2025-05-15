@extends('docs.layout')

@section('docs-content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-3">{{ $apiRoute['title'] ?? $key }}</h1>
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
            <nav class="flex endpoint-tabs">
                <button class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="activateTab(this, 'overview-panel')">
                    Overview
                </button>
                <button class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="activateTab(this, 'parameters-panel')">
                    Parameters
                </button>
                <button class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="activateTab(this, 'responses-panel')">
                    Response
                </button>
                <button class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="activateTab(this, 'examples-panel')">
                    Examples
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Tab contents here... -->
            <!-- Overview Tab -->
            <div class="tab-panel" id="overview-panel">
                <p class="mb-4">
                    {{ $apiRoute['description'] ?? 'No detailed description available.' }}
                </p>

                <h3 class="text-lg font-medium mb-2">Rate Limiting</h3>
                <p class="mb-4">
                    Standard rate limits apply to this endpoint.
                </p>

                <h3 class="text-lg font-medium mb-2">Authentication</h3>
                <p>
                    This endpoint can be accessed in sandbox mode without authentication, or via a valid API key in production.
                </p>
            </div>

            <!-- Parameters Tab -->
            <div class="tab-panel hidden" id="parameters-panel">
                <!-- Parameters content here... -->
                @if((isset($apiRoute['route_params']) && count($apiRoute['route_params']) > 0) || (isset($apiRoute['query_params']) && count($apiRoute['query_params']) > 0))
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
            <div class="tab-panel hidden" id="responses-panel">
                <!-- Responses content here... -->
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-700">Response details for this API endpoint.</p>
                </div>

                @if(isset($apiRoute['response_example']) && !empty($apiRoute['response_example']))
                    <div class="mt-4">
                        <h3 class="text-lg font-medium mb-2">Example Response</h3>
                        <div class="bg-gray-100 p-4 rounded-md overflow-x-auto">
                            <pre class="text-sm"><code>{{ json_encode($apiRoute['response_example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Examples Tab -->
            <div class="tab-panel hidden" id="examples-panel">
                <!-- Examples content here... -->
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium mb-2">Example Usage</h3>
                    <p class="text-gray-700 mb-4">Here's how to use this endpoint in various languages:</p>

                    <div class="mt-4">
                        <h4 class="text-md font-medium mb-2">cURL</h4>
                        <div class="bg-gray-100 p-4 rounded-md overflow-x-auto">
                            <pre><code>curl -X {{ $apiRoute['method'] ?? 'GET' }} \
    "{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}" \
    -H "X-Sandbox-Token: YOUR_SANDBOX_TOKEN"</code></pre>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="text-md font-medium mb-2">JavaScript</h4>
                        <div class="bg-gray-100 p-4 rounded-md overflow-x-auto">
                            <pre><code>fetch("{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}", {
    method: "{{ $apiRoute['method'] ?? 'GET' }}",
    headers: {
        "Content-Type": "application/json",
        "X-Sandbox-Token": "YOUR_SANDBOX_TOKEN"
    }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error("Error:", error));</code></pre>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="text-md font-medium mb-2">PHP</h4>
                        <div class="bg-gray-100 p-4 rounded-md overflow-x-auto">
                            <pre><code>$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "{{ config('app.url') }}/{{ $apiRoute['uri'] ?? '' }}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-Sandbox-Token: "YOUR_SANDBOX_TOKEN"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;</code></pre>
                        </div>
                    </div>
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

    <script>
        // Immediate inline script execution (no DOMContentLoaded needed)
        function activateTab(button, panelId) {
            console.log('Tab clicked:', panelId);

            // Hide all panels
            var panels = document.querySelectorAll('.tab-panel');
            for (var i = 0; i < panels.length; i++) {
                panels[i].classList.add('hidden');
            }

            // Show the selected panel
            var selectedPanel = document.getElementById(panelId);
            if (selectedPanel) {
                selectedPanel.classList.remove('hidden');
            }

            // Update button states
            var buttons = document.querySelectorAll('.tab-button');
            for (var i = 0; i < buttons.length; i++) {
                buttons[i].classList.remove('active', 'text-[#0A2240]', 'border-[#0A2240]', 'border-b-2');
                buttons[i].classList.add('text-gray-500', 'border-transparent');
            }

            // Activate clicked button
            button.classList.add('active', 'text-[#0A2240]', 'border-[#0A2240]', 'border-b-2');
            button.classList.remove('text-gray-500', 'border-transparent');
        }

        // Activate first tab immediately
        (function() {
            var firstButton = document.querySelector('.tab-button');
            if (firstButton) {
                firstButton.click();
            }
        })();
    </script>
@endsection
