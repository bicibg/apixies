@extends('layouts.app')

@section('title', 'Verify Your Email Address')

@section('content')
    <div class="max-w-lg mx-auto mt-12 p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-semibold mb-4">Please Verify Your Email</h1>

        @if (session('status') === 'verification-link-sent')
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-800 rounded">
                A new verification link has been sent to your email address.
            </div>
        @elseif (session('status'))
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-800 rounded">
                {{ session('status') }}
            </div>
        @endif

        <p class="mb-4">
            Before proceeding, please check your email for a verification link.
            If you did not receive the email, click the button below to request another.
        </p>

        <form method="POST" action="{{ route('verification.send') }}" class="inline">
            @csrf
            <button
                type="submit"
                class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 transition text-white"
            >
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="inline ml-4">
            @csrf
            <button
                type="submit"
                class="px-4 py-2 bg-gray-600 rounded hover:bg-gray-700 transition border text-white"
            >
                Logout
            </button>
        </form>
    </div>
@endsection
