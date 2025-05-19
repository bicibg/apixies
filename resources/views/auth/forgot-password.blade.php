@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
    <div class="container max-w-lg mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-xl font-semibold text-navy mb-4">Reset Your Password</h1>

            @if(session('status'))
                <div class="alert-success mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-input w-full rounded-md @error('email') border-red-500 @enderror"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        autofocus
                    >

                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('login') }}" class="text-sm text-teal hover:text-teal-700">
                        Back to login
                    </a>

                    <button type="submit" class="btn-primary">
                        Send Password Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
