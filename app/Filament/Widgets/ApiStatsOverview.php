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
        $prodLogs = $logs->where('sandbox', false);
        $sandboxLogs = $logs->where('sandbox', true);

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
                ->description("10% Trimmed Avg: $trimmedLatency")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($this->getLatencyColor($logs->avg('latency_ms')))
                ->extraAttributes(['title' => "95th Percentile: $p95Latency ms"]),

            Stat::make('Users & Tokens', User::count())
                ->description(SandboxToken::count() . ' sandbox tokens')
                ->descriptionIcon('heroicon-m-user')
                ->color('secondary'),
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
}
