@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
    <div class="container max-w-lg mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-xl font-semibold text-navy mb-4">Set New Password</h1>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-input w-full rounded-md @error('email') border-red-500 @enderror"
                        value="{{ old('email', $request->email) }}"
                        required
                        autocomplete="email"
                    >

                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-input w-full rounded-md @error('password') border-red-500 @enderror"
                        required
                        autocomplete="new-password"
                    >

                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="form-input w-full rounded-md"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="btn-primary">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
