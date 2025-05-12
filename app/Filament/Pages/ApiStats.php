<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ApiLogsTable;
use App\Filament\Widgets\ApiStatsOverview;
use Filament\Pages\Page;

class ApiStats extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Dashboard';
    protected static ?int    $navigationSort  = 0;
    protected static ?string $title           = 'API Stats';

    protected static ?string $slug  = 'api-stats';           // URL = /admin/api-stats
    protected static string  $view  = 'filament.pages.api-stats';

    /**  Header widgets (cards) */
    protected function getHeaderWidgets(): array
    {
        return [ ApiStatsOverview::class ];
    }

    /**  Footer widgets (table) */
    protected function getFooterWidgets(): array
    {
        return [ ApiLogsTable::class ];
    }
}
