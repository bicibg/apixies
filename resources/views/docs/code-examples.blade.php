@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'Code Examples']
        ];
    @endphp

    <h1 class="text-3xl font-bold mb-6">Code Examples</h1>

    @include('docs.partials.examples')
@endsection
