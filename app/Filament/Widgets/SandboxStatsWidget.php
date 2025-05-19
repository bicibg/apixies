<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Carbon;

class SandboxStatsWidget extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        // Get sandbox usage data
        $sandboxLogs = ApiEndpointLog::whereNotNull('sandbox_token_id')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $sandboxCalls = $sandboxLogs->count();

        // Get health/ready counts
        $healthReadyCount = $sandboxLogs->filter(function($log) {
            return str_contains($log->endpoint, 'health') || str_contains($log->endpoint, 'ready');
        })->count();

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

        // Calculate error rate for sandbox
        $sandboxErrors = $sandboxLogs->filter(function($log) {
            return $log->response_code >= 400;
        });

        $errorRate = $sandboxCalls > 0 ? round(($sandboxErrors->count() / $sandboxCalls) * 100, 2) : 0;

        // Calculate latency stats
        $avgLatency = $sandboxLogs->avg('latency_ms') ?: 0;
        $maxLatency = $sandboxLogs->max('latency_ms') ?: 0;

        // Sandbox usage by hour of day
        $timeOfDayUsage = $this->getTimeOfDayDistribution($sandboxLogs);
        $peakHour = !empty($timeOfDayUsage) ? array_search(max($timeOfDayUsage), $timeOfDayUsage) : null;
        $peakHourFormatted = $peakHour !== null ?
            Carbon::createFromFormat('H', $peakHour)->format('g A') . ' - ' .
            Carbon::createFromFormat('H', $peakHour)->addHour()->format('g A') :
            'N/A';

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

        // Status code distribution for sandbox
        $statusDistribution = $sandboxLogs->groupBy('response_code')
            ->map->count()
            ->sortKeys();

        $statusHtml = '';

        if ($statusDistribution->isEmpty()) {
            $statusHtml = '<div class="text-center text-gray-500 py-2">No status data yet</div>';
        } else {
            foreach ($statusDistribution as $code => $count) {
                $colorClass = 'bg-success-100 text-success-800';

                if ($code >= 400 && $code < 500) {
                    $colorClass = 'bg-warning-100 text-warning-800';
                } else if ($code >= 500) {
                    $colorClass = 'bg-danger-100 text-danger-800';
                } else if ($code >= 300) {
                    $colorClass = 'bg-info-100 text-info-800';
                }

                $statusHtml .= sprintf(
                    '<div class="flex items-center justify-between py-1 border-b border-gray-100">
                        <div class="px-2 py-1 rounded-md text-xs font-medium %s">%s</div>
                        <div class="font-semibold">%s</div>
                    </div>',
                    $colorClass,
                    $code,
                    number_format($count)
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
                ->descriptionIcon('heroicon-s-beaker')
                ->color('primary'),

            Card::make('Sandbox Tokens', number_format($sandboxTokens->total_tokens ?? 0))
                ->description(
                    'Used: ' . number_format($sandboxTokens->total_calls ?? 0) .
                    ' of ' . number_format($sandboxTokens->total_quota ?? 0) .
                    ' (' . ($sandboxTokens->quota_usage_percent ?? 0) . '%)'
                )
                ->descriptionIcon('heroicon-s-key')
                ->color($sandboxTokens->quota_usage_percent > 75 ? 'danger' : 'success'),

            // Top Endpoints - as HTML
            Card::make('Top Sandbox Endpoints', new HtmlString('<div class="space-y-1">' . $topEndpointsHtml . '</div>'))
                ->color('warning'),

            // Error Rate Card
            Card::make('Sandbox Error Rate', $errorRate . '%')
                ->description(
                    $sandboxErrors->count() . ' errors out of ' . $sandboxCalls . ' requests'
                )
                ->descriptionIcon('heroicon-s-exclamation-triangle')
                ->color($errorRate > 5 ? 'danger' : ($errorRate > 1 ? 'warning' : 'success')),

            // Latency Card
            Card::make('Sandbox Latency', number_format($avgLatency) . 'ms avg')
                ->description(
                    'Max: ' . number_format($maxLatency) . 'ms'
                )
                ->descriptionIcon('heroicon-s-bolt')
                ->color($avgLatency > 500 ? 'danger' : ($avgLatency > 200 ? 'warning' : 'success')),

            // Status Distribution
            Card::make('Status Codes', new HtmlString('<div class="space-y-1">' . $statusHtml . '</div>'))
                ->description('Response status distribution')
                ->descriptionIcon('heroicon-s-clipboard-document-check')
                ->color('info'),

            // Peak Usage
            Card::make('Peak Usage Time', $peakHourFormatted)
                ->description(
                    'Most active period for sandbox'
                )
                ->descriptionIcon('heroicon-s-clock')
                ->color('secondary'),
        ];
    }

    /**
     * Get time of day distribution
     */
    protected function getTimeOfDayDistribution($logs): array
    {
        return $logs->groupBy(function ($log) {
            return Carbon::parse($log->created_at)->format('H');
        })->map->count()->toArray();
    }
}
