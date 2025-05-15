<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;
use Illuminate\Support\Facades\DB;

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

        $topEndpointsText = $topEndpoints->map(function ($endpoint) {
            $path = $endpoint->endpoint;
            $shortPath = strlen($path) > 25 ? '...' . substr($path, -22) : $path;
            return "{$shortPath}: " . number_format($endpoint->total_count);
        })->join(', ');

        // Get count of unique API keys/users
        $uniqueUsers = ApiEndpointLog::where('is_sandbox', false)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

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

            // Top Endpoints
            Card::make('Top Endpoints', $topEndpointsText)
                ->color('warning'),

            // User counts
            Card::make('Users & Tokens', $uniqueUsers . ' users')
                ->description($sandboxTokens . ' sandbox tokens')
                ->color('danger'),
        ];
    }
}
