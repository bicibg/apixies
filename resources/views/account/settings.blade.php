@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')
    <div class="container max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-navy mb-6">Account Settings</h1>

        @if(session('status'))
            <div class="alert-success mb-6">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Sidebar -->
            <div class="col-span-1">
                <div class="bg-white rounded-lg shadow overflow-hidden">
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

                    <div class="p-4">
                        <nav class="space-y-1">
                            <a href="#profile" class="block px-3 py-2 rounded-md bg-blue-50 text-teal font-medium">
                                Profile Information
                            </a>
                            <a href="#password" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-blue-50 hover:text-teal">
                                Update Password
                            </a>
                            <a href="#tokens" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-blue-50 hover:text-teal">
                                API Keys
                            </a>
                            <a href="#delete" class="block px-3 py-2 rounded-md text-red-600 hover:bg-red-50">
                                Delete Account
                            </a>
                        </nav>
                    </div>
                </div>

                <div class="mt-6 bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Account Status</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Account created</span>
                            <span class="text-sm font-medium">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">API Requests</span>
                            <span class="text-sm font-medium">{{ auth()->user()->api_requests_count ?? 0 }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">API Keys</span>
                            <span class="text-sm font-medium">{{ auth()->user()->apiKeys()->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
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
                            <button type="submit" class="btn-primary">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Update Password -->
                <div id="password" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-navy mb-4">Update Password</h2>
                    <p class="text-sm text-gray-600 mb-4">Ensure your account is using a secure password.</p>

                    <form method="POST" action="{{ route('password.update') }}">
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
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                            <button type="submit" class="btn-primary">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- API Tokens -->
                <div id="tokens" class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-medium text-navy">API Keys</h2>
                        <a href="{{ route('api-keys.index') }}" class="text-sm text-teal hover:text-teal-700">Manage API Keys →</a>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Manage your API keys for accessing Apixies services.</p>

                    @if(auth()->user()->apiKeys()->exists())
                        <div class="border border-gray-200 rounded-md overflow-hidden mb-4">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(auth()->user()->apiKeys()->latest()->take(3)->get() as $key)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-navy">{{ $key->name }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $key->created_at->format('M d, Y') }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('api-keys.show', $key) }}" class="text-teal hover:text-teal-700">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-blue-50 p-4 rounded-md">
                            <p class="text-sm text-blue-700">You don't have any API keys yet. Create one to start using Apixies APIs.</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('api-keys.create') }}" class="btn-primary inline-block">
                            Create New API Key
                        </a>
                    </div>
                </div>

                <!-- Delete Account -->
                <div id="delete" class="bg-white rounded-lg shadow p-6 border-t-4 border-red-500">
                    <h2 class="text-lg font-medium text-red-600 mb-4">Delete Account</h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Once your account is deleted, all of its resources and data will be permanently deleted.
                        Before deleting your account, please download any data or information that you wish to retain.
                    </p>

                    <form method="POST" action="{{ route('profile.destroy') }}" class="mt-5"
                          onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')

                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password_confirmation"
                                class="form-input w-full rounded-md @error('password') border-red-500 @enderror"
                                placeholder="Enter your current password to confirm"
                                required
                            >

                            @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                Delete Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
