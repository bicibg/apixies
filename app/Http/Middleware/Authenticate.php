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
        // Always redirect to login for web routes
        if ($request->is('api-keys*') || $request->is('*/api-keys*')) {
            return route('login');
        }

        if ($request->expectsJson()) {
            return null;
        }

        return route('login');
    }
}
