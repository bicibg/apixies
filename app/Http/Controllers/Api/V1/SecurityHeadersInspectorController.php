<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SecurityHeadersInspectorService;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class SecurityHeadersInspectorController extends Controller
{
    /**
     * Handle GET /inspect‑headers
     */
    public function __invoke(Request $request, SecurityHeadersInspectorService $inspector)
    {
        // Step 1: basic presence validation
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:255'],
        ]);

        // Step 2: ensure scheme; default to HTTPS
        $raw = trim($validated['url']);
        if (!preg_match('#^https?://#i', $raw)) {
            $raw = 'https://' . ltrim($raw, '/');
        }

        // Step 3: final URL sanity check
        if (!filter_var($raw, FILTER_VALIDATE_URL)) {
            return ApiResponse::error(
                'The url field must be a valid URL.',
                422,
                ['INVALID_URL']
            );
        }
        
        $selfDomains = [
            'apixies.io',
            parse_url(config('app.url'), PHP_URL_HOST),
            $_SERVER['HTTP_HOST'] ?? '',
            $_SERVER['SERVER_NAME'] ?? '',
        ];

        // Parse the domain from the URL
        $urlHost = parse_url($raw, PHP_URL_HOST);

        // Check if trying to scan own domain
        if ($urlHost && in_array($urlHost, array_filter($selfDomains))) {
            return ApiResponse::error(
                'Cannot scan own domain for security reasons',
                400,
                ['SELF_REFERENCE_NOT_ALLOWED']
            );
        }

        // Step 4: inspect
        $result = $inspector->inspect($raw);

        if (($result['error'] ?? false) === true) {
            \Log::debug('Error condition met', [
                'has_message' => isset($result['message']),
                'message_type' => isset($result['message']) ? gettype($result['message']) : 'not_set',
                'message_value' => $result['message'] ?? 'default_not_applied'
            ]);

            // Use the error message from the service if available
            $errorMessage = $result['message'] ?? 'Unable to fetch headers for the given URL.';

            return ApiResponse::error(
                $errorMessage,
                400,
                ['SECURITY_HEADERS_INSPECTION_FAILED']
            );
        }

        return ApiResponse::success(
            $result,
            'Security headers inspected successfully.'
        );
    }
}
