<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class IpGeolocationService
{
    /**
     * Check if IP address is valid.
     *
     * @param string $ip
     * @return bool
     */
    public function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Get geolocation information for an IP address.
     *
     * @param string $ip
     * @return array
     */
    public function getGeolocation(string $ip): array
    {
        // Check for cache first
        $cacheDuration = Config::get('ip_geolocation.cache_duration', 24);
        $cacheKey = 'ip_geolocation_' . md5($ip);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $apiUrl = Config::get('ip_geolocation.api_url', 'http://ip-api.com/json/');
            $response = Http::get("{$apiUrl}{$ip}");

            if ($response->successful()) {
                $data = $response->json();

                // Format the response
                $result = [
                    'ip' => $ip,
                    'country' => $data['country'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'region' => $data['region'] ?? null,
                    'region_name' => $data['regionName'] ?? null,
                    'city' => $data['city'] ?? null,
                    'zip' => $data['zip'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'isp' => $data['isp'] ?? null,
                    'org' => $data['org'] ?? null,
                    'as' => $data['as'] ?? null,
                ];

                // Cache the result
                Cache::put($cacheKey, $result, now()->addHours($cacheDuration));

                return $result;
            }

            // If the API request failed
            Log::error('IP Geolocation API request failed', [
                'ip' => $ip,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'ip' => $ip,
                'error' => 'Failed to retrieve geolocation data',
            ];
        } catch (\Exception $e) {
            Log::error('IP Geolocation service error', [
                'ip' => $ip,
                'exception' => $e->getMessage(),
            ]);

            return [
                'ip' => $ip,
                'error' => 'An error occurred while processing the request',
            ];
        }
    }
}
