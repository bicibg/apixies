<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SslHealthInspectorService
{
    /**
     * Cache duration in seconds (30 minutes)
     * Short enough to be useful for debugging but long enough to prevent abuse
     */
    protected const CACHE_DURATION = 1800;

    /**
     * Timeout for socket operations in seconds (very aggressive timeout)
     */
    protected const SOCKET_TIMEOUT = 2;

    /**
     * Connection timeout in seconds
     */
    protected const CONNECT_TIMEOUT = 1.5;

    /**
     * Check the SSL configuration for a domain
     *
     * @param string $domain
     * @param int $port
     * @param bool $bypassCache Force a fresh check bypassing the cache
     * @return array
     */
    public function inspect(string $domain, int $port = 443, bool $bypassCache = false): array
    {
        // Validate domain
        $domain = $this->sanitizeDomain($domain);

        if (empty($domain)) {
            return [
                'status' => 'error',
                'message' => 'Invalid domain provided',
            ];
        }

        // Check cache first unless bypass is requested
        $cacheKey = "ssl_inspection:{$domain}:{$port}";

        if (!$bypassCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Start processing timer
        $startTime = microtime(true);

        try {
            // Use a much more aggressive approach with a non-blocking socket connection
            // Create socket context with very short timeouts
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
                'socket' => [
                    'tcp_nodelay' => true,
                    'bindto' => '0:0',
                ]
            ]);

            // Use a non-blocking connection to improve speed
            $socket = @stream_socket_client(
                "tcp://{$domain}:{$port}",
                $errno,
                $errstr,
                self::CONNECT_TIMEOUT,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$socket) {
                $errorResult = [
                    'status' => 'error',
                    'message' => "Cannot connect to {$domain}:{$port}: {$errstr}",
                    'error_code' => $errno,
                    'domain' => $domain,
                ];
                // Cache errors for a shorter time (5 minutes)
                Cache::put($cacheKey, $errorResult, 300);
                return $errorResult;
            }

            stream_set_timeout($socket, self::SOCKET_TIMEOUT);

            // Enable crypto with short timeout
            $cryptoResult = @stream_socket_enable_crypto(
                $socket,
                true,
                STREAM_CRYPTO_METHOD_ANY_CLIENT
            );

            if (!$cryptoResult) {
                fclose($socket);
                $cryptoError = [
                    'status' => 'error',
                    'message' => "SSL/TLS handshake failed with {$domain}:{$port}",
                    'domain' => $domain,
                ];
                Cache::put($cacheKey, $cryptoError, 300);
                return $cryptoError;
            }

            // Get certificate
            $certData = stream_context_get_options($context);
            if (empty($certData['ssl']['peer_certificate'])) {
                fclose($socket);
                $noCertResult = [
                    'status' => 'error',
                    'message' => 'Could not retrieve SSL certificate',
                    'domain' => $domain,
                ];
                Cache::put($cacheKey, $noCertResult, 300);
                return $noCertResult;
            }

            // Parse the certificate
            $cert = openssl_x509_parse($certData['ssl']['peer_certificate']);
            fclose($socket);

            if (empty($cert)) {
                $parseError = [
                    'status' => 'error',
                    'message' => 'Could not parse SSL certificate',
                    'domain' => $domain,
                ];
                Cache::put($cacheKey, $parseError, 300);
                return $parseError;
            }

            // Extract basic certificate data
            $validFrom = $cert['validFrom_time_t'] ?? 0;
            $validTo = $cert['validTo_time_t'] ?? 0;
            $currentTime = time();

            $isValid = true;
            $validationIssues = [];

            // Check validity period
            if ($validTo < $currentTime) {
                $isValid = false;
                $validationIssues[] = 'Certificate is expired';
            }

            if ($validFrom > $currentTime) {
                $isValid = false;
                $validationIssues[] = 'Certificate is not yet valid';
            }

            // Get certificate subject
            $subject = isset($cert['subject']) ? $this->formatDN($cert['subject']) : '';
            $issuer = isset($cert['issuer']) ? $this->formatDN($cert['issuer']) : '';

            // Extract common name
            $commonName = $cert['subject']['CN'] ?? '';

            // Get subject alternative names
            $sanNames = [];
            if (!empty($cert['extensions']['subjectAltName'])) {
                $sans = explode(', ', $cert['extensions']['subjectAltName']);
                foreach ($sans as $san) {
                    if (strpos($san, 'DNS:') === 0) {
                        $sanNames[] = substr($san, 4);
                    }
                }
            }

            // Check domain match
            $domainMatches = false;
            if (strtolower($commonName) === strtolower($domain)) {
                $domainMatches = true;
            } else {
                foreach ($sanNames as $altName) {
                    // Support wildcard certificates
                    $pattern = '/^' . str_replace('\*', '.*', preg_quote($altName, '/')) . '$/i';
                    if (preg_match($pattern, $domain) || strtolower($altName) === strtolower($domain)) {
                        $domainMatches = true;
                        break;
                    }
                }
            }

            if (!$domainMatches) {
                $isValid = false;
                $validationIssues[] = 'Domain does not match certificate';
            }

            // Calculate days until expiry
            $daysUntilExpiry = floor(($validTo - $currentTime) / 86400);

            // Format the result
            $result = [
                'status' => 'success',
                'message' => 'SSL inspection successful',
                'data' => [
                    'domain' => $domain,
                    'port' => $port,
                    'valid' => $isValid,
                    'issuer' => $issuer,
                    'subject' => $subject,
                    'common_name' => $commonName,
                    'alt_names' => $sanNames,
                    'valid_from' => date('Y-m-d H:i:s', $validFrom),
                    'valid_to' => date('Y-m-d H:i:s', $validTo),
                    'days_until_expiry' => $daysUntilExpiry,
                    'signature_algorithm' => $cert['signatureTypeSN'] ?? '',
                    'validation_issues' => $validationIssues,
                ]
            ];

            // Cache successful results
            Cache::put($cacheKey, $result, self::CACHE_DURATION);

            return $result;

        } catch (\Exception $e) {
            Log::error('Exception during SSL inspection: ' . $e->getMessage(), [
                'domain' => $domain,
                'exception' => $e
            ]);

            $exceptionResult = [
                'status' => 'error',
                'message' => 'SSL check failed: ' . $e->getMessage(),
                'domain' => $domain,
            ];

            // Cache error results for a shorter time (5 minutes)
            Cache::put($cacheKey, $exceptionResult, 300);

            return $exceptionResult;
        }
    }

    /**
     * Format Distinguished Name from array to string
     *
     * @param array $dn
     * @return string
     */
    private function formatDN(array $dn): string
    {
        $parts = [];
        foreach ($dn as $key => $value) {
            $parts[] = "$key=$value";
        }
        return implode(', ', $parts);
    }

    /**
     * Sanitize domain to prevent command injection and invalid requests
     *
     * @param string $domain
     * @return string
     */
    private function sanitizeDomain(string $domain): string
    {
        // Remove protocol if present
        $domain = preg_replace('#^https?://#i', '', $domain);

        // Remove path, query string, etc.
        $domain = preg_replace('#/.*$#', '', $domain);

        // Allow only valid domain characters
        $domain = preg_replace('/[^a-zA-Z0-9.\-]/', '', $domain);

        // Validate domain structure
        if (!preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $domain)) {
            return '';
        }

        return $domain;
    }
}
