<?php

return [
    // TEMPLATE FOR NEW ENDPOINTS
    // Copy this template and customize it for your new endpoint
    // 'my-new-endpoint' => [
    //     'title' => 'My New Endpoint', // Human-readable title
    //     'description' => 'Description of what it does', // Short description
    //     'uri' => 'api/v1/my-new-endpoint', // URL path (without leading slash)
    //     'method' => 'GET', // HTTP method (GET, POST, PUT, DELETE)
    //     'category' => 'inspector', // Category for grouping (system, inspector, converter, etc.)
    //     'route_params' => [], // Route parameters (e.g. ['id', 'slug'])
    //     'query_params' => ['param1', 'param2', 'param3'], // Query parameters
    //     'demo' => true, // Whether to show the "Try" button
    //     'response_example' => [
    //         'status' => 'success',
    //         'http_code' => 200,
    //         'code' => 'SUCCESS',
    //         'message' => 'Operation completed successfully',
    //         'data' => [
    //             // Example response data structure
    //             'param1' => 'value1',
    //             'timestamp' => '2025-05-19T12:00:00Z',
    //         ],
    //     ],
    // ],

    'health' => [
        'title' => 'Health Check',
        'description' => 'Check the health status of the API',
        'uri' => 'api/v1/health',
        'method' => 'GET',
        'category' => 'system',
        'route_params' => [],
        'query_params' => [],
        'demo' => true,
        'response_example' => [
            'status' => 'success',
            'http_code' => 200,
            'code' => 'SUCCESS',
            'message' => 'API is healthy',
            'data' => [
                'version' => '1.0.0',
                'environment' => 'production',
                'timestamp' => '2025-05-15T12:34:56Z',
            ],
        ],
    ],
    'ready' => [
        'title' => 'Readiness Check',
        'description' => 'Check if the API is ready to accept requests by verifying database and cache connections',
        'uri' => 'api/v1/ready',
        'method' => 'GET',
        'category' => 'system',
        'route_params' => [],
        'query_params' => [],
        'demo' => true,
        'response_example' => [
            'status' => 'success',
            'http_code' => 200,
            'code' => 'SUCCESS',
            'message' => 'API is ready',
            'data' => [
                'version' => '1.0.0',
                'database' => 'connected',
                'cache' => 'connected',
                'timestamp' => '2025-05-15T12:34:56Z',
            ],
        ],
    ],
    'ssl' => [
        'title' => 'SSL Health Inspector',
        'description' => 'Inspect SSL certificate details for a domain, including validity, expiry and chain health',
        'uri' => 'api/v1/inspect-ssl',
        'method' => 'GET',
        'category' => 'inspector',
        'route_params' => [],
        'query_params' => ['domain'],
        'demo' => true,
        'response_example' => [
            'status' => 'success',
            'http_code' => 200,
            'code' => 'SUCCESS',
            'message' => 'SSL certificate inspected successfully',
            'data' => [
                'url' => 'https://example.com',
                'status_code' => 200,
                'certificate' => [
                    'issuer' => 'Let\'s Encrypt Authority X3',
                    'subject' => 'example.com',
                    'valid_from' => '2023-01-01T00:00:00Z',
                    'valid_to' => '2023-04-01T00:00:00Z',
                    'valid' => true,
                    'days_until_expiry' => 90,
                ],
            ],
        ],
    ],
    'headers' => [
        'title' => 'Security Headers Inspector',
        'description' => 'Inspect security headers for a URL to check for adherence to current best practices',
        'uri' => 'api/v1/inspect-headers',
        'method' => 'GET',
        'category' => 'inspector',
        'route_params' => [],
        'query_params' => ['url'],
        'demo' => true,
        'response_example' => [
            'status' => 'success',
            'http_code' => 200,
            'code' => 'SUCCESS',
            'message' => 'Security headers inspected successfully',
            'data' => [
                'url' => 'https://example.com',
                'status_code' => 200,
                'headers' => [
                    'Content-Security-Policy' => 'default-src \'self\'',
                    'X-Content-Type-Options' => 'nosniff',
                    'X-Frame-Options' => 'DENY',
                    'X-XSS-Protection' => '1; mode=block',
                ],
                'missing' => [
                    'Strict-Transport-Security'
                ],
                'grade' => 'B',
            ],
        ],
    ],
    'email' => [
        'title' => 'Email Inspector',
        'description' => 'Inspect email address details including format validation, MX record checks, and disposable email detection',
        'uri' => 'api/v1/inspect-email',
        'method' => 'GET',
        'category' => 'inspector',
        'route_params' => [],
        'query_params' => ['email'],
        'demo' => true,
        'response_example' => [
            'status' => 'success',
            'http_code' => 200,
            'code' => 'SUCCESS',
            'message' => 'Email address inspected successfully',
            'data' => [
                'email' => 'user@example.com',
                'valid' => true,
                'domain' => 'example.com',
                'mx_records' => [
                    'mx1.example.com',
                    'mx2.example.com',
                ],
                'disposable' => false,
                'free_provider' => false,
            ],
        ],
    ],
    'user-agent' => [
        'title' => 'User Agent Inspector',
        'description' => 'Parse a User-Agent string to detect browser, operating system, device type and bot status',
        'uri' => 'api/v1/inspect-user-agent',
        'method' => 'GET',
        'category' => 'inspector',
        'route_params' => [],
        'query_params' => ['user_agent'],
        'demo' => true,
        'response_example' => [
            'status' => 'success',
            'http_code' => 200,
            'code' => 'SUCCESS',
            'message' => 'User agent inspected successfully',
            'data' => [
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36',
                'browser' => 'Chrome',
                'browser_version' => '90.0.4430.212',
                'os' => 'Windows',
                'os_version' => '10',
                'device' => 'Desktop',
                'mobile' => false,
                'bot' => false,
            ],
        ],
    ],
    'ip-geolocation' => [
        'title' => 'IP Geolocation',
        'description' => 'Convert IP addresses to location data including country, city, coordinates, timezone, and ISP information',
        'uri' => 'api/v1/ip-geolocation',
        'method' => 'GET',
        'category' => 'inspector',
        'route_params' => [],
        'query_params' => ['ip'],
        'demo' => true,
        'response_example' => [
            'status' => 'success',
            'http_code' => 200,
            'code' => 'SUCCESS',
            'message' => 'IP geolocation retrieved successfully',
            'data' => [
                'ip' => '8.8.8.8',
                'country' => 'United States',
                'country_code' => 'US',
                'region' => 'CA',
                'region_name' => 'California',
                'city' => 'Mountain View',
                'zip' => '94043',
                'latitude' => 37.422,
                'longitude' => -122.084,
                'timezone' => 'America/Los_Angeles',
                'isp' => 'Google LLC',
                'org' => 'Google LLC',
                'as' => 'AS15169 Google LLC',
            ],
        ],
    ],
    'html-to-pdf' => [
        'title' => 'HTML to PDF Converter',
        'description' => 'Convert HTML content to a PDF document with proper formatting and styling',
        'uri' => 'api/v1/html-to-pdf',
        'method' => 'POST',
        'category' => 'converter',
        'route_params' => [],
        'query_params' => ['html'],
        'demo' => true,
        'response_example' => [
            'Binary PDF content with Content-Type: application/pdf',
        ],
    ],
    'test' => [
        'title' => 'Test Endpoint',
        'description' => 'Simple endpoint for testing API connectivity and authentication',
        'uri' => 'api/v1/test',
        'method' => 'GET',
        'category' => 'system',
        'route_params' => [],
        'query_params' => [],
        'demo' => true,
        'response_example' => [
            'status' => 'success',
            'http_code' => 200,
            'code' => 'SUCCESS',
            'message' => 'API test successful',
            'data' => [
                'message' => 'API connection successful',
                'authenticated' => true,
                'timestamp' => '2025-05-15T12:34:56Z',
            ],
        ],
    ],
];
