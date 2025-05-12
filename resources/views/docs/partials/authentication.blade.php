{{-- docs/partials/authentication.blade.php --}}
<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Authentication</h2>

    <p class="mb-4">Every request to protected endpoints must include <code class="param-badge">Authorization: Bearer YOUR_API_KEY</code>.</p>

    <h3 class="font-medium text-gray-800 mb-2">Example</h3>
    <pre class="code-block" aria-label="curl auth header"><code>curl -H "Authorization: Bearer YOUR_API_KEY" \
  https://{{ request()->getHost() }}/api/v1/test</code></pre>

    <hr class="my-6">

    @guest
        <p class="text-blue-800 bg-blue-50 border border-blue-200 rounded-md p-4">
            <strong>Need an API key?</strong>
            <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-semibold">Sign up</a>
            or
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-semibold">log in</a>.
        </p>
    @endguest

    @auth
        <p class="text-green-800 bg-green-50 border border-green-200 rounded-md p-4">
            Manage your keys in
            <a href="{{ route('api-keys.index') }}" class="text-green-700 hover:underline font-semibold">API Keys</a>.
        </p>
    @endauth
</div>
