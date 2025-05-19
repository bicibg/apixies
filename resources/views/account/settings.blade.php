@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')
    <div class="container max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-navy mb-6 text-center">Account Settings</h1>

        @if(session('status'))
            <div class="alert-success mb-6">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
            <!-- Left Column - User Info and Navigation (Fixed) -->
            <div class="col-span-1">
                <div class="md:sticky" style="top: 80px;">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <!-- User Profile Header -->
                        <div class="p-6 bg-gradient-to-r from-navy to-teal text-white">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-white/30 flex items-center justify-center text-white font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div>
                                    <h2 class="text-lg font-medium">{{ auth()->user()->name }}</h2>
                                    <p class="text-sm text-blue-100">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Menu -->
                        <div class="p-4">
                            <nav class="space-y-1">
                                <a href="#profile" class="block px-3 py-2 rounded-md bg-blue-50 text-teal font-medium">
                                    Profile Information
                                </a>
                                <a href="#password" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-blue-50 hover:text-teal">
                                    Update Password
                                </a>
                                <a href="#delete" class="block px-3 py-2 rounded-md text-red-600 hover:bg-red-50">
                                    Delete Account
                                </a>
                            </nav>
                        </div>
                    </div>

                    <!-- Account Status Card -->
                    <div class="mt-6 bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Account Status</h3>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Account created</span>
                                <span class="text-sm font-medium">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">API Requests</span>
                                <span class="text-sm font-medium">{{ $apiRequestCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Main Content -->
            <div class="col-span-2 space-y-6">
                <!-- Profile Information -->
                <div id="profile" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-navy mb-4">Profile Information</h2>
                    <p class="text-sm text-gray-600 mb-4">Update your account profile information.</p>

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-input w-full rounded-md @error('name') border-red-500 @enderror"
                                value="{{ old('name', auth()->user()->name) }}"
                                required
                            >

                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-input w-full rounded-md @error('email') border-red-500 @enderror"
                                value="{{ old('email', auth()->user()->email) }}"
                                required
                            >

                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Update Password -->
                <div id="password" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-navy mb-4">Update Password</h2>
                    <p class="text-sm text-gray-600 mb-4">Ensure your account is using a secure password.</p>

                    <form method="POST" action="{{ route('profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input
                                type="password"
                                name="current_password"
                                id="current_password"
                                class="form-input w-full rounded-md @error('current_password') border-red-500 @enderror"
                                required
                            >

                            @error('current_password')
                            <p class="mt-1 text-sm text-red-600">The password is incorrect.</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-input w-full rounded-md @error('password') border-red-500 @enderror"
                                required
                            >

                            @error('password')
                            <p class="mt-1 text-sm text-red-600">The password is incorrect.</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-input w-full rounded-md"
                                required
                            >
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Delete Account Card -->
                <div id="delete" class="bg-white rounded-lg shadow p-6 border-t-4 border-red-500">
                    <h2 class="text-lg font-medium text-red-600 mb-4">Delete Account</h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Once your account is deleted, all of its resources and data will be permanently deleted.
                        Before deleting your account, please download any data or information that you wish to retain.
                    </p>

                    <button
                        type="button"
                        id="open-delete-modal"
                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
                    >
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal (Step 1) -->
    <div id="delete-modal-step1" class="fixed inset-0 flex items-center justify-center hidden z-50">
        <!-- Backdrop with blur effect -->
        <div class="absolute inset-0 bg-navy-dark bg-opacity-90" id="modal-backdrop-step1"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 z-10">
            <div class="p-6">
                <h3 class="text-xl font-bold text-red-600 mb-4">Delete Account</h3>

                <p class="text-gray-600 mb-6">
                    This action is permanent and cannot be undone. All your data, API keys, and account information will be permanently removed.
                </p>

                <div class="flex justify-between mt-6">
                    <button
                        type="button"
                        id="cancel-delete-step1"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-md transition-colors"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        id="confirm-delete-step1"
                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
                    >
                        I understand, delete my account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal (Step 2) -->
    <div id="delete-modal-step2" class="fixed inset-0 flex items-center justify-center hidden z-50">
        <!-- Backdrop with blur effect -->
        <div class="absolute inset-0 bg-navy-dark bg-opacity-90" id="modal-backdrop-step2"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 z-10">
            <div class="p-6">
                <h3 class="text-xl font-bold text-red-600 mb-4">Delete Account</h3>

                <p class="text-gray-600 mb-6">
                    This action is permanent and cannot be undone. All your data, API keys, and account information will be permanently removed.
                </p>

                <form method="POST" action="{{ route('profile.destroy') }}" id="delete-account-form">
                    @csrf
                    @method('DELETE')

                    <div class="mb-5">
                        <label for="delete_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Type "DELETE" to confirm</label>
                        <input
                            type="text"
                            name="delete_confirmation"
                            id="delete_confirmation"
                            class="form-input w-full p-2 rounded border border-gray-300 @error('delete_confirmation') border-red-500 @enderror"
                            placeholder="DELETE"
                            required
                        >

                        @error('delete_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="delete_password"
                            class="form-input w-full p-2 rounded border border-gray-300 @error('password') border-red-500 @enderror"
                            placeholder="Enter your current password to confirm"
                            required
                            autocomplete="new-password"> <!-- This prevents autofill -->

                        @error('password')
                        <p class="mt-1 text-sm text-red-600">The password is incorrect.</p>
                        @enderror
                    </div>

                    <div class="flex justify-between mt-6">
                        <button
                            type="button"
                            id="cancel-delete-step2"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-md transition-colors"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            id="confirm-delete-step2"
                            class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
                            disabled
                        >
                            Permanently Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for modals and navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Step 1 Modal elements
            const modalStep1 = document.getElementById('delete-modal-step1');
            const modalBackdropStep1 = document.getElementById('modal-backdrop-step1');
            const openModalButton = document.getElementById('open-delete-modal');
            const cancelButtonStep1 = document.getElementById('cancel-delete-step1');
            const confirmButtonStep1 = document.getElementById('confirm-delete-step1');

            // Step 2 Modal elements
            const modalStep2 = document.getElementById('delete-modal-step2');
            const modalBackdropStep2 = document.getElementById('modal-backdrop-step2');
            const cancelButtonStep2 = document.getElementById('cancel-delete-step2');
            const confirmButtonStep2 = document.getElementById('confirm-delete-step2');
            const deleteConfirmField = document.getElementById('delete_confirmation');

            // Function to open step 1 modal
            function openStep1Modal() {
                modalStep1.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            // Function to close step 1 modal
            function closeStep1Modal() {
                modalStep1.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            // Function to open step 2 modal
            function openStep2Modal() {
                closeStep1Modal();
                modalStep2.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            // Function to close step 2 modal
            function closeStep2Modal() {
                modalStep2.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                if (deleteConfirmField) {
                    deleteConfirmField.value = ''; // Clear the confirmation field
                }
                confirmButtonStep2.disabled = true; // Reset button state
            }

            // Open step 1 modal when delete button is clicked
            if (openModalButton) {
                openModalButton.addEventListener('click', openStep1Modal);
            }

            // Close step 1 modal when cancel button is clicked
            if (cancelButtonStep1) {
                cancelButtonStep1.addEventListener('click', closeStep1Modal);
            }

            // Open step 2 modal when confirm button in step 1 is clicked
            if (confirmButtonStep1) {
                confirmButtonStep1.addEventListener('click', openStep2Modal);
            }

            // Close step 2 modal when cancel button is clicked
            if (cancelButtonStep2) {
                cancelButtonStep2.addEventListener('click', closeStep2Modal);
            }

            // Close step 1 modal when clicking outside of it
            if (modalBackdropStep1) {
                modalBackdropStep1.addEventListener('click', closeStep1Modal);
            }

            // Close step 2 modal when clicking outside of it
            if (modalBackdropStep2) {
                modalBackdropStep2.addEventListener('click', closeStep2Modal);
            }

            // Enable/disable delete button based on confirmation text
            if (deleteConfirmField) {
                deleteConfirmField.addEventListener('input', function() {
                    confirmButtonStep2.disabled = this.value !== 'DELETE';
                });
            }

            // Navigation highlighting based on scroll
            const sections = document.querySelectorAll('#profile, #password, #delete');
            const navLinks = document.querySelectorAll('.p-4 nav a');

            function setActiveLink() {
                let currentSection = 'profile';

                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.offsetHeight;

                    if (window.scrollY >= sectionTop - 100) {
                        currentSection = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('bg-blue-50', 'text-teal', 'font-medium');
                    link.classList.add('text-gray-700', 'hover:bg-blue-50', 'hover:text-teal');

                    if (link.getAttribute('href') === '#' + currentSection) {
                        link.classList.remove('text-gray-700');
                        link.classList.add('bg-blue-50', 'text-teal', 'font-medium');

                        // Special case for delete link
                        if (currentSection === 'delete') {
                            link.classList.remove('text-gray-700');
                            link.classList.add('text-red-600');
                        }
                    }
                });
            }

            // Add smooth scrolling to nav links
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);

                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });

                    // Update URL hash without scrolling
                    history.pushState(null, null, targetId);

                    // Update active state
                    setActiveLink();
                });
            });

            // Set active link on scroll
            window.addEventListener('scroll', setActiveLink);

            // Set initial active link
            setActiveLink();
        });
    </script>
@endsection
