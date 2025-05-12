<div class="card">
    <h2 class="card-heading">Example Requests</h2>

    <h3 class="font-medium text-gray-800 mb-2">cURL</h3>
    <pre class="code-block"><code>curl -X GET \
  https://{{ request()->getHost() }}/api/v1/inspect-email?email=someone@example.com \
  -H 'Authorization: Bearer YOUR_API_KEY'</code></pre>

    <h3 class="font-medium text-gray-800 mb-2 mt-6">JavaScript (fetch)</h3>
    <pre class="code-block"><code>fetch('https://{{ request()->getHost() }}/api/v1/inspect-email?email=someone@example.com', {
  headers: { Authorization: 'Bearer YOUR_API_KEY' }
}).then(r => r.json()).then(console.log);</code></pre>

    <h3 class="font-medium text-gray-800 mb-2 mt-6">PHP</h3>
    <pre class="code-block"><code>$curl = curl_init();
curl_setopt_array($curl, [
  CURLOPT_URL => "https://{{ request()->getHost() }}/api/v1/inspect-email?email=someone@example.com",
  CURLOPT_HTTPHEADER => ["Authorization: Bearer YOUR_API_KEY"],
]);
$response = curl_exec($curl);
curl_close($curl);
echo $response;</code></pre>
</div>
