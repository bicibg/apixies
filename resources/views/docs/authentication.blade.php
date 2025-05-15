@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'Authentication']
        ];
    @endphp

    <h1 class="text-3xl font-bold mb-6">Authentication</h1>

    @include('docs.partials.authentication')
@endsection
