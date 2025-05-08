@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4 space-y-8">
        {{-- Aggregate counts --}}
        <div>
            <h1 class="text-2xl font-bold mb-4">API Endpoint Usage (Counts)</h1>
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

        {{-- Detailed logs --}}
        <div>
            <h1 class="text-2xl font-bold mb-4">Recent API Calls (Logs)</h1>
            <table class="min-w-full border table-auto text-sm">
                <thead>
                <tr class="bg-gray-200">
                    <th class="px-2 py-1">Time</th>
                    <th class="px-2 py-1">Endpoint</th>
                    <th class="px-2 py-1">User ID</th>
                    <th class="px-2 py-1">User Name</th>
                    <th class="px-2 py-1">API Key ID</th>
                    <th class="px-2 py-1">IP Address</th>
                    <th class="px-2 py-1">User Agent</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr class="border-t">
                        <td class="px-2 py-1">{{ $log->created_at }}</td>
                        <td class="px-2 py-1">{{ $log->endpoint }}</td>
                        <td class="px-2 py-1">{{ $log->user_id ?? '—' }}</td>
                        <td class="px-2 py-1">{{ $log->user_name ?? 'Guest' }}</td>
                        <td class="px-2 py-1">{{ $log->api_key_id ?? '—' }}</td>
                        <td class="px-2 py-1">{{ $log->ip_address }}</td>
                        <td class="px-2 py-1">{{ Str::limit($log->user_agent, 40) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
