<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SecurityHeadersInspectorService
{
    /**
     * Cache duration in seconds (30 minutes)
     * Short enough to be useful for debugging but long enough to prevent abuse
     */
    protected const CACHE_DURATION = 1800;

    /**
     * Security‑relevant headers we expect to see.
     * Array keys are lower‑case for case‑insensitive look‑ups.
     */
    private array $expected = [
        'content-security-policy',
        'strict-transport-security',
        'x-frame-options',
        'x-content-type-options',
        'referrer-policy',
        'permissions-policy',
        'cross-origin-embedder-policy',
        'cross-origin-opener-policy',
        'cross-origin-resource-policy',
    ];

    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout'         => 5,
            'allow_redirects' => true,
            'http_errors'     => false,
            'verify'          => config('inspector.verify_ssl', true),
            'headers'         => [
                'User-Agent' => 'ApixiesSecurityHeaders/1.0',
                'Accept'     => '*/*',
            ],
            'connect_timeout' => 3,
        ]);
    }

    /**
     * Fetch headers and compute a simple grade.
     *
     * @param string $url
     * @param bool $bypassCache Force a fresh check bypassing the cache
     * @return array
     */
    public function inspect(string $url, bool $bypassCache = false): array
    {
        // Prepend scheme if absent.
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        // Check cache first unless bypass is requested
        $cacheKey = 'headers_inspection:' . md5($url);

        if (!$bypassCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $resp = $this->http->request('HEAD', $url);
        } catch (\Throwable $e) {
            $errorResult = [
                'error'     => true,
                'message'   => 'Unable to fetch headers: ' . $e->getMessage(),
                'exception' => $e->getMessage(),
            ];

            // Cache error results for a shorter period (5 minutes)
            Cache::put($cacheKey, $errorResult, 300);

            return $errorResult;
        }

        // Check if response is successful (200-299 status code)
        $statusCode = $resp->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $statusResult = [
                'error'     => true,
                'message'   => 'Server responded with status code ' . $statusCode,
                'status_code' => $statusCode,
            ];

            // Cache non-success status codes for a shorter period
            Cache::put($cacheKey, $statusResult, 300);

            return $statusResult;
        }

        $headers = collect($resp->getHeaders())
            ->mapWithKeys(fn ($v, $k) => [strtolower($k) => implode('; ', $v)])
            ->all();

        $missing = array_values(array_diff($this->expected, array_keys($headers)));
        $grade   = $this->grade(count($missing));

        $result = [
            'url'          => $url,
            'status_code'  => $resp->getStatusCode(),
            'headers'      => $headers,
            'missing'      => $missing,
            'grade'        => $grade,
            'scanned_at'   => Carbon::now()->toIso8601String(),
        ];

        // Cache successful results
        Cache::put($cacheKey, $result, self::CACHE_DURATION);

        return $result;
    }

    private function grade(int $missing): string
    {
        return match (true) {
            $missing === 0       => 'A+',
            $missing <= 2        => 'A',
            $missing <= 4        => 'B',
            $missing <= 6        => 'C',
            default              => 'D',
        };
    }
}
