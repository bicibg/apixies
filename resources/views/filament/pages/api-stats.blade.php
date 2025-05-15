<x-filament-panels::page>
    <div class="text-center text-lg text-gray-500 mb-6">
        Summary of API usage across the platform.
    </div>

    {{-- Section headings are minimal and can be removed if desired --}}
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-700">Overall API Statistics</h2>
    </div>

    {{ $this->widgets[0] ?? null }}

    <div class="mt-8 mb-4">
        <h2 class="text-lg font-semibold text-gray-700">Sandbox Environment</h2>
    </div>

    {{ $this->widgets[1] ?? null }}

    <div class="mt-8 mb-4">
        <h2 class="text-lg font-semibold text-gray-700">API Logs</h2>
    </div>
</x-filament-panels::page>
