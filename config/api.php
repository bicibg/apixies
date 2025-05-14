<?php
return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration specific to the API system.
    |
    */

    // Whether to enable sandbox tokens
    'enable_sandbox_tokens' => env('API_ENABLE_SANDBOX_TOKENS', false),

    // Default sandbox token quota
    'sandbox_token_quota' => env('API_SANDBOX_TOKEN_QUOTA', 50),

    // Default sandbox token expiration in seconds (24 hours)
    'sandbox_token_expiration' => env('API_SANDBOX_TOKEN_EXPIRATION', 86400),

    'rate_limit' => [
        'requests' => 60,
        'minutes' => 1,
    ],
];
