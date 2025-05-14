<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // API routes should never redirect - return null to throw an exception
        if ($request->is('api/*')) {
            return null;
        }

        // If client expects JSON, don't redirect
        if ($request->expectsJson()) {
            return null;
        }

        // Web routes should redirect to login
        return route('login');
    }
}
