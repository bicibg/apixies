<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class SslHealthInspectorService
{
    /**
     * Inspect a domain’s SSL certificate.
     */
    public function inspect(string $domain, int $port = 443): array
    {
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
            ],
        ]);

        $client  = @stream_socket_client(
            "ssl://{$domain}:{$port}",
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$client) {
            return [
                'error'     => true,
                'message'   => $errstr ?: 'Unable to connect',
            ];
        }

        $params = stream_context_get_params($client);
        $cert   = openssl_x509_parse($params['options']['ssl']['peer_certificate'] ?? '');

        $valid = $cert !== false;

        $expiresAt = $valid ? Carbon::createFromTimestampUTC($cert['validTo_time_t']) : null;
        $daysLeft  = $valid ? Carbon::now('UTC')->diffInDays($expiresAt, false) : null;

        return [
            'domain'          => $domain,
            'port'            => $port,
            'valid'           => $valid,
            'issuer'          => $cert['issuer']['O']    ?? null,
            'subject'         => $cert['subject']['CN']  ?? null,
            'subject_alt'     => $cert['extensions']['subjectAltName'] ?? null,
            'expires_at'      => $expiresAt?->toIso8601String(),
            'days_left'       => $daysLeft,
            'scanned_at'      => Carbon::now()->toIso8601String(),
        ];
    }
}
