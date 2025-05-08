@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">API Endpoint Usage</h1>
        <table class="min-w-full border">
            <thead>
            <tr class="bg-gray-200">
                <th class="px-4 py-2 text-left">Endpoint</th>
                <th class="px-4 py-2 text-right">Count</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $row)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $row->endpoint }}</td>
                    <td class="px-4 py-2 text-right">{{ $row->count }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
