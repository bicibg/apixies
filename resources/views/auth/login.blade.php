@extends('layouts.app')

@section('title','Log In – Apixies')

@section('content')
    <div class="auth-card">
        <h2 class="text-3xl font-bold text-center text-[#0A2240] mb-2">
            Welcome Back
        </h2>
        <p class="text-center text-gray-600 mb-6">
            Log in to manage your API keys
        </p>

        @if (session('status'))
            <div class="mb-4 p-2 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="login-error" class="text-red-600 text-sm mb-4"></div>

        <form id="login-form" method="POST" action="{{ route('login.submit') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-gray-700 mb-1">Email Address</label>
                <input name="email" type="email" required class="form-input" value="{{ old('email') }}"/>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Password</label>
                <input name="password" type="password" required class="form-input"/>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="form-checkbox"/>
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
                <a href="{{ route('password.forgot') }}" class="text-sm text-blue-600 hover:underline">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="btn-primary">
                Log In
            </button>

            <p class="mt-4 text-center text-gray-600 text-sm">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Sign Up</a>
            </p>
        </form>
    </div>
@endsection
