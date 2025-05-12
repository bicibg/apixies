<?php
namespace App\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;

class UserAgentInspectorService
{
    /**
     * Inspect the given email address.
     *
     * @param  string  $email
     * @return array
     */
    public function inspect(string $email): array
    {
        // 1) Format check
        $formatValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

        // Split local and domain
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        // 2) Domain resolvable (A or MX)
        $domainResolvable = $formatValid && (
                checkdnsrr($domain, 'A') ||
                checkdnsrr($domain, 'MX')
            );

        // 3) MX records
        $mxRecordsFound = $formatValid && (checkdnsrr($domain, 'MX'));

        // 4) Disposable domain?
        $disposable = in_array(strtolower($domain), config('email_inspector.disposable_domains', []), true);

        // 5) Role-based local-part?
        $roleBased = in_array(strtolower($local), config('email_inspector.role_local_parts', []), true);

        // In EmailInspectorService@inspect, just before your typo‐suggestion block:

// 6a) If the domain looks like a global provider but with a local TLD, swap to .com
        $suggestion = null;
        if ($formatValid) {
            // split off the TLD
            if (preg_match('/^([^.]+)\.(ch|de|at|fr|es|it|nl|be)$/i', $domain, $m)) {
                $baseDomain = strtolower($m[1]);   // e.g. "gmail"
                $comVariant = $baseDomain . '.com';

                // only if we actually know that .com variant is in our common list
                if (in_array($comVariant, config('email_inspector.common_domains'), true)) {
                    $suggestion = "{$local}@{$comVariant}";
                }
            }
        }

// If no swap suggestion, fall back to your Levenshtein logic
        if (! $suggestion) {
            $shortest  = PHP_INT_MAX;
            $best      = null;
            $threshold = max(1, floor(strlen($domain) * 0.3));  // e.g. 30% of length

            foreach (config('email_inspector.common_domains', []) as $common) {
                $distance = levenshtein($domain, $common);
                if ($distance < $shortest && $distance <= $threshold) {
                    $shortest = $distance;
                    $best     = $common;
                }
            }

            if ($best && $best !== $domain) {
                $suggestion = "{$local}@{$best}";
            }
        }

// return suggestion (may still be null if nothing matched)

        // 7) Mailbox existence check via SMTP
        $mailboxExists = false;
        if ($formatValid && $mxRecordsFound) {
            $hosts = [];
            getmxrr($domain, $hosts, $weights);
            // sort by weight
            array_multisort($weights, $hosts);

            foreach ($hosts as $host) {
                if ($this->smtpCheck($host, $local, $domain)) {
                    $mailboxExists = true;
                    break;
                }
            }
        }

        return [
            'email'             => $email,
            'format_valid'      => $formatValid,
            'domain_resolvable' => $domainResolvable,
            'mx_records_found'  => $mxRecordsFound,
            'mailbox_exists'    => $mailboxExists,
            'is_disposable'     => $disposable,
            'is_role_based'     => $roleBased,
            'suggestion'        => $suggestion,
        ];
    }

    /**
     * Attempt a simple SMTP "RCPT TO" check.
     *
     * @param  string  $mxHost
     * @param  string  $local
     * @param  string  $domain
     * @return bool
     */
    protected function smtpCheck(string $mxHost, string $local, string $domain): bool
    {
        $fp = @fsockopen($mxHost, 25, $errno, $errstr, 5);
        if (! $fp) {
            return false;
        }

        stream_set_timeout($fp, 5);
        $this->getSmtpResponse($fp);                                  // Banner
        fputs($fp, "HELO " . gethostname() . "\r\n");
        $this->getSmtpResponse($fp);

        fputs($fp, "MAIL FROM: <verify@{$domain}>\r\n");
        $this->getSmtpResponse($fp);

        fputs($fp, "RCPT TO: <{$local}@{$domain}>\r\n");
        $resp = $this->getSmtpResponse($fp);

        fputs($fp, "QUIT\r\n");
        fclose($fp);

        // 250 = OK, 550 = no such user, 450/451/452 = mailbox unavailable
        return str_starts_with($resp, '250');
    }

    /**
     * Read a single SMTP response line.
     */
    protected function getSmtpResponse($fp): string
    {
        $line = fgets($fp, 512);
        return $line === false ? '' : trim($line);
    }
}
