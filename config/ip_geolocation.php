<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IP Geolocation Service Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the IP Geolocation service.
    |
    */

    // Cache duration in hours
    'cache_duration' => env('IP_GEOLOCATION_CACHE_DURATION', 24),

    // Rate limiting for IP Geolocation API calls (per minute)
    'rate_limit' => env('IP_GEOLOCATION_RATE_LIMIT', 45),

    // IP API service URL - we use the free service by default
    'api_url' => env('IP_GEOLOCATION_API_URL', 'http://ip-api.com/json/'),

    // Additional fields to include in the response
    'fields' => [
        'country',
        'countryCode',
        'region',
        'regionName',
        'city',
        'zip',
        'lat',
        'lon',
        'timezone',
        'isp',
        'org',
        'as',
    ],
];
