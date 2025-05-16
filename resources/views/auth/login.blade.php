@extends('layouts.app')

@section('title','Log In – Apixies')

@section('content')
    <div class="auth-card mt-8 sm:mt-12">
        <h2 class="text-3xl font-bold text-center text-navy mb-2">
            Welcome Back
        </h2>
        <p class="text-center text-gray-600 mb-6">
            Log in to manage your API keys
        </p>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="login-error" class="text-danger-600 text-sm mb-4"></div>

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
                <a href="#" class="text-sm text-teal hover:text-teal-700">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="btn-primary">
                Log In
            </button>

            <p class="mt-4 text-center text-gray-600 text-sm">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-teal hover:text-teal-700">Sign Up</a>
            </p>
        </form>
    </div>
@endsection
