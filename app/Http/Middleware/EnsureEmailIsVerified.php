<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is not authenticated, proceed with the request
        if (!$request->user()) {
            return $next($request);
        }

        // If the user instance doesn't implement MustVerifyEmail, proceed
        if (!($request->user() instanceof MustVerifyEmail)) {
            return $next($request);
        }

        // If the user's email is already verified, proceed
        if ($request->user()->hasVerifiedEmail()) {
            return $next($request);
        }

        // Only redirect to verification notice if not accessing these routes
        if ($request->is('email/verify', 'email/verify/*', 'email/verification-notification', 'logout')) {
            return $next($request);
        }

        // Redirect to verification notice
        return Redirect::route('verification.notice');
    }
}
