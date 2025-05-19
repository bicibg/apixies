<?php

namespace App\Filament\Pages;

use App\Models\ApiEndpointLog;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ApiStats extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Dashboard';
    protected static ?int    $navigationSort  = 0;
    protected static ?string $title           = 'API Stats';

    protected static ?string $slug  = 'api-stats';
    protected static string  $view  = 'filament.pages.api-stats';

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        // Time period for reports
        $startDate = now()->subDays(30)->startOfDay();
        $endDate = now()->endOfDay();

        // Get API logs for the period
        $logs = ApiEndpointLog::whereBetween('created_at', [$startDate, $endDate])->get();

        // Status code distribution
        $statusDistribution = $logs->groupBy('response_code')
            ->map(function ($items) use ($logs) {
                $count = $items->count();
                return [
                    'count' => $count,
                    'percentage' => $logs->count() > 0 ? round(($count / $logs->count()) * 100, 1) : 0,
                ];
            })
            ->toArray();

        // Endpoint popularity
        $popularEndpoints = $logs->groupBy('endpoint')
            ->map(function ($items) {
                $totalLatency = $items->sum('latency_ms');
                $count = $items->count();

                return [
                    'count' => $count,
                    'avg_latency' => $count > 0 ? round($totalLatency / $count, 2) : 0,
                    'success_rate' => $count > 0 ?
                        round(($items->where('response_code', '<', 400)->count() / $count) * 100, 1) : 0,
                ];
            })
            ->toArray();

        // Sort by count
        arsort($popularEndpoints);

        // Get top 10
        $popularEndpoints = array_slice($popularEndpoints, 0, 10, true);

        // Usage by hour of day
        $usageByHour = $logs->groupBy(function ($log) {
            return Carbon::parse($log->created_at)->format('H');
        })->map->count()->toArray();

        // Make sure all hours are represented (0-23)
        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hour = sprintf('%02d', $i);
            $hourlyData[$hour] = $usageByHour[$hour] ?? 0;
        }

        // Usage by day of week
        $usageByDayOfWeek = $logs->groupBy(function ($log) {
            return Carbon::parse($log->created_at)->format('w'); // 0 (Sunday) to 6 (Saturday)
        })->map->count()->toArray();

        // Make sure all days are represented (0-6)
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $dailyData = [];
        for ($i = 0; $i < 7; $i++) {
            $dailyData[$dayNames[$i]] = $usageByDayOfWeek[$i] ?? 0;
        }

        // Performance over time - group by day
        $performanceByDay = $logs->groupBy(function ($log) {
            return Carbon::parse($log->created_at)->format('Y-m-d');
        })->map(function ($dayLogs) {
            return [
                'count' => $dayLogs->count(),
                'avg_latency' => $dayLogs->avg('latency_ms'),
                'error_rate' => $dayLogs->count() > 0 ?
                    ($dayLogs->where('response_code', '>=', 400)->count() / $dayLogs->count()) * 100 : 0,
            ];
        })->toArray();

        // Ensure we have data for all days in the range
        $dateRange = [];
        $currentDate = Carbon::parse($startDate);

        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');

            if (!isset($performanceByDay[$dateKey])) {
                $performanceByDay[$dateKey] = [
                    'count' => 0,
                    'avg_latency' => 0,
                    'error_rate' => 0,
                ];
            }

            $dateRange[] = $dateKey;
            $currentDate->addDay();
        }

        // Sort by date
        ksort($performanceByDay);

        // Prepare data for charts
        $chartData = [
            'dates' => $dateRange,
            'requests' => array_map(function ($date) use ($performanceByDay) {
                return $performanceByDay[$date]['count'] ?? 0;
            }, $dateRange),
            'latency' => array_map(function ($date) use ($performanceByDay) {
                return round($performanceByDay[$date]['avg_latency'] ?? 0, 2);
            }, $dateRange),
            'error_rate' => array_map(function ($date) use ($performanceByDay) {
                return round($performanceByDay[$date]['error_rate'] ?? 0, 2);
            }, $dateRange),
        ];

        return [
            'statusDistribution' => $statusDistribution,
            'popularEndpoints' => $popularEndpoints,
            'hourlyData' => $hourlyData,
            'dailyData' => $dailyData,
            'performanceOverTime' => $chartData,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];
    }
}
