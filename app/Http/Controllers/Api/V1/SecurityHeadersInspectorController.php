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
        // Step 1: basic presence validation
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:255'],
        ]);

        // Step 2: ensure scheme; default to HTTPS
        $raw = trim($validated['url']);
        if (!preg_match('#^https?://#i', $raw)) {
            $raw = 'https://' . ltrim($raw, '/');
        }

        // Step 3: final URL sanity check
        if (!filter_var($raw, FILTER_VALIDATE_URL)) {
            return ApiResponse::error(
                null,
                'The url field must be a valid URL.',
                422,
                'INVALID_URL'
            );
        }

        // Step 4: inspect
        $result = $inspector->inspect($raw);

        if (($result['error'] ?? false) === true) {
            return ApiResponse::error(
                null,
                'Unable to fetch headers for the given URL.',
                400,
                'SECURITY_HEADERS_INSPECTION_FAILED'
            );
        }

        return ApiResponse::success(
            $result,
            'Security headers inspected successfully.'
        );
    }
}
