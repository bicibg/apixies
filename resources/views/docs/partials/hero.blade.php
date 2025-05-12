@props([
    'title'    => 'Apixies API',
    'subtitle' => 'Build powerful applications with our simple, reliable API',
    'showCta'  => true,
])

<div class="api-hero p-8 mb-10 rounded-lg shadow-md bg-gradient-to-r from-[#0A2240] to-[#007C91] text-white">
    {{-- Heading + tagline --}}
    <h1 class="text-3xl font-bold mb-3">{{ $title }}</h1>
    <p class="text-xl opacity-90 mb-6">{{ $subtitle }}</p>

    @if($showCta)
        <div class="flex flex-wrap items-center space-x-4">
            @auth
                <a href="{{ route('api-keys.index') }}"
                   class="inline-block bg-white text-[#0A2240] font-semibold px-5 py-2 rounded-md
                          focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white
                          hover:bg-gray-100 transition"
                >
                    Manage API Keys
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-block bg-white text-[#0A2240] font-semibold px-5 py-2 rounded-md
                          focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white
                          hover:bg-gray-100 transition"
                   aria-label="Log in to Apixies"
                >
                    Log In
                </a>
                <a href="{{ route('register') }}"
                   class="inline-block bg-[#10B981] text-white font-semibold px-5 py-2 rounded-md
                          focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#10B981]
                          hover:bg-[#059669] transition"
                   aria-label="Sign up for API access"
                >
                    Sign Up for API Access
                </a>
            @endauth

            {{-- Suggest button --}}
            <x-suggest-modal class="mt-2 md:mt-0"/>
        </div>
    @endif
</div>
