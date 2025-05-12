<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

class SecurityHeadersInspectorService
{
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
            'timeout'         => 10,
            'allow_redirects' => true,
            'http_errors'     => false,
            'verify'          => config('inspector.verify_ssl', true),
            'headers'         => [
                'User-Agent' => 'ApixiesSecurityHeaders/1.0',
                'Accept'     => '*/*',
            ],
        ]);
    }

    /**
     * Fetch headers and compute a simple grade.
     */
    public function inspect(string $url): array
    {
        // Prepend scheme if absent.
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        try {
            $resp = $this->http->request('HEAD', $url);
        } catch (\Throwable $e) {
            return [
                'error'     => true,
                'exception' => $e->getMessage(),
            ];
        }

        $headers = collect($resp->getHeaders())
            ->mapWithKeys(fn ($v, $k) => [strtolower($k) => implode('; ', $v)])
            ->all();

        $missing = array_values(array_diff($this->expected, array_keys($headers)));
        $grade   = $this->grade(count($missing));

        return [
            'url'          => $url,
            'status_code'  => $resp->getStatusCode(),
            'headers'      => $headers,
            'missing'      => $missing,
            'grade'        => $grade,
            'scanned_at'   => Carbon::now()->toIso8601String(),
        ];
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
