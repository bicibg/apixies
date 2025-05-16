@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'Response Format']
        ];
    @endphp

    <h2 class="card-heading">Response Format</h2>

    @include('docs.partials.responses')
@endsection
