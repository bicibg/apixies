@php($title = 'Apixies API Docs')
@extends('docs.layout')

@section('content-body')

    @include('docs.partials.hero')
    {{-- tab bar ------------------------------------------------------------}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <nav id="tab-bar" class="flex border-b text-sm font-medium overflow-x-auto">
            <button data-pane="endpoints"      class="tab-btn active">API Endpoints</button>
            <button data-pane="authentication" class="tab-btn">Authentication</button>
            <button data-pane="examples"       class="tab-btn">Examples</button>
            <button data-pane="responses"      class="tab-btn">Response Format</button>
            <button data-pane="features"       class="tab-btn">Features</button>
        </nav>

        {{-- panes (all in DOM; only one visible) ---------------------------}}
        <section id="endpoints"      class="tab-pane p-6">@include('docs.partials.endpoints')</section>
        <section id="authentication" class="tab-pane p-6 hidden">@include('docs.partials.authentication')</section>
        <section id="examples"       class="tab-pane p-6 hidden">@include('docs.partials.examples')</section>
        <section id="responses"      class="tab-pane p-6 hidden">@include('docs.partials.responses')</section>
        <section id="features"       class="tab-pane p-6 hidden">@include('docs.partials.features')</section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = [...document.querySelectorAll('#tab-bar .tab-btn')];
            const panes   = id => document.getElementById(id);

            function show(id) {
                /* switch button styles */
                buttons.forEach(b => b.classList.toggle('active', b.dataset.pane === id));
                /* show the matching pane, hide others */
                ['endpoints','authentication','examples','responses','features']
                    .forEach(pid => panes(pid).classList.toggle('hidden', pid !== id));
            }

            /* basic button styling classes */
            buttons.forEach(b => b.classList.add(
                'px-5','py-3','border-b-2','border-transparent','transition','text-gray-600'
            ));
            const activeCls = ['text-[#0A2240]','border-[#0A2240]'];

            /* click handler */
            buttons.forEach(b => b.addEventListener('click', () => {
                buttons.forEach(btn => btn.classList.remove(...activeCls));
                b.classList.add(...activeCls);
                show(b.dataset.pane);
            }));

            show('endpoints');          // default tab
        });
    </script>
@endpush
