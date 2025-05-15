@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'Response Format']
        ];
    @endphp

    <h1 class="text-3xl font-bold mb-6">Response Format</h1>

    @include('docs.partials.responses')
@endsection
