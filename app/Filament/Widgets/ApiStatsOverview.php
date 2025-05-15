<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ApiStatsOverview extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        // Get total counts
        $totalRequests = ApiEndpointCount::sum('count');
        $totalProductionRequests = ApiEndpointCount::where('is_sandbox', false)->sum('count');
        $totalSandboxRequests = ApiEndpointCount::where('is_sandbox', true)->sum('count');

        // Get average latency for the last 30 days
        $avgLatency = round(ApiEndpointLog::where('created_at', '>=', now()->subDays(30))
            ->avg('latency_ms'), 1);

        // Average latency by environment
        $avgProductionLatency = round(ApiEndpointLog::where('created_at', '>=', now()->subDays(30))
            ->where('is_sandbox', false)
            ->avg('latency_ms'), 1);

        $avgSandboxLatency = round(ApiEndpointLog::where('created_at', '>=', now()->subDays(30))
            ->where('is_sandbox', true)
            ->avg('latency_ms'), 1);

        // Get top endpoints (limit to 3)
        $topEndpoints = ApiEndpointCount::select('endpoint', DB::raw('SUM(count) as total_count'))
            ->groupBy('endpoint')
            ->orderBy('total_count', 'desc')
            ->limit(3)
            ->get();

        // Format endpoints as HTML to ensure proper display
        $topEndpointsHtml = '';
        foreach ($topEndpoints as $endpoint) {
            // Extract just the endpoint name
            $pathParts = explode('/', $endpoint->endpoint);
            $endpointName = end($pathParts);

            // Build styled HTML row
            $topEndpointsHtml .= sprintf(
                '<div class="flex justify-between py-1 border-b border-gray-100">
                    <div class="font-mono text-sm">%s:</div>
                    <div class="font-semibold">%s</div>
                </div>',
                $endpointName,
                number_format($endpoint->total_count)
            );
        }

        // FIXED: Count total registered users correctly
        $totalUsers = User::count();

        // Sandbox token count
        $sandboxTokens = DB::table('sandbox_tokens')->count();

        return [
            // Total API Requests
            Card::make('Total Requests', number_format($totalRequests))
                ->description(
                    'Prod: ' . number_format($totalProductionRequests) .
                    ' | Sandbox: ' . number_format($totalSandboxRequests)
                )
                ->color('primary'),

            // Average Latency
            Card::make('Avg Latency (ms)', number_format($avgLatency))
                ->description(
                    'Prod: ' . number_format($avgProductionLatency) .
                    ' | Sandbox: ' . number_format($avgSandboxLatency) .
                    ' (last 30d)'
                )
                ->color('success'),

            // Top Endpoints - as HTML for better structuring
            Card::make('Top Endpoints', new HtmlString('<div class="space-y-1">' . $topEndpointsHtml . '</div>'))
                ->color('warning'),

            // User counts - FIXED
            Card::make('Users & Tokens', number_format($totalUsers) . ' users')
                ->description($sandboxTokens . ' sandbox tokens')
                ->color('danger'),
        ];
    }
}
