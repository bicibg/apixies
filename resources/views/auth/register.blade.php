@extends('layouts.app')

@section('title','Sign Up – Apixies')

@section('content')
    <div class="auth-card mt-8 sm:mt-12">
        <h2 class="text-3xl font-bold text-center text-navy mb-2">
            Create Account
        </h2>
        <p class="text-center text-gray-600 mb-6">
            Sign up for free API access during our beta period
        </p>

        <!-- Benefits banner -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6 border-l-4 border-teal">
            <h3 class="font-medium text-navy mb-2">What you'll get:</h3>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Access to all API endpoints</span>
                </li>
                <li class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Unlimited API calls during beta</span>
                </li>
                <li class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Free during our beta period</span>
                </li>
            </ul>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="register-form" method="POST" action="{{ route('register.submit') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-gray-700 mb-1">Name</label>
                <input name="name" type="text" required class="form-input" value="{{ old('name') }}"/>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Email Address</label>
                <input name="email" type="email" required class="form-input" value="{{ old('email') }}"/>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Password</label>
                <input name="password" type="password" required class="form-input"/>
                <p class="text-gray-500 text-xs mt-1">Minimum 10 characters</p>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Confirm Password</label>
                <input name="password_confirmation" type="password" required class="form-input"/>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="terms" required class="form-checkbox"/>
                <span class="ml-2 text-sm text-gray-600">
                    I agree to the <a href="#" class="text-teal hover:text-teal-700">Terms of Service</a> and <a href="#" class="text-teal hover:text-teal-700">Privacy Policy</a>
                </span>
            </div>

            <button type="submit" class="btn-primary">
                Sign Up
            </button>

            <p class="mt-4 text-center text-gray-600 text-sm">
                Already have an account?
                <a href="{{ route('login') }}" class="text-teal hover:text-teal-700">Log In</a>
            </p>
        </form>
    </div>
@endsection
