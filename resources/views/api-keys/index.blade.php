@extends('layouts.app')

@section('title', 'API Keys - Apixies')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-navy">Your API Keys</h1>

                <button
                    id="create-key-button"
                    class="btn-gradient"
                >
                    Create New API Key
                </button>
            </div>

            <!-- Success message -->
            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <!-- New token display -->
            @if (session('new_token'))
                <div class="alert-info">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">API Key Created</h3>
                    <p class="text-sm text-gray-700 mb-1">
                        Name: <span class="font-medium">{{ session('new_token')['name'] }}</span>
                    </p>
                    <div class="mb-3">
                        <p class="text-sm text-gray-700 mb-1">
                            Token (copy this now, it won't be shown again):
                        </p>
                        <div class="relative">
                            <input
                                id="token-display"
                                type="text"
                                value="{{ session('new_token')['token'] }}"
                                class="w-full p-2 pr-20 border border-gray-300 rounded bg-gray-50 font-mono text-sm"
                                readonly
                            >
                            <button
                                id="copy-token-btn"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-blue-600 hover:text-blue-800 text-sm"
                                onclick="copyToken()"
                            >
                                Copy to clipboard
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-red-600">
                        <strong>Important:</strong> This is the only time your token will be visible. Please copy it now and store it securely.
                    </p>
                </div>
            @endif

            <!-- API Keys Table -->
            <div class="overflow-x-auto">
                @if ($apiKeys->isEmpty())
                    <div class="alert-info mb-6">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">Get Started with API Keys</h3>
                        <p class="text-gray-700 mb-2">
                            API keys allow your applications to authenticate with our API services. Create your first API key to start building!
                        </p>
                        <p class="text-gray-700">
                            After creating an API key, you'll need to include it in your requests as a Bearer token in the Authorization header.
                        </p>
                    </div>
                @endif
                <table class="min-w-full bg-white">
                    <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Created</th>
                        <th class="py-3 px-4 text-left">Last Used</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-700">
                    @forelse ($apiKeys as $key)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $key->name }}</td>
                            <td class="py-3 px-4">{{ $key->created_at->format('M d, Y H:i') }}</td>
                            <td class="py-3 px-4">
                                {{ $key->last_used_at ? $key->last_used_at->format('M d, Y H:i') : 'Never used' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <form action="{{ route('api-keys.destroy', $key->uuid) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-red-600 hover:text-red-800 cursor-pointer"
                                        onclick="return confirm('Are you sure you want to revoke this API key?')"
                                    >
                                        Revoke
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500">
                                No API keys found. Create your first one!
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create API Key Modal -->
    <div id="create-key-modal" class="fixed inset-0 flex items-center justify-center hidden z-50">
        <!-- Transparent overlay with blur -->
        <div class="absolute inset-0 modal-backdrop"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md relative z-10">
            <!-- Modal header -->
            <div class="flex justify-between items-center p-5 border-b">
                <h2 class="text-lg font-medium text-gray-800">Create New API Key</h2>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                              clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <!-- Modal body -->
            <div class="p-5">
                <form action="{{ route('api-keys.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="key-name" class="block text-sm font-medium text-gray-700 mb-1">Key Name</label>
                        <input
                            type="text"
                            id="key-name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="e.g., Development, Production, etc."
                            class="form-input @error('name') border-red-500 @enderror"
                            required
                        >
                        @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Give your API key a descriptive name to identify its use.
                        </p>
                    </div>

                    <!-- Modal footer -->
                    <div class="flex justify-end mt-6">
                        <button
                            type="button"
                            id="cancel-create"
                            class="btn-gray mr-3"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn-blue"
                        >
                            Create API Key
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('create-key-modal');
            const modalOverlay = modal.querySelector('.modal-backdrop');
            const openBtn = document.getElementById('create-key-button');
            const closeBtn = document.getElementById('close-modal');
            const cancelBtn = document.getElementById('cancel-create');

            function openModal() {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }

            // Bind handlers
            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);
            modalOverlay.addEventListener('click', e => {
                if (e.target === modalOverlay) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Re-open on validation errors
            @if ($errors->any())
            openModal();
            @endif
        });

        function copyToken() {
            const tokenInput = document.getElementById('token-display');
            const copyBtn    = document.getElementById('copy-token-btn');

            tokenInput.select();
            document.execCommand('copy');

            const originalText = copyBtn.innerText;
            copyBtn.innerText = 'Copied!';

            setTimeout(() => {
                copyBtn.innerText = originalText;
            }, 2000);
        }
    </script>
@endsection
