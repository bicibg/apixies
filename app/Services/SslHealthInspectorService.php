<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslHealthInspectorService
{
    /**
     * Check the SSL configuration for a domain
     *
     * @param string $domain
     * @return array
     */
    public function inspect(string $domain): array
    {
        // Validate domain
        $domain = $this->sanitizeDomain($domain);

        if (empty($domain)) {
            return [
                'status' => 'error',
                'message' => 'Invalid domain provided',
            ];
        }

        try {
            // Log the request
            Log::info('Starting SSL inspection for domain: ' . $domain);

            // Initialize cURL to check SSL certificate
            $ch = curl_init("https://{$domain}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CERTINFO, true);

            // Execute request
            $response = curl_exec($ch);
            $certInfo = curl_getinfo($ch, CURLINFO_CERTINFO);
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            $errno = curl_errno($ch);

            curl_close($ch);

            // If there was an error, handle it
            if ($errno !== 0) {
                Log::warning('SSL inspection failed for domain: ' . $domain . ' with error: ' . $error);

                return [
                    'status' => 'error',
                    'message' => 'SSL check failed: ' . $error,
                    'domain' => $domain,
                    'error_code' => $errno,
                ];
            }

            // Process certificate info
            $cert = $certInfo[0] ?? [];

            // Check if certificate information was obtained
            if (empty($cert)) {
                return [
                    'status' => 'error',
                    'message' => 'Could not retrieve SSL certificate information',
                    'domain' => $domain,
                ];
            }

            // Validate dates
            $validFrom = strtotime($cert['Start date'] ?? '');
            $validTo = strtotime($cert['Expire date'] ?? '');
            $currentTime = time();

            $isValid = true;
            $validationIssues = [];

            // Check if certificate is expired or not yet valid
            if ($validTo < $currentTime) {
                $isValid = false;
                $validationIssues[] = 'Certificate is expired';
            }

            if ($validFrom > $currentTime) {
                $isValid = false;
                $validationIssues[] = 'Certificate is not yet valid';
            }

            // Get certificate details
            $issuer = $cert['Issuer'] ?? '';
            $subject = $cert['Subject'] ?? '';

            // Extract common name and alternative names
            $commonName = '';
            if (preg_match('/CN=([^,]+)/', $subject, $matches)) {
                $commonName = $matches[1];
            }

            $altNames = [];
            if (isset($cert['Subject Alternative Name'])) {
                $altNamesStr = $cert['Subject Alternative Name'];
                preg_match_all('/DNS:([^,]+)/', $altNamesStr, $matches);
                $altNames = $matches[1] ?? [];
            }

            // Check domain match
            $domainMatches = false;
            if (strtolower($commonName) === strtolower($domain)) {
                $domainMatches = true;
            } else {
                foreach ($altNames as $altName) {
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

            // Enhanced metrics about the SSL certificate
            $daysUntilExpiry = floor(($validTo - $currentTime) / 86400);

            $result = [
                'status' => 'success',
                'message' => 'SSL inspection successful',
                'data' => [
                    'domain' => $domain,
                    'port' => $info['primary_port'] ?? 443,
                    'valid' => $isValid,
                    'issuer' => $issuer,
                    'subject' => $subject,
                    'subject_alt' => $cert['Subject Alternative Name'] ?? '',
                    'valid_from' => date('Y-m-d H:i:s', $validFrom),
                    'valid_to' => date('Y-m-d H:i:s', $validTo),
                    'days_until_expiry' => $daysUntilExpiry,
                    'cipher' => $info['ssl_cipher'] ?? '',
                    'version' => $cert['Version'] ?? '',
                    'signature_type' => $cert['Signature Algorithm'] ?? '',
                    'validation_issues' => $validationIssues,
                ]
            ];

            return $result;

        } catch (\Exception $e) {
            Log::error('Exception during SSL inspection: ' . $e->getMessage(), [
                'domain' => $domain,
                'exception' => $e
            ]);

            return [
                'status' => 'error',
                'message' => 'SSL check failed: ' . $e->getMessage(),
                'domain' => $domain,
            ];
        }
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
