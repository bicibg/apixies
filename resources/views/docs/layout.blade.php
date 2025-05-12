{{-- docs/layout.blade.php --------------------------------------------------}}
@extends('layouts.app')

@section('title', $title ?? 'Apixies API Documentation')

@section('content')
    {{-- accessibility skip link --}}
    <a href="#docs-main"
       class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2
              focus:bg-white focus:text-blue-700 focus:ring focus:ring-blue-600 rounded">
        Skip to main content
    </a>

    <main id="docs-main" class="max-w-7xl mx-auto px-4 py-8" role="main">
        @yield('content-body')
    </main>
@endsection

@stack('scripts')
