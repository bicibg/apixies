@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'Code Examples']
        ];
    @endphp

    <h2 class="card-heading">Code Examples</h2>

    @include('docs.partials.examples')
@endsection
