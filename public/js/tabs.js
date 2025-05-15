// Tab Initialization Script for Detail Pages
// Save this to public/js/tabs.js

document.addEventListener('DOMContentLoaded', function() {
    console.log("Standalone tab script loaded");

    // Get all tab buttons and tab contents
    const tabButtons = document.querySelectorAll('.tab-nav .tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    console.log("Found " + tabButtons.length + " tab buttons");
    console.log("Found " + tabContents.length + " tab contents");

    // Function to activate a tab
    function activateTab(button) {
        const tabId = button.getAttribute('data-tab');
        console.log("Activating tab: " + tabId);

        // Deactivate all tabs
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('text-[#0A2240]');
            btn.classList.remove('border-[#0A2240]');
            btn.classList.remove('border-b-2');
            btn.classList.add('text-gray-500');
            btn.classList.add('border-transparent');
        });

        // Hide all tab contents
        tabContents.forEach(content => {
            content.classList.add('hidden');
        });

        // Activate selected tab
        button.classList.add('active');
        button.classList.add('text-[#0A2240]');
        button.classList.add('border-[#0A2240]');
        button.classList.add('border-b-2');
        button.classList.remove('text-gray-500');
        button.classList.remove('border-transparent');

        // Show the tab content
        const tabContent = document.getElementById(tabId);
        if (tabContent) {
            tabContent.classList.remove('hidden');
            console.log("Tab content shown: " + tabId);
        } else {
            console.error("Tab content not found: " + tabId);
        }
    }

    // Add click handlers to tab buttons
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log("Tab clicked: " + this.getAttribute('data-tab'));
            activateTab(this);
        });
    });

    // Activate first tab by default
    if (tabButtons.length > 0) {
        console.log("Activating first tab on load");
        activateTab(tabButtons[0]);
    }
});
