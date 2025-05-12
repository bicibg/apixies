<div class="mb-4 flex justify-between items-center">
    <h2 class="text-xl font-semibold text-[#0A2240]">Available Endpoints</h2>
    <div class="relative">
        <input id="endpoint-search" type="text" placeholder="Search endpoints…"
               class="px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500">
        <svg class="h-5 w-5 absolute right-3 top-2.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
             viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round"
                                                             stroke-width="2" d="M21 21l-4.35-4.35M9.5 17a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
    </div>
</div>

<table class="api-table w-full text-sm">
    <thead>
    <tr>
        <th class="py-3 px-4">Method</th>
        <th class="py-3 px-4">URI</th>
        <th class="py-3 px-4">Description</th>
        <th class="py-3 px-4">Query Params</th>
    </tr>
    </thead>
    <tbody id="endpoint-table-body">
    @foreach($routes as $route)
        <tr class="endpoint-row">
            <td class="py-3 px-4">
                <span class="method-badge {{ strtolower($route['method']) }}">{{ $route['method'] }}</span>
            </td>
            <td class="py-3 px-4">
                <a href="{{ route('docs.show', $route['uri']) }}" class="text-blue-600 hover:underline">
                    /{{ $route['uri'] }}
                </a>
            </td>
            <td class="py-3 px-4">{{ $route['description'] }}</td>
            <td class="py-3 px-4">
                @forelse($route['query_params'] as $param)
                    <span class="param-badge">{{ $param }}</span>
                @empty
                    <span class="text-gray-400">None</span>
                @endforelse
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div id="no-search-results" class="hidden py-8 text-center text-gray-500">No endpoints match your search.</div>
