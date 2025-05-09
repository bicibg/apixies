@extends('layouts.app')

@section('title','Apixies API Documentation')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        {{-- Hero --}}
        @include('docs.partials.hero')

        {{-- Tabs Container --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
            {{-- Tab Buttons --}}
            <div class="flex border-b overflow-x-auto">
                <button
                    class="tab-btn active px-6 py-3 font-medium text-[#0A2240] border-b-2 border-[#0A2240]"
                    data-tab="endpoints"
                >API Endpoints</button>
                <button
                    class="tab-btn px-6 py-3 font-medium text-gray-500"
                    data-tab="authentication"
                >Authentication</button>
                <button
                    class="tab-btn px-6 py-3 font-medium text-gray-500"
                    data-tab="examples"
                >Examples</button>
                <button
                    class="tab-btn px-6 py-3 font-medium text-gray-500"
                    data-tab="responses"
                >Response Format</button>
                <button
                    class="tab-btn px-6 py-3 font-medium text-gray-500"
                    data-tab="features"
                >API Features</button>
            </div>

            {{-- Tab Panes --}}
            <div id="endpoints"      class="tab-content p-6">
                @include('docs.partials.endpoints')
            </div>
            <div id="authentication" class="tab-content hidden p-6">
                @include('docs.partials.authentication')
            </div>
            <div id="examples"       class="tab-content hidden p-6">
                @include('docs.partials.examples')
            </div>
            <div id="responses"      class="tab-content hidden p-6">
                @include('docs.partials.responses')
            </div>
            <div id="features"       class="tab-content hidden p-6">
                @include('docs.partials.features')
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs    = document.querySelectorAll('.tab-btn');
            const panes   = document.querySelectorAll('.tab-content');

            function activate(tabId) {
                tabs.forEach(t => {
                    if (t.dataset.tab === tabId) {
                        t.classList.add('active','text-[#0A2240]','border-b-2','border-[#0A2240]');
                        t.classList.remove('text-gray-500');
                    } else {
                        t.classList.remove('active','text-[#0A2240]','border-b-2','border-[#0A2240]');
                        t.classList.add('text-gray-500');
                    }
                });
                panes.forEach(p => {
                    p.id === tabId ? p.classList.remove('hidden') : p.classList.add('hidden');
                });
            }

            // Show endpoints on first load
            activate('endpoints');

            tabs.forEach(t => {
                t.addEventListener('click', () => activate(t.dataset.tab));
            });
        });
    </script>
@endpush
