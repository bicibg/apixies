<div class="api-hero p-8 mb-10">
    <h1 class="text-3xl font-bold mb-3">Apixies API</h1>
    <p class="text-xl opacity-90 mb-6">Build powerful applications with our simple, reliable API</p>
    @auth
        <a href="{{ route('api-keys.index') }}" class="btn">Manage API Keys</a>
    @else
        <a href="{{ route('login') }}"    class="btn">Log In</a>
        <a href="{{ route('register') }}" class="btn bg-teal-500 text-white">Sign Up</a>
    @endauth
</div>
