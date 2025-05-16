@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'API Endpoints']
        ];
    @endphp

    <h1 class="text-3xl font-bold mb-6">API Endpoints</h1>

    <div id="api-intro-section" class="text-lg mb-8">
        <p class="mb-4">
            Discover Apixies.io's powerful collection of developer tools in one place. Our comprehensive API endpoints
            help you build better, more secure applications with minimal effort.
        </p>
        <p>
            Explore our endpoints by category below. Each endpoint includes detailed documentation,
            parameter specifications, and interactive examples you can test right in your browser.
        </p>
    </div>

    @include('docs.partials.endpoints')
@endsection
