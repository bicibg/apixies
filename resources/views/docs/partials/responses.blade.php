{{-- docs/partials/responses.blade.php --}}
<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Response Format</h2>

    <p class="mb-4">All API responses follow:</p>
    <pre class="code-block mb-6"><code>{
  "status": "success", // or "error"
  "http_code": 200,
  "code": "SUCCESS_CODE",
  "message": "Human-readable message",
  "data": {} // object or array
}</code></pre>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gray-50 p-5 rounded-lg border">
            <h3 class="font-medium mb-2">Success Example</h3>
            <pre class="code-block"><code>{
  "status":"success",
  "http_code":200,
  "code":"INSPECT_EMAIL_OK",
  "message":"Email inspection successful",
  "data":{ /* ... */ }
}</code></pre>
        </div>
        <div class="bg-gray-50 p-5 rounded-lg border">
            <h3 class="font-medium mb-2">Error Example</h3>
            <pre class="code-block"><code>{
  "status":"error",
  "http_code":422,
  "code":"VALIDATION_FAILED",
  "message":"Invalid email address",
  "errors":{"email":["The email must be valid."]}
}</code></pre>
        </div>
    </div>
</div>
