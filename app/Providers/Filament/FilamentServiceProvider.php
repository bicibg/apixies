<?php

namespace App\Providers\Filament;

use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register hooks for rendering widgets in the API stats page
        FilamentView::registerRenderHook(
            'api-stats.overview',
            function (array $parameters): string {
                $widget = $parameters['widget'] ?? null;
                if ($widget) {
                    return Blade::render('<x-filament::widget :widget="$widget"/>', ['widget' => $widget]);
                }
                return '<div class="p-4 bg-gray-50 rounded text-center text-gray-500">No widget registered</div>';
            }
        );

        FilamentView::registerRenderHook(
            'api-stats.sandbox',
            function (array $parameters): string {
                $widget = $parameters['widget'] ?? null;
                if ($widget) {
                    return Blade::render('<x-filament::widget :widget="$widget"/>', ['widget' => $widget]);
                }
                return '<div class="p-4 bg-gray-50 rounded text-center text-gray-500">No widget registered</div>';
            }
        );

        FilamentView::registerRenderHook(
            'api-stats.logs',
            function (array $parameters): string {
                $widget = $parameters['widget'] ?? null;
                if ($widget) {
                    return Blade::render('<x-filament::widget :widget="$widget"/>', ['widget' => $widget]);
                }
                return '<div class="p-4 bg-gray-50 rounded text-center text-gray-500">No widget registered</div>';
            }
        );
    }
}
