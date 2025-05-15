@extends('docs.layout')

@section('docs-content')
    @php
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => 'API Endpoints']
        ];
    @endphp

    <h1 class="text-3xl font-bold mb-6">API Endpoints</h1>

    <p class="text-lg mb-8">
        Browse all available API endpoints by category. Click on any endpoint to view detailed documentation,
        parameters, response examples, and to try it out directly in your browser.
    </p>

    @include('docs.partials.endpoints')

    @push('doc-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('endpoint-search');
                const endpoints = document.querySelectorAll('.endpoint-row');
                const noResults = document.getElementById('no-search-results');
                const categories = document.querySelectorAll('.mb-8');

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    let foundAny = false;

                    // Loop through each category
                    categories.forEach(category => {
                        let categoryHasVisible = false;

                        // Find endpoints in this category
                        const categoryEndpoints = category.querySelectorAll('.endpoint-row');

                        categoryEndpoints.forEach(endpoint => {
                            const title = endpoint.querySelector('h4').textContent.toLowerCase();
                            const description = endpoint.querySelector('p').textContent.toLowerCase();
                            const uri = endpoint.querySelector('code').textContent.toLowerCase();

                            if (title.includes(searchTerm) || description.includes(searchTerm) || uri.includes(searchTerm)) {
                                endpoint.style.display = '';
                                categoryHasVisible = true;
                                foundAny = true;
                            } else {
                                endpoint.style.display = 'none';
                            }
                        });

                        // Hide/show category based on whether it has visible endpoints
                        category.style.display = categoryHasVisible ? '' : 'none';
                    });

                    // Show/hide no results message
                    noResults.style.display = foundAny ? 'none' : 'block';
                });
            });
        </script>
    @endpush
@endsection
