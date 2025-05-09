<div class="tab-content hidden p-6" id="features">
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">API Features</h2>

    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Request Tracking</h3>
            <p>Each API request is assigned a unique request ID, returned in the <code class="bg-gray-100 px-1 py-0.5 rounded text-sm font-mono">X-Request-ID</code> header. You may also supply your own.</p>
            <pre class="code-block"><code>// Example header
X-Request-ID: 550e8400-e29b-41d4-a716-446655440000</code></pre>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">CORS Support</h3>
            <ul class="list-disc ml-6 mb-2">
                <li>Access-Control-Allow-Origin: *</li>
                <li>Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS</li>
                <li>Access-Control-Allow-Headers: Content-Type, Authorization</li>
            </ul>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Input Sanitization</h3>
            <p>All input fields are trimmed and sanitized to remove leading/trailing whitespace and guard against injection.</p>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">JSON-Only Responses</h3>
            <p>Every endpoint returns well-formed JSON—no HTML—so your client can reliably parse it.</p>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Rate Limiting</h3>
            <p>Limits of <strong>100 requests/minute</strong> per API key. Exceeding it returns HTTP 429.</p>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Security</h3>
            <ul class="list-disc ml-6">
                <li>Strict security headers (CSP, HSTS, X-Frame-Options, etc.)</li>
                <li>CSRF protection on web routes; API routes are exempt</li>
            </ul>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Error Handling</h3>
            <p>All errors include an HTTP status, machine-readable code, human message, and validation details when applicable.</p>
        </div>
    </div>
</div>
