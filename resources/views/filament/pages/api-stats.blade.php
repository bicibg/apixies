<x-filament-panels::page>
    <div class="text-center text-lg text-gray-500 mb-6">
        Summary of API usage across the platform.
    </div>

    <!-- Overall API Statistics Section -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Overall API Statistics</h2>
        <p class="text-sm text-gray-500 mb-4">Combined statistics for production and sandbox</p>

        @livewire(\App\Filament\Widgets\ApiStatsOverview::class)
    </div>

    <!-- Sandbox Environment Section -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Sandbox Environment</h2>
        <p class="text-sm text-gray-500 mb-4">Data for sandbox token usage only</p>

        @livewire(\App\Filament\Widgets\SandboxStatsWidget::class)
    </div>

    <!-- API Logs Section -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">API Logs</h2>
        <p class="text-sm text-gray-500 mb-4">Recent API requests</p>

        @livewire(\App\Filament\Widgets\ApiLogsTable::class)
    </div>
</x-filament-panels::page>
