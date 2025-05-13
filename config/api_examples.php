<?php
// config/api_examples.php

return [

    /*
    |--------------------------------------------------------------------------
    | Per‐URI Example Responses
    |--------------------------------------------------------------------------
    |
    | Map your API URI (relative to your domain, without leading slash) to
    | an array that will be shown as the "example_response".  When you add
    | a new feature, just drop it in here.
    |
    */

    'api/v1/inspect-email' => [
        "status"    => "success",
        "http_code" => 200,
        "code"      => "200",
        "message"   => "Email inspection successful",
        "data"      => [
            "email"             => "someone@example.com",
            "format_valid"      => true,
            "domain_resolvable" => false,
            "mx_records_found"  => false,
            "mailbox_exists"    => false,
            "is_disposable"     => false,
            "is_role_based"     => false,
            "suggestion"        => "someone@example.com"
        ],
    ],
    'api/v1/inspect-headers' => [
        "status"    => "success",
        "http_code" => 200,
        "code"      => "200",
        "message"   => "Security headers inspection successful",
        "data"      => [
            "url"         => "https://apixies.io",
            "status_code" => 200,
            "headers"     => [
                "strict-transport-security"     => "max-age=63072000; includeSubDomains; preload",
                "content-security-policy"       => "default-src 'self'; img-src 'self' https: data:; object-src 'none'; frame-ancestors 'none'",
                "referrer-policy"               => "strict-origin-when-cross-origin",
                "permissions-policy"            => "geolocation=(), microphone=(), camera=()",
                "x-frame-options"               => "DENY",
                "x-content-type-options"        => "nosniff",
                "x-xss-protection"              => "1; mode=block",
                "cross-origin-opener-policy"    => "same-origin",
                "cross-origin-embedder-policy"  => "require-corp",
                "cross-origin-resource-policy"  => "same-origin",
            ],
            "missing"    => [],
            "grade"      => "A+",
            "scanned_at" => "2025-05-12T12:34:56Z"
        ],
    ],


    'api/v1/inspect-user-agent' => [
        "status"    => "success",
        "http_code" => 200,
        "code"      => "200",
        "message"   => "User‑Agent inspection successful",
        "data"      => [
            "user_agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
            "is_bot"     => false,
            "device"     => ["family" => "Desktop", "model" => null, "brand" => null],
            "os"         => ["family" => "macOS", "major" => "13", "minor" => "4", "patch" => null],
            "browser"    => ["family" => "Chrome", "major" => "123", "minor" => "0", "patch" => "0"],
            "scanned_at" => "2025-05-12T12:34:56+00:00"
        ],
    ],

    'api/v1/inspect-ssl' => [
        "status"    => "success",
        "http_code" => 200,
        "code"      => "200",
        "message"   => "SSL inspection successful",
        "data"      => [
            "domain"      => "example.com",
            "port"        => 443,
            "valid"       => true,
            "issuer"      => "Let's Encrypt",
            "subject"     => "example.com",
            "subject_alt" => "DNS:example.com, DNS:www.example.com",
            "expires_at"  => "2026-03-12T04:13:00+00:00",
            "days_left"   => 305,
            "scanned_at"  => "2025-05-12T12:34:56+00:00"
        ],
    ],
    'api/v1/html-to-pdf' => [
        'headers' => [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="document.pdf"',
        ],
        'body'    => '<binary PDF stream>',
    ],


];
