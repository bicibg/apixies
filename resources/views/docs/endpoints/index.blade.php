@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'API Endpoints']
        ];
    @endphp

    <h1 class="text-3xl font-bold mb-6">API Endpoints</h1>

    <p class="text-lg mb-8">
        Browse all available API endpoints by category. Click on any endpoint to view detailed documentation,
        parameters, response examples, and to try it out directly in your browser.
    </p>

    @include('docs.partials.endpoints')
@endsection
