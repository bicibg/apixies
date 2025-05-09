@extends('layouts.app')

@section('title', 'Endpoint: ' . $route['uri'])

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Hero Section for this Endpoint -->
        <div class="api-hero p-8 mb-10 bg-gradient-to-r from-[#0A2240] to-[#007C91] text-white rounded-lg">
            <h1 class="text-3xl font-bold mb-3">
                {{ strtoupper($route['method']) }} /{{ $route['uri'] }}
            </h1>
            <p class="text-xl opacity-90">{{ $route['description'] }}</p>
        </div>

        <!-- Route Parameters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold text-[#0A2240] mb-4">Route Parameters</h2>
            @if(count($route['route_params']))
                <ul class="list-disc ml-6">
                    @foreach($route['route_params'] as $param)
                        <li><code class="font-mono">{{ $param }}</code></li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-600">None</p>
            @endif
        </div>

        <!-- Query Parameters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold text-[#0A2240] mb-4">Query Parameters</h2>
            @if(count($route['query_params']))
                <ul class="list-disc ml-6">
                    @foreach($route['query_params'] as $param)
                        <li><code class="font-mono">{{ $param }}</code></li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-600">None</p>
            @endif
        </div>

        <!-- cURL Example Request -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold text-[#0A2240] mb-4">Example Request</h2>
            <pre class="bg-gray-50 p-4 rounded"><code>curl -X {{ explode('|', $route['method'])[0] }} \
  https://{{ request()->getHost() }}/{{ $route['uri'] }} \
  -H 'Authorization: Bearer YOUR_API_KEY'</code></pre>
        </div>

        <!-- Example Response -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-[#0A2240] mb-4">Example Response</h2>
            <pre class="bg-gray-50 p-4 rounded"><code>{{ json_encode($route['example_response'], JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    </div>
@endsection
