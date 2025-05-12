<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class ApiStats extends Dashboard
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Dashboard';
    protected static ?int    $navigationSort  = 0;

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ApiStatsOverview::class,
        ];
    }
}
