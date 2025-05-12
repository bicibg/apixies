<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;

class ApiStatsOverview extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Requests', number_format(ApiEndpointCount::sum('count'))),

            Card::make('Avg Latency (ms)',
                round(ApiEndpointLog::where('created_at', '>=', now()->subDays(30))
                    ->avg('latency_ms'), 1)
            )->color('success')->description('last 30d'),

            Card::make('Unique API Keys',
                ApiEndpointLog::distinct('api_key_id')->count()
            )->color('primary'),
        ];
    }
}
