@extends('layouts.app')

@section('title','Apixies API Endpoints')

@section('content')
    <div class="table-container">
        <table class="min-w-full table-auto text-sm">
            <thead>
            <tr class="bg-gray-100 text-left">
                <th class="py-2 px-4 border-b">Method</th>
                <th class="py-2 px-4 border-b">URI</th>
                <th class="py-2 px-4 border-b">Description</th>
                <th class="py-2 px-4 border-b">Route Parameters</th>
                <th class="py-2 px-4 border-b">Query Parameters</th>
            </tr>
            </thead>
            <tbody>
            @foreach($routes as $route)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="py-2 px-4">{{ $route['method'] }}</td>
                    <td class="py-2 px-4">{{ $route['uri'] }}</td>
                    <td class="py-2 px-4">{{ $route['description'] ?? 'N/A' }}</td>
                    <td class="py-2 px-4">
                        @if(!empty($route['route_params']))
                            {{ implode(', ', $route['route_params']) }}
                        @else
                            None
                        @endif
                    </td>
                    <td class="py-2 px-4">
                        @if(!empty($route['query_params']))
                            {{ implode(', ', $route['query_params']) }}
                        @else
                            None
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
