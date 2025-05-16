@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'API Features']
        ];
    @endphp

    <h2 class="card-heading">API Features</h2>

    @include('docs.partials.features')
@endsection
