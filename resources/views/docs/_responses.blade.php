<div class="tab-content hidden p-6" id="responses">
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Response Format</h2>

    <p class="mb-4">All API responses follow this envelope:</p>

    <pre class="code-block mb-6"><code>{
  "status": "success" | "error",
  "http_code": 200,
  "code": "SOME_CODE",
  "message": "Human-readable message",
  "data": { /* payload */ }
}</code></pre>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="font-medium text-base mb-3">Email Inspector Response</h3>
            <pre class="code-block"><code>{
  "status": "success",
  "http_code": 200,
  "code": "EMAIL_INSPECTION_SUCCESS",
  "message": "Email inspection successful",
  "data": {
    "email": "someone@example.com",
    "format_valid": true,
    "domain_resolvable": true,
    "mx_records_found": true,
    "mailbox_exists": false,
    "is_disposable": false,
    "is_role_based": false,
    "suggestion": "someone@gmail.com"
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
    "email": ["The email field is required.", "The email must be a valid email address."]
  }
}</code></pre>
        </div>

        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="font-medium text-base mb-3">Insufficient Scope</h3>
            <pre class="code-block"><code>{
  "status": "error",
  "http_code": 403,
  "code": "INSUFFICIENT_SCOPE",
  "message": "Insufficient scope"
}</code></pre>
        </div>
    </div>
</div>
