{{-- resources/views/docs/partials/hero.blade.php --}}
@props([
    'title'    => 'Apixies API',
    'subtitle' => 'Build powerful applications with our simple, reliable API',
    'showCta'  => true,
    'route'    => null,
])

<div class="api-hero p-8 mb-10 rounded-lg shadow-md bg-gradient-to-r from-[#0A2240] to-[#007C91] text-white">
    {{-- Heading + tagline --}}
    <h1 class="text-3xl font-bold mb-3">{{ $title }}</h1>
    <p class="text-xl opacity-90 mb-6">{{ $subtitle }}</p>

    {{-- Action buttons based on context --}}
    @if($showCta)
        {{-- Authentication CTAs --}}
        <div class="flex flex-wrap">
            @auth
                <a href="{{ route('api-keys.index') }}"
                   class="px-4 py-2 rounded font-medium bg-white text-[#0A2240] hover:bg-gray-100 transition">
                    Manage API Keys
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-4 py-2 rounded font-medium bg-white text-[#0A2240] hover:bg-gray-100 transition mr-3">
                    Log In
                </a>
                <a href="{{ route('register') }}"
                   class="px-4 py-2 rounded font-medium bg-[#10B981] hover:bg-[#0DA271] text-white transition mr-3">
                    Sign Up for API Access
                </a>
            @endauth
            <x-suggest-modal />
        </div>
    @elseif($route)
        {{-- API demo button with complete route information --}}
        <div class="w-full">
            <x-demo-modal :route="[
                'uri' => $route['uri'] ?? '',
                'method' => $route['method'] ?? 'GET',
                'route_params' => $route['route_params'] ?? [],
                'query_params' => $route['query_params'] ?? []
            ]"/>
        </div>
    @endif
</div>
