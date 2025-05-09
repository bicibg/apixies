<div class="tab-content hidden p-6" id="examples">
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Example Requests</h2>

    <div class="mb-6">
        <h3 class="font-medium text-gray-800 mb-3">cURL Example</h3>
        <pre class="code-block"><code>curl -X GET \
  https://{{ request()->getHost() }}/api/v1/inspect-email?email=foo@bar.com \
  -H 'Authorization: Bearer YOUR_API_KEY'</code></pre>
    </div>

    <div class="mb-6">
        <h3 class="font-medium text-gray-800 mb-3">JavaScript Example</h3>
        <pre class="code-block"><code>// JavaScript fetch example
fetch('https://{{ request()->getHost() }}/api/v1/inspect-email?email=foo@bar.com', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));</code></pre>
    </div>

    <div>
        <h3 class="font-medium text-gray-800 mb-3">PHP cURL Example</h3>
        <pre class="code-block"><code>// PHP cURL example
$curl = curl_init();
curl_setopt_array($curl, [
  CURLOPT_URL => "https://{{ request()->getHost() }}/api/v1/inspect-email?email=foo@bar.com",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer YOUR_API_KEY",
    "Content-Type: application/json"
  ],
]);
$response = curl_exec($curl);
curl_close($curl);
echo $response;</code></pre>
    </div>
</div>
