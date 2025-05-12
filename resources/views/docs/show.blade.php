@extends('docs.layout')

@section('title', 'Endpoint: ' . $route['uri'])

@section('content-body')
    {{-- Hero banner without CTA buttons on endpoint page --}}
    @include('docs.partials.hero', [
        'title'    => strtoupper($route['method']) . ' /' . $route['uri'],
        'subtitle' => $route['description'],
        'showCta'  => false,
    ])

    {{-- Route parameters --}}
    <section class="card">
        <h2 class="card-heading">Route Parameters</h2>
        @forelse($route['route_params'] as $p)
            <code class="param-badge">{{ $p }}</code>
        @empty
            <p class="text-gray-600">None</p>
        @endforelse
    </section>

    {{-- Query parameters --}}
    <section class="card">
        <h2 class="card-heading">Query Parameters</h2>
        @forelse($route['query_params'] as $p)
            <code class="param-badge">{{ $p }}</code>
        @empty
            <p class="text-gray-600">None</p>
        @endforelse
    </section>

    {{-- Example request --}}
    <section class="card">
        <h2 class="card-heading">Example Request</h2>
        <pre class="code-block" aria-label="cURL example"><code>curl -X {{ explode('|', $route['method'])[0] }} \
  https://{{ request()->getHost() }}/{{ $route['uri'] }} \
  -H 'Authorization: Bearer YOUR_API_KEY'</code></pre>
    </section>

    {{-- Example response --}}
    <section class="card">
        <h2 class="card-heading">Example Response</h2>
        <pre class="code-block" aria-label="JSON example"><code>{{ json_encode($route['example_response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
    </section>
@endsection
