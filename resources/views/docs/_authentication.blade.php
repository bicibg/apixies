<div class="tab-content hidden p-6" id="authentication">
    <h2 class="text-xl font-semibold text-[#0A2240] mb-4">Authentication</h2>

    @guest
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
            <div class="flex">
                <p class="text-blue-800">
                    <strong>Want to use our API?</strong>
                    <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-semibold">Sign up</a>
                    or
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-semibold">Log in</a>
                    to manage your API keys.
                </p>
            </div>
        </div>
    @endguest

    <p class="mb-4">
        All future endpoints under <code class="font-mono">/api/v1/</code> require a valid API key.
        <strong>Except</strong>:
        <code>/api/v1/health</code> and <code>/api/v1/ready</code> are public.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="font-medium text-base mb-3 flex items-center">
                Bearer Authentication (recommended)
            </h3>
            <pre class="code-block"><code>Authorization: Bearer YOUR_API_KEY</code></pre>
        </div>

        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="font-medium text-base mb-3 flex items-center">
                X-API-KEY Header
            </h3>
            <pre class="code-block"><code>X-API-KEY: YOUR_API_KEY</code></pre>
        </div>
    </div>

    @auth
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-2 flex items-center">
            <p class="text-green-800">
                Manage your keys any time in
                <a href="{{ route('api-keys.index') }}" class="text-green-700 hover:underline font-semibold">API Keys</a>.
            </p>
        </div>
    @endauth
</div>
