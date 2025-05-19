<?php

namespace App\Filament\Widgets;

use App\Models\ApiEndpointLog;
use App\Models\SandboxToken;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ApiStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get logs (last 30 days)
        $logs = ApiEndpointLog::where('created_at', '>=', now()->subDays(30))->get();

        // Split logs by environment
        $prodLogs = $logs->filter(function($log) {
            return is_null($log->sandbox_token_id);
        });

        $sandboxLogs = $logs->filter(function($log) {
            return !is_null($log->sandbox_token_id);
        });

        // Get stats
        $totalRequests = $logs->count();
        $prodRequests = $prodLogs->count();
        $sandboxRequests = $sandboxLogs->count();

        // More robust latency metrics
        $avgLatency = $this->formatLatency($logs->avg('latency_ms'));
        $medianLatency = $this->formatLatency($this->getMedianLatency($logs));
        $p95Latency = $this->formatLatency($this->getPercentileLatency($logs, 95));
        $trimmedLatency = $this->formatLatency($this->getTrimmedMeanLatency($logs, 10));

        // Production vs Sandbox latencies
        $prodAvgLatency = $prodLogs->count() > 0 ? $this->formatLatency($prodLogs->avg('latency_ms')) : 'N/A';
        $sandboxAvgLatency = $sandboxLogs->count() > 0 ? $this->formatLatency($sandboxLogs->avg('latency_ms')) : 'N/A';
        $prodMedianLatency = $prodLogs->count() > 0 ? $this->formatLatency($this->getMedianLatency($prodLogs)) : 'N/A';
        $sandboxMedianLatency = $sandboxLogs->count() > 0 ? $this->formatLatency($this->getMedianLatency($sandboxLogs)) : 'N/A';

        // Error Rate Analysis
        $errorLogs = $logs->filter(function($log) {
            return $log->response_code >= 400;
        });
        $errorCount = $errorLogs->count();
        $errorRate = $totalRequests > 0 ? round(($errorCount / $totalRequests) * 100, 2) : 0;

        $clientErrors = $logs->filter(function($log) {
            return $log->response_code >= 400 && $log->response_code < 500;
        })->count();

        $serverErrors = $logs->filter(function($log) {
            return $log->response_code >= 500;
        })->count();

        // Trend Analysis - Current Week vs Previous Week
        $currentWeekLogs = $logs->filter(function($log) {
            return Carbon::parse($log->created_at)->isAfter(now()->subDays(7));
        });

        $previousWeekLogs = $logs->filter(function($log) {
            $date = Carbon::parse($log->created_at);
            return $date->isAfter(now()->subDays(14)) && $date->isBefore(now()->subDays(7));
        });

        $currentWeekLatency = $currentWeekLogs->avg('latency_ms');
        $previousWeekLatency = $previousWeekLogs->avg('latency_ms');

        $latencyTrend = 0;
        if ($previousWeekLatency > 0) {
            $latencyTrend = round((($currentWeekLatency - $previousWeekLatency) / $previousWeekLatency) * 100, 1);
        }

        $latencyTrendIndicator = 'heroicon-m-minus';
        $latencyTrendColor = 'primary';

        if (abs($latencyTrend) >= 1) {
            $latencyTrendIndicator = $latencyTrend > 0
                ? 'heroicon-m-arrow-trending-up'
                : 'heroicon-m-arrow-trending-down';

            $latencyTrendColor = $latencyTrend > 0 ? 'danger' : 'success';
        }

        // Top used endpoints
        $topEndpoints = ApiEndpointLog::select('endpoint')
            ->selectRaw('COUNT(*) as count, AVG(latency_ms) as avg_latency')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('endpoint')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        $topEndpointsText = $topEndpoints->map(function($endpoint) {
            $parts = explode('/', $endpoint->endpoint);
            $name = end($parts);
            return "$name: {$endpoint->count}";
        })->join(' | ');

        return [
            Stat::make('Total Requests', $totalRequests)
                ->description("Prod: $prodRequests | Sandbox: $sandboxRequests")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Median Latency (ms)', $medianLatency)
                ->description("Prod: $prodMedianLatency | Sandbox: $sandboxMedianLatency")
                ->descriptionIcon('heroicon-m-bolt')
                ->color('primary'),

            Stat::make('Avg Latency (ms)', $avgLatency)
                ->description($latencyTrend === 0
                    ? "10% Trimmed Avg: $trimmedLatency"
                    : ($latencyTrend > 0
                        ? "+$latencyTrend% vs last week"
                        : "$latencyTrend% vs last week"))
                ->descriptionIcon($latencyTrend === 0 ? 'heroicon-m-chart-bar' : $latencyTrendIndicator)
                ->color($latencyTrend === 0
                    ? $this->getLatencyColor($logs->avg('latency_ms'))
                    : $latencyTrendColor)
                ->extraAttributes(['title' => "95th Percentile: $p95Latency ms"]),

            Stat::make('Error Rate', "$errorRate%")
                ->description("$clientErrors client | $serverErrors server")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($errorRate > 5 ? 'danger' : ($errorRate > 1 ? 'warning' : 'success')),

            Stat::make('Users & Tokens', User::count())
                ->description(SandboxToken::count() . ' sandbox tokens')
                ->descriptionIcon('heroicon-m-user')
                ->color('secondary'),

            Stat::make('Top Endpoints', $topEndpoints->count() > 0 ? $topEndpoints->first()->endpoint : 'N/A')
                ->description($topEndpointsText)
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }

    /**
     * Get the median latency from a collection of logs
     */
    protected function getMedianLatency(Collection $logs): float
    {
        if ($logs->isEmpty()) {
            return 0.0; // Return 0 as float when no logs exist
        }

        $latencies = $logs->pluck('latency_ms')->filter()->sort()->values();
        $count = $latencies->count();

        if ($count === 0) {
            return 0.0;
        }

        $middle = (int) ($count / 2);

        if ($count % 2 === 0) {
            return (float)(($latencies[$middle - 1] + $latencies[$middle]) / 2);
        }

        return (float)$latencies[$middle];
    }

    /**
     * Get a percentile latency value
     */
    protected function getPercentileLatency(Collection $logs, int $percentile = 95): float
    {
        if ($logs->isEmpty()) {
            return 0.0;
        }

        $latencies = $logs->pluck('latency_ms')->filter()->sort()->values();
        $count = $latencies->count();

        if ($count === 0) {
            return 0.0;
        }

        $index = (int) ceil($percentile / 100 * $count) - 1;
        $index = max(0, min($index, $count - 1)); // Ensure index is within bounds

        return (float)$latencies[$index];
    }

    /**
     * Get a trimmed mean latency, removing outliers
     */
    protected function getTrimmedMeanLatency(Collection $logs, int $trimPercentage = 10): float
    {
        if ($logs->isEmpty()) {
            return 0.0;
        }

        $latencies = $logs->pluck('latency_ms')->filter()->sort()->values();
        $count = $latencies->count();
        if ($count === 0) {
            return 0.0;
        }

        if ($count <= 2) {
            return (float)$latencies->avg(); // Not enough data to trim
        }

        $trimCount = (int) ($count * $trimPercentage / 100);

        // Make sure we have at least one value after trimming
        if ($count - (2 * $trimCount) <= 0) {
            $trimCount = (int)($count / 4); // Limit trimming to at most 25% on each side
        }

        $trimmedLatencies = $latencies->slice($trimCount, $count - (2 * $trimCount));

        return (float)$trimmedLatencies->avg() ?: 0.0;
    }

    /**
     * Format latency for display
     */
    protected function formatLatency($latency): string
    {
        // Use more decimal places to avoid small values appearing as 0
        $value = (float)$latency;

        if ($value < 0.01 && $value > 0) {
            // For very small values, show at least 3 decimal places
            return number_format($value, 3);
        }

        return number_format(round($value, 2));
    }

    /**
     * Get color based on latency
     */
    protected function getLatencyColor($latency): string
    {
        if ($latency < 100) return 'success';
        if ($latency < 500) return 'primary';
        if ($latency < 1000) return 'warning';
        return 'danger';
    }

    /**
     * Get time of day distribution
     */
    protected function getTimeOfDayDistribution(Collection $logs): array
    {
        return $logs->groupBy(function ($log) {
            return Carbon::parse($log->created_at)->format('H');
        })->map->count()->toArray();
    }
}
