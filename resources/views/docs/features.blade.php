@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'API Features']
        ];
    @endphp

    <h1 class="text-3xl font-bold mb-6">API Features</h1>

    @include('docs.partials.features')
@endsection
