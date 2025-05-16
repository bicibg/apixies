@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'Authentication']
        ];
    @endphp

    <h2 class="card-heading">Authentication</h2>

    @include('docs.partials.authentication')
@endsection
