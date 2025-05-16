<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

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
}
