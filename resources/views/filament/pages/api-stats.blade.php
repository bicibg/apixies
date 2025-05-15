<x-filament-panels::page>
    <div class="text-center text-lg text-gray-500 mb-6">
        Summary of API usage across the platform.
    </div>

    <div class="border-t border-gray-200 pt-4 mb-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Overall API Statistics</h2>
        <p class="text-gray-500 mb-4">
            Combined statistics for both production and sandbox environments.
        </p>
    </div>

    {{ $this->widgets[0] ?? null }}

    <div class="border-t border-gray-200 pt-4 mt-8 mb-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Sandbox Environment</h2>
        <p class="text-gray-500 mb-4">
            Statistics specifically for sandbox API usage. Sandbox requests are tracked separately
            from production requests and do not affect production quotas or limits.
        </p>
    </div>

    {{ $this->widgets[1] ?? null }}

    <div class="border-t border-gray-200 pt-4 mt-8 mb-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">API Logs</h2>
        <p class="text-gray-500 mb-4">
            Recent API calls across all environments. Use the Environment filter to see only sandbox or production calls.
        </p>
    </div>
</x-filament-panels::page>
