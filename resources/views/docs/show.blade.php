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
            <nav class="tab-nav flex">
                <button class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="overview">
                    Overview
                </button>
                <button class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="parameters">
                    Parameters
                </button>
                <button class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="responses">
                    Response
                </button>
                <button class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="examples">
                    Examples
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Tab contents here... -->
            <!-- Overview Tab -->
            <div class="tab-content" id="overview">
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
            <div class="tab-content hidden" id="parameters">
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
                        <!-- Query parameters table here... -->
                    @endif
                @else
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-700">This endpoint does not require any parameters.</p>
                    </div>
                @endif
            </div>

            <!-- Responses Tab -->
            <div class="tab-content hidden" id="responses">
                <!-- Responses content here... -->
            </div>

            <!-- Examples Tab -->
            <div class="tab-content hidden" id="examples">
                <!-- Examples content here... -->
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

@push('scripts')
    <script src="{{ asset('js/tabs.js') }}"></script>
@endpush
