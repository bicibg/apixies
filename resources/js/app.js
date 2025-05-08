// resources/js/app.js
import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Check token on every page load
    checkAuthToken();

    // Set up form handlers
    setupSignupForm();
    setupLoginForm();
    setupLogoutForm();

    // Initialize API documentation tabs
    initApiDocumentationTabs();

    // Initialize API endpoint search
    initApiEndpointSearch();

    /**
     * Handle signup form submission
     */
    function setupSignupForm() {
        const signupForm = document.getElementById('signup-form');
        if (!signupForm) return;

        const errorBox = document.getElementById('signup-error');
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorBox.textContent = '';

            try {
                const formData = Object.fromEntries(new FormData(signupForm));
                const response = await apiRequest('/api/v1/register', 'POST', formData);

                // Create web session and redirect
                await createWebSession(response.data?.token || null);
                window.location = '/';
            } catch (err) {
                displayError(err, errorBox);
            }
        });
    }

    /**
     * Handle login form submission
     */
    function setupLoginForm() {
        const loginForm = document.getElementById('login-form');
        if (!loginForm) return;

        const errorBox = document.getElementById('login-error');
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorBox.textContent = '';

            try {
                const formData = Object.fromEntries(new FormData(loginForm));
                const response = await apiRequest('/api/v1/login', 'POST', formData);

                // Create web session and redirect
                await createWebSession(response.data?.token || null);
                window.location = '/';
            } catch (err) {
                displayError(err, errorBox);
            }
        });
    }

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
     * Centralized API request function
     * @param {string} url - API endpoint
     * @param {string} method - HTTP method
     * @param {Object} data - Request payload
     * @returns {Promise<Object>} - Response data
     */
    async function apiRequest(url, method, data) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data),
        });

        const json = await response.json();

        if (!response.ok) {
            throw json;
        }

        return json;
    }

    /**
     * Display error message in the specified element
     * @param {Object} err - Error object
     * @param {HTMLElement} errorElement - Element to display error
     */
    function displayError(err, errorElement) {
        const message = err.errors?.name?.[0] ||
            err.errors?.email?.[0] ||
            err.errors?.password?.[0] ||
            err.message ||
            'An error occurred';

        errorElement.textContent = message;
    }

    /**
     * Create a web session using the provided token
     * @param {string|null} token - API token
     */
    async function createWebSession(token) {
        if (!token) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            const response = await fetch('/auth/session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ token }),
            });

            if (!response.ok) {
                console.error('Failed to create web session');
            }
        } catch (err) {
            console.error('Error creating web session:', err);
        }
    }

    /**
     * Check for stored API token and update UI if needed
     */
    function checkAuthToken() {
        const token = localStorage.getItem('APIToken');
        if (token) {
            console.log('User has API token');
            // Additional UI updates can be added here if needed
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
