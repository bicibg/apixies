<div class="api-hero p-8 mb-10 rounded-lg shadow-md bg-gradient-to-r from-[#0A2240] to-[#007C91] text-white">
    <h1 class="text-3xl font-bold mb-3">Apixies API</h1>
    <p class="text-xl opacity-90 mb-6">Build powerful applications with our simple, reliable API</p>

    @auth
        <div class="flex space-x-4">
            <a href="{{ route('api-keys.index') }}"
               class="bg-white text-[#0A2240] px-5 py-2 rounded-md font-medium hover:bg-gray-100 transition">
                Manage API Keys
            </a>
        </div>
    @else
        <div class="flex space-x-4">
            <a href="{{ route('login') }}"
               class="bg-white text-[#0A2240] px-5 py-2 rounded-md font-medium hover:bg-gray-100 transition">
                Log In
            </a>
            <a href="{{ route('register') }}"
               class="bg-green-500 text-white px-5 py-2 rounded-md font-medium hover:bg-green-600 transition">
                Sign Up for API Access
            </a>
        </div>
    @endauth
</div>
