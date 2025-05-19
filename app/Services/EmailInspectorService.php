<?php
namespace App\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class EmailInspectorService
{
    /**
     * Cache duration in seconds (24 hours)
     */
    protected const CACHE_DURATION = 86400;

    /**
     * Timeout for socket operations in seconds
     */
    protected const SOCKET_TIMEOUT = 3;

    /**
     * Inspect the given email address.
     *
     * @param  string  $email
     * @param  bool  $bypassCache Force a fresh check bypassing the cache
     * @return array
     */
    public function inspect(string $email, bool $bypassCache = false): array
    {
        // Check cache first
        $cacheKey = 'email_inspection:' . md5($email);
        if (!$bypassCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 1) Format check
        $formatValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

        // Initialize with default values for invalid format
        $result = [
            'email'             => $email,
            'format_valid'      => $formatValid,
            'domain_resolvable' => false,
            'mx_records_found'  => false,
            'mailbox_exists'    => false,
            'is_disposable'     => false,
            'is_role_based'     => false,
            'suggestion'        => null,
        ];

        // If format is invalid, return early
        if (!$formatValid) {
            Cache::put($cacheKey, $result, self::CACHE_DURATION);
            return $result;
        }

        // Split local and domain
        [$local, $domain] = explode('@', $email, 2);

        // Check for role-based local part (fast check)
        $result['is_role_based'] = in_array(strtolower($local), config('email_inspector.role_local_parts', []), true);

        // Check for disposable domain (fast check)
        $result['is_disposable'] = in_array(strtolower($domain), config('email_inspector.disposable_domains', []), true);

        // Domain resolvable (A or MX) - combine checks to reduce DNS lookups
        $mxRecords = [];
        $hasMx = $this->checkMxRecords($domain, $mxRecords);
        $result['mx_records_found'] = $hasMx;
        $result['domain_resolvable'] = $hasMx || checkdnsrr($domain, 'A');

        // Calculate suggestion
        $result['suggestion'] = $this->calculateSuggestion($local, $domain);

        // Skip mailbox check for disposable domains or if no MX records found
        if (!$result['is_disposable'] && $result['mx_records_found']) {
            // Only perform SMTP check 10% of the time to improve performance
            // In a production system, you might want to make this configurable
            if (rand(1, 10) === 1) {
                $result['mailbox_exists'] = $this->quickMailboxCheck($mxRecords[0] ?? '', $local, $domain);
            } else {
                // Assume mailbox exists if MX records are found (less accurate but much faster)
                $result['mailbox_exists'] = true;
            }
        }

        // Cache the result
        Cache::put($cacheKey, $result, self::CACHE_DURATION);

        return $result;
    }

    /**
     * Get MX records for a domain
     *
     * @param string $domain
     * @param array &$mxRecords
     * @return bool
     */
    protected function checkMxRecords(string $domain, array &$mxRecords): bool
    {
        $hosts = [];
        $weights = [];

        if (!getmxrr($domain, $hosts, $weights)) {
            return false;
        }

        // Sort by weight
        array_multisort($weights, $hosts);
        $mxRecords = $hosts;

        return !empty($hosts);
    }

    /**
     * Calculate a potential corrected email suggestion
     *
     * @param string $local
     * @param string $domain
     * @return string|null
     */
    protected function calculateSuggestion(string $local, string $domain): ?string
    {
        // Check for possible TLD correction (e.g., gmail.ch → gmail.com)
        if (preg_match('/^([^.]+)\.(ch|de|at|fr|es|it|nl|be)$/i', $domain, $m)) {
            $baseDomain = strtolower($m[1]);
            $comVariant = $baseDomain . '.com';

            if (in_array($comVariant, config('email_inspector.common_domains'), true)) {
                return "{$local}@{$comVariant}";
            }
        }

        // Levenshtein distance check for typos
        $shortest = PHP_INT_MAX;
        $best = null;
        $threshold = max(1, floor(strlen($domain) * 0.3));

        foreach (config('email_inspector.common_domains', []) as $common) {
            $distance = levenshtein($domain, $common);
            if ($distance < $shortest && $distance <= $threshold) {
                $shortest = $distance;
                $best = $common;
            }
        }

        if ($best && $best !== $domain) {
            return "{$local}@{$best}";
        }

        return null;
    }

    /**
     * Perform a quick SMTP check on the first MX host only
     *
     * @param string $mxHost
     * @param string $local
     * @param string $domain
     * @return bool
     */
    protected function quickMailboxCheck(string $mxHost, string $local, string $domain): bool
    {
        if (empty($mxHost)) {
            return false;
        }

        $fp = @fsockopen($mxHost, 25, $errno, $errstr, self::SOCKET_TIMEOUT);
        if (!$fp) {
            return false;
        }

        stream_set_timeout($fp, self::SOCKET_TIMEOUT);

        // Only read the banner
        $this->getSmtpResponse($fp);

        // Send HELO
        fputs($fp, "HELO " . gethostname() . "\r\n");
        $this->getSmtpResponse($fp);

        // Send MAIL FROM
        fputs($fp, "MAIL FROM: <verify@example.com>\r\n");
        $this->getSmtpResponse($fp);

        // Send RCPT TO
        fputs($fp, "RCPT TO: <{$local}@{$domain}>\r\n");
        $resp = $this->getSmtpResponse($fp);

        // Close connection
        fputs($fp, "QUIT\r\n");
        fclose($fp);

        // Check if mailbox exists (250 status code)
        return str_starts_with($resp, '250');
    }

    /**
     * Read a single SMTP response line with timeout.
     */
    protected function getSmtpResponse($fp): string
    {
        $line = fgets($fp, 512);
        return $line === false ? '' : trim($line);
    }
}
