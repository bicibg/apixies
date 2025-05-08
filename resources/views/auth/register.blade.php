@extends('layouts.app')

@section('title','Sign Up – Apixies')

@section('content')
    <div class="auth-card">
        <h2 class="text-3xl font-bold text-center text-[#0A2240] mb-2">
            Create an Account
        </h2>
        <p class="text-center text-gray-600 mb-6">
            Get your free API key and start building.
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

        <div id="signup-error" class="text-red-600 text-sm mb-4"></div>

        <form id="signup-form" method="POST" action="{{ route('register.submit') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-gray-700 mb-1">Name</label>
                <input name="name" required class="form-input" value="{{ old('name') }}"/>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Email Address</label>
                <input name="email" type="email" required class="form-input" value="{{ old('email') }}"/>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Password</label>
                <input name="password" type="password" required class="form-input"/>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Confirm Password</label>
                <input name="password_confirmation" type="password" required class="form-input"/>
            </div>

            <button type="submit" class="btn-primary">
                Sign Up
            </button>

            <p class="mt-4 text-center text-gray-600 text-sm">
                Already have an account?
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Log In</a>
            </p>
        </form>
    </div>
@endsection
