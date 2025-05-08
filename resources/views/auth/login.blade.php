@extends('layouts.app')

@section('title','Log In – Apixies')

@section('content')
    <div class="auth-card">
        <h2 class="text-3xl font-bold text-center text-[#0A2240] mb-2">
            Welcome Back
        </h2>
        <p class="text-center text-gray-600 mb-6">
            Enter your credentials to access your API keys.
        </p>

        <div id="login-error" class="text-red-600 text-sm mb-4"></div>

        <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-gray-700 mb-1">Email Address</label>
                <input name="email" type="email" required class="form-input"/>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Password</label>
                <input name="password" type="password" required class="form-input"/>
            </div>

            <button type="submit" class="btn-primary">
                Log In
            </button>

            <p class="mt-4 text-center text-gray-600 text-sm">
                Don’t have an account?
                <a href="{{ url('/register') }}" class="text-blue-600 hover:underline">
                    Sign Up
                </a>
            </p>
        </form>
    </div>
@endsection
