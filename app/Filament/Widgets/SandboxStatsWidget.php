<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class SandboxStatsWidget extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        // Get sandbox usage data
        $sandboxCalls = ApiEndpointCount::where('is_sandbox', true)->sum('count');

        // Get health/ready counts
        $healthReadyCount = ApiEndpointCount::where('is_sandbox', true)
            ->where(function($query) {
                $query->where('endpoint', 'like', '%health%')
                    ->orWhere('endpoint', 'like', '%ready%');
            })
            ->sum('count');

        $otherSandboxCount = $sandboxCalls - $healthReadyCount;

        // Get sandbox token usage
        $sandboxTokens = DB::table('sandbox_tokens')
            ->selectRaw('
                COUNT(*) as total_tokens,
                SUM(calls) as total_calls,
                AVG(calls) as avg_calls_per_token,
                COUNT(CASE WHEN calls >= quota THEN 1 END) as exhausted_tokens,
                SUM(quota) as total_quota,
                ROUND(SUM(calls) * 100.0 / NULLIF(SUM(quota), 0), 1) as quota_usage_percent
            ')
            ->first();

        // Top sandbox endpoints
        $topSandboxEndpoints = ApiEndpointCount::where('is_sandbox', true)
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();

        // Format endpoints as HTML for proper display
        $topEndpointsHtml = '';

        if ($topSandboxEndpoints->isEmpty()) {
            $topEndpointsHtml = '<div class="text-center text-gray-500 py-2">No sandbox data yet</div>';
        } else {
            foreach ($topSandboxEndpoints as $endpoint) {
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
                    number_format($endpoint->count)
                );
            }
        }

        // Return cards with sandbox statistics
        return [
            Card::make('Sandbox Calls', number_format($sandboxCalls))
                ->description(
                    'Health/Ready: ' . number_format($healthReadyCount) .
                    ' | Other: ' . number_format($otherSandboxCount)
                )
                ->color('primary'),

            Card::make('Sandbox Tokens', number_format($sandboxTokens->total_tokens ?? 0))
                ->description(
                    'Used: ' . number_format($sandboxTokens->total_calls ?? 0) .
                    ' of ' . number_format($sandboxTokens->total_quota ?? 0) .
                    ' (' . ($sandboxTokens->quota_usage_percent ?? 0) . '%)'
                )
                ->color($sandboxTokens->quota_usage_percent > 75 ? 'danger' : 'success'),

            // Top Endpoints - as HTML
            Card::make('Top Sandbox Endpoints', new HtmlString('<div class="space-y-1">' . $topEndpointsHtml . '</div>'))
                ->color('warning'),
        ];
    }
}
