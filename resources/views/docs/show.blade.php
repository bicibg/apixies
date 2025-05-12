{{-- docs/show.blade.php – single endpoint ---------------------------------------------------------}}
@extends('layouts.app')

@section('title', 'Endpoint: ' . $route['uri'])

@section('content')
    <a href="#endpoint-main"
       class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:bg-white focus:text-blue-700 focus:ring focus:ring-blue-600 rounded">
        Skip to main content
    </a>

    <main id="endpoint-main" class="max-w-7xl mx-auto px-4 py-8">
        {{-- hero heading --}}
        <section class="api-hero p-8 mb-10 text-white rounded-lg">
            <h1 class="text-3xl font-bold mb-3">{{ strtoupper($route['method']) }} /{{ $route['uri'] }}</h1>
            <p class="text-xl opacity-90">{{ $route['description'] }}</p>
        </section>

        {{-- route params --}}
        <section class="card">
            <h2 class="card-heading">Route Parameters</h2>
            @forelse($route['route_params'] as $p)
                <code class="param-badge">{{ $p }}</code>
            @empty
                <p class="text-gray-600">None</p>
            @endforelse
        </section>

        {{-- query params --}}
        <section class="card">
            <h2 class="card-heading">Query Parameters</h2>
            @forelse($route['query_params'] as $p)
                <code class="param-badge">{{ $p }}</code>
            @empty
                <p class="text-gray-600">None</p>
            @endforelse
        </section>

        {{-- example request --}}
        <section class="card">
            <h2 class="card-heading">Example Request</h2>
            <pre class="code-block" aria-label="cURL example" tabindex="0"><code>curl -X {{ explode('|', $route['method'])[0] }} \
  https://{{ request()->getHost() }}/{{ $route['uri'] }} \
  -H 'Authorization: Bearer YOUR_API_KEY'</code></pre>
        </section>

        {{-- example response --}}
        <section class="card">
            <h2 class="card-heading">Example Response</h2>
            <pre class="code-block" aria-label="JSON example" tabindex="0"><code>{{ json_encode($route['example_response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
        </section>
    </main>
@endsection
