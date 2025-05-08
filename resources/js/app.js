// resources/js/app.js
import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Check token on every page load
    checkAuthToken();

    // Set up form handlers
    setupLogoutForm();

    // Initialize API documentation tabs
    initApiDocumentationTabs();

    // Initialize API endpoint search
    initApiEndpointSearch();

    // Set up copy functionality for API tokens
    setupCopyApiToken();

    /**
     * Handle logout form submission
     */
    function setupLogoutForm() {
        const logoutForm = document.querySelector('form[action*="logout"]');
        if (!logoutForm) return;

        logoutForm.addEventListener('submit', () => {
            localStorage.removeItem('APIToken');
        });
    }

    /**
     * Set up copy functionality for API tokens
     */
    function setupCopyApiToken() {
        const copyButton = document.querySelector('.copy-token-btn');
        if (!copyButton) return;

        copyButton.addEventListener('click', () => {
            const tokenElement = document.querySelector('.token-display');
            if (!tokenElement) return;

            // Copy token to clipboard
            navigator.clipboard.writeText(tokenElement.textContent.trim())
                .then(() => {
                    // Show success message
                    const originalText = copyButton.textContent;
                    copyButton.textContent = 'Copied!';

                    // Reset button text after 2 seconds
                    setTimeout(() => {
                        copyButton.textContent = originalText;
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy text: ', err);
                });
        });
    }

    /**
     * Check for stored API token and update UI if needed
     */
    function checkAuthToken() {
        const token = localStorage.getItem('APIToken');
        if (token) {
            console.log('User has API token');
        }
    }

    /**
     * Set up tab functionality for API documentation
     */
    function initApiDocumentationTabs() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        if (!tabButtons.length) return;

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Deactivate all tabs
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'text-[#0A2240]', 'border-b-2', 'border-[#0A2240]');
                    btn.classList.add('text-gray-500');
                });

                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Activate clicked tab
                button.classList.add('active', 'text-[#0A2240]', 'border-b-2', 'border-[#0A2240]');
                button.classList.remove('text-gray-500');

                const tabId = button.dataset.tab;
                const tabContent = document.getElementById(tabId);

                if (tabContent) {
                    tabContent.classList.remove('hidden');
                }
            });
        });
    }

    /**
     * Set up search functionality for API endpoints
     */
    function initApiEndpointSearch() {
        const searchInput = document.getElementById('endpoint-search');
        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('.endpoint-row');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });

            // Show message if no results
            const noResults = document.getElementById('no-search-results');

            if (noResults) {
                if (query && ![...rows].some(row => row.style.display !== 'none')) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }
        });

        // Add clear button functionality
        const clearButton = document.getElementById('clear-search');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
            });
        }
    }
});
