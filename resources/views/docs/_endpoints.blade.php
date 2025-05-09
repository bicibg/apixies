<div class="tab-content p-6" id="endpoints">
    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-[#0A2240]">Available Endpoints</h2>
        <div class="relative">
            <input type="text" id="endpoint-search" placeholder="Search endpoints..."
                   class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute right-3 top-2.5 text-gray-400"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-auto text-sm api-table">
            <thead>
            <tr class="bg-gray-50 text-left">
                <th class="py-3 px-4 border-b">Method</th>
                <th class="py-3 px-4 border-b">URI</th>
                <th class="py-3 px-4 border-b">Description</th>
                <th class="py-3 px-4 border-b">Route Params</th>
                <th class="py-3 px-4 border-b">Query Params</th>
            </tr>
            </thead>
            <tbody>
            @foreach($routes as $route)
                <tr class="endpoint-row border-b hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <span class="method-badge {{ strtolower($route['method']) }}">
                            {{ $route['method'] }}
                        </span>
                    </td>
                    <td class="py-3 px-4 font-mono">{{ $route['uri'] }}</td>
                    <td class="py-3 px-4">{{ $route['description'] ?? 'N/A' }}</td>
                    <td class="py-3 px-4">
                        @forelse($route['route_params'] ?? [] as $param)
                            <span class="param-badge">{{ $param }}</span>
                        @empty
                            <span class="text-gray-400">None</span>
                        @endforelse
                    </td>
                    <td class="py-3 px-4">
                        @forelse($route['query_params'] ?? [] as $param)
                            <span class="param-badge">{{ $param }}</span>
                        @empty
                            <span class="text-gray-400">None</span>
                        @endforelse
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div id="no-search-results" class="hidden py-8 text-center text-gray-500">
            No endpoints match your search. Try different keywords.
        </div>
    </div>
</div>
