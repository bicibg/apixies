@extends('layouts.app')

@section('title', 'Account Deactivated')

@section('content')
    <div class="container max-w-lg mx-auto px-4 py-12">
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-red-600 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-navy mb-4">Account Deactivated</h1>

            <p class="text-gray-600 mb-6">
                Your account has been deactivated as requested. You can restore your account within 30 days by clicking on the restoration link that was sent to your email address.
            </p>

            <p class="text-gray-600 mb-8">
                If you don't see the email in your inbox, please check your spam folder. The restoration link will expire after 30 days and your account will be permanently deleted.
            </p>

            <div class="border-t border-gray-200 pt-6">
                <p class="text-sm text-gray-500">
                    Need help? <a href="mailto:support@apixies.io" class="text-teal hover:text-teal-700">Contact our support team</a>
                </p>
            </div>
        </div>
    </div>
@endsection
