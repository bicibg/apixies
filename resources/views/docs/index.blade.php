{{-- resources/views/docs/index.blade.php --}}
@section('docs-title','Apixies API')
@section('docs-subtitle','Build powerful applications with our simple, reliable API')
@section('docs-hero-buttons')
    @auth
        <a href="{{ route('api-keys.index') }}"
           class="inline-block bg-white text-[#0A2240] px-5 py-2 rounded-md font-medium hover:bg-gray-100 transition mr-4">
            Manage API Keys
        </a>
    @else
        <a href="{{ route('login') }}"
           class="inline-block bg-white text-[#0A2240] px-5 py-2 rounded-md font-medium hover:bg-gray-100 transition mr-4">
            Log In
        </a>
        <a href="{{ route('register') }}"
           class="inline-block bg-teal-500 text-white px-5 py-2 rounded-md font-medium hover:bg-teal-600 transition">
            Sign Up for API Access
        </a>
    @endauth
@endsection

@extends('docs.layout')

@section('docs-endpoints')
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Available Endpoints</h2>
    <div class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full">
            <thead class="bg-gray-100 text-gray-600 uppercase text-sm">
            <tr>
                <th class="py-3 px-4 text-left">Method</th>
                <th class="py-3 px-4 text-left">URI</th>
                <th class="py-3 px-4 text-left">Description</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @foreach($routes as $route)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4">
              <span class="method-badge {{ strtolower($route['method']) }}">
                {{ $route['method'] }}
              </span>
                    </td>
                    <td class="py-3 px-4 font-mono text-sm">
                        <a href="{{ route('docs.endpoints.show', ['key'=>$route['uri']]) }}"
                           class="text-blue-600 hover:underline">
                            {{ $route['uri'] }}
                        </a>
                    </td>
                    <td class="py-3 px-4">{{ $route['description'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('docs-authentication')
    @include('docs.partials.authentication')
@endsection

@section('docs-examples')
    @include('docs.partials.examples')
@endsection

@section('docs-responses')
    @include('docs.partials.responses')
@endsection

@section('docs-features')
    @include('docs.partials.features')
@endsection
