<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int,string>
     */
    protected $except = [
        // Only exclude API routes specifically
        'api/*',
        // Add specific webhooks if needed
        'webhooks/*',
        'sandbox/token',
    ];
}
