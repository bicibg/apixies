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

                // Get languages for the country if the feature is enabled
                $languages = [];
                if (Config::get('ip_geolocation.include_languages', true)) {
                    $languages = $this->getLanguagesForCountry($data['countryCode'] ?? null);
                }

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

                // Add languages if available
                if (!empty($languages)) {
                    $result['languages'] = $languages;
                }

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

    /**
     * Get languages for a country based on country code.
     *
     * @param string|null $countryCode
     * @return array
     */
    protected function getLanguagesForCountry(?string $countryCode): array
    {
        if (!$countryCode) {
            return [];
        }

        // Get the language data source from config
        $dataSource = Config::get('ip_geolocation.language_data_source', 'rest_countries');

        if ($dataSource === 'internal') {
            // Get language map from config
            $languageMap = Config::get('ip_geolocation.language_map', []);

            // Check if we have language data for this country
            if (isset($languageMap[$countryCode])) {
                return $languageMap[$countryCode];
            }
        } elseif ($dataSource === 'rest_countries') {
            return $this->getLanguagesFromRestCountries($countryCode);
        } elseif ($dataSource === 'database') {
            return $this->getLanguagesFromDatabase($countryCode);
        }

        // Return an empty array if no language data is available
        return [];
    }

    /**
     * Get languages from REST Countries API
     *
     * @param string $countryCode
     * @return array
     */
    protected function getLanguagesFromRestCountries(string $countryCode): array
    {
        $cacheKey = 'languages_rest_countries_' . $countryCode;

        // Try to get from cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $apiUrl = Config::get('ip_geolocation.rest_countries_url', 'https://restcountries.com/v3.1/all');
            $response = Http::get("{$apiUrl}{$countryCode}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data[0]['languages'])) {
                    $languages = [];
                    $languageData = $data[0]['languages'];

                    foreach ($languageData as $code => $name) {
                        $languages[] = [
                            'code' => $code,
                            'name' => $name,
                            'native_name' => $name, // REST Countries doesn't provide native names
                            'official' => true // Can't determine this from REST Countries
                        ];
                    }

                    // Cache the result for 30 days (language data rarely changes)
                    Cache::put($cacheKey, $languages, now()->addDays(30));

                    return $languages;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching language data from REST Countries', [
                'country_code' => $countryCode,
                'exception' => $e->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Get languages from database
     * This is a placeholder for a potential database implementation
     *
     * @param string $countryCode
     * @return array
     */
    protected function getLanguagesFromDatabase(string $countryCode): array
    {
        // This would be implemented if you create a countries_languages table
        // For now, it returns an empty array
        return [];
    }
}
