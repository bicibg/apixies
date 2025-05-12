@php($title = 'Apixies API Docs')
@extends('docs.layout')

@section('content-body')
    {{-- █ Hero banner with CTA buttons (kept) --}}
    @include('docs.partials.hero', [
        'title'    => 'Apixies API',
        'subtitle' => 'Build powerful applications with our simple, reliable API',
        'showCta'  => true,
    ])

    {{-- █ Tabs + Suggest button --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        {{-- Tab bar (class names align with app.js) --}}
        <div class="flex items-center justify-between border-b px-4 py-2">
            <nav class="flex text-sm font-medium overflow-x-auto">
                @foreach([
                    'endpoints'      => 'API Endpoints',
                    'authentication' => 'Authentication',
                    'examples'       => 'Examples',
                    'responses'      => 'Response Format',
                    'features'       => 'Features'
                ] as $id => $label)
                    <button data-tab="{{ $id }}"
                            class="tab-btn px-5 py-3 border-b-2 border-transparent text-gray-500 transition">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>
        {{-- █ Tab panes ----------------------------------------------------}}
        <section id="endpoints"      class="tab-content p-6">@include('docs.partials.endpoints')</section>
        <section id="authentication" class="tab-content p-6 hidden">@include('docs.partials.authentication')</section>
        <section id="examples"       class="tab-content p-6 hidden">@include('docs.partials.examples')</section>
        <section id="responses"      class="tab-content p-6 hidden">@include('docs.partials.responses')</section>
        <section id="features"       class="tab-content p-6 hidden">@include('docs.partials.features')</section>
    </div>
@endsection
