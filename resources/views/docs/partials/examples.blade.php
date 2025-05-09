{{-- docs/partials/examples.blade.php --}}
<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Example Requests</h2>

    <div class="mb-6">
        <h3 class="font-medium text-gray-800 mb-2">cURL</h3>
        <pre class="code-block"><code>curl -X GET \
  https://{{ request()->getHost() }}/api/v1/inspect-email?email=someone@example.com \
  -H 'Authorization: Bearer YOUR_API_KEY'</code></pre>
    </div>

    <div class="mb-6">
        <h3 class="font-medium text-gray-800 mb-2">JavaScript</h3>
        <pre class="code-block"><code>fetch('https://{{ request()->getHost() }}/api/v1/inspect-email?email=someone@example.com', {
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
.then(res => res.json())
.then(console.log);</code></pre>
    </div>

    <div>
        <h3 class="font-medium text-gray-800 mb-2">PHP</h3>
        <pre class="code-block"><code>$curl = curl_init();
curl_setopt_array($curl, [
  CURLOPT_URL => "https://{{ request()->getHost() }}/api/v1/inspect-email?email=someone@example.com",
  CURLOPT_HTTPHEADER => ["Authorization: Bearer YOUR_API_KEY"],
]);
$response = curl_exec($curl);
curl_close($curl);
echo $response;</code></pre>
    </div>
</div>
