<div class="tab-content hidden p-6" id="responses">
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Response Format</h2>

    <p class="mb-4">All API responses are returned in JSON with a consistent envelope:</p>

    <pre class="code-block mb-6"><code>{
  "status": "success",      // or "error"
  "http_code": 200,         // HTTP status code
  "code": "SUCCESS_CODE",   // API‐specific code
  "message": "Operation successful",
  "data": {}                // payload
}</code></pre>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="font-medium text-base mb-3">Create API Key</h3>
            <pre class="code-block"><code>{
  "status": "success",
  "http_code": 200,
  "code": "API_KEY_CREATED",
  "message": "API key created",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Production API Key",
    "plainTextToken": "1|yQPrJKDIwCRrGSJQI5SpDPZvVAHnqkH4rGI3kYgF"
  }
}</code></pre>
        </div>

        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="font-medium text-base mb-3">Validation Error</h3>
            <pre class="code-block"><code>{
  "status": "error",
  "http_code": 422,
  "code": "VALIDATION_FAILED",
  "message": "Validation failed.",
  "errors": {
    "name": ["The name field is required."]
  }
}</code></pre>
        </div>

        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="font-medium text-base mb-3">Sample Feature</h3>
            <pre class="code-block"><code>{
  "status": "success",
  "http_code": 200,
  "code": "SAMPLE_FEATURE_RESPONSE",
  "message": "Here’s a made-up feature response",
  "data": {
    "sampleKey": "sampleValue",
    "items": [1,2,3]
  }
}</code></pre>
        </div>
    </div>
</div>
