{{-- resources/views/docs/layout.blade.php --}}
@extends('layouts.app')

@section('title', 'API Documentation')

@section('content')
    <div class="container mx-auto px-4 py-8">
        @yield('docs-content')
    </div>
@endsection

@push('styles')
    <style>
        /* Additional docs-specific styles that don't need to be in the main CSS file */
        .docs-section {
            scroll-margin-top: 2rem;
        }

        .tab-btn.active {
            color: #0A2240;
            border-bottom: 2px solid #0A2240;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Initialize tabs for code examples
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            if (tabButtons.length === 0) return;

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Deactivate all tabs
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'text-[#0A2240]', 'border-b-2', 'border-[#0A2240]');
                        btn.classList.add('text-gray-500');
                    });

                    // Hide all panes
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Activate this tab
                    button.classList.add('active', 'text-[#0A2240]', 'border-b-2', 'border-[#0A2240]');
                    button.classList.remove('text-gray-500');

                    // Show its pane
                    const tabId = button.dataset.tab;
                    const pane = document.getElementById(tabId);
                    if (pane) {
                        pane.classList.remove('hidden');
                    }
                });
            });
        });
    </script>
@endpush
