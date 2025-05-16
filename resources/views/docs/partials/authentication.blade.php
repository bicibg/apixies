<div class="card">
    <p class="mb-4">Include <code class="param-badge">Authorization: Bearer YOUR_API_KEY</code> with every request to protected endpoints.</p>
    <pre class="code-block"><code>curl -H "Authorization: Bearer YOUR_API_KEY" \
  https://{{ request()->getHost() }}/api/v1/test</code></pre>
</div>
