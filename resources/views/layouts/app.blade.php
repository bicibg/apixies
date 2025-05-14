<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-E91MYB4CWC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-E91MYB4CWC');
    </script>
    <!-- End Google Tag Manager -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Apixies')</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
    <link rel="manifest" href="/favicon/site.webmanifest" />

    <meta name="theme-color" content="#0A2240">

    <!-- Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-gray-800">

<!-- Sandbox status bar -->
<div id="sandbox-bar" class="hidden bg-yellow-100 text-yellow-800 p-2 text-center">
    Demo mode • calls left: <span id="sandbox-quota">—</span> • expires in: <span id="sandbox-expiry">—</span>s
</div>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MXM728HH"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- Header -->
<header class="bg-gradient-to-r from-[#0A2240] to-[#007C91] py-3">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between px-4">
        <div class="flex items-center mb-3 md:mb-0">
            <a href="/" class="flex items-center hover:opacity-95 transition-opacity">
                <div class="bg-white p-2 rounded flex items-center justify-center mr-3">
                    <span class="text-[#0A2240] font-bold text-sm">Apixies</span>
                </div>
                <span class="text-white text-xl font-medium">Developer API Suite</span>
            </a>
        </div>

        <!-- Navigation links -->
        <div class="flex flex-wrap justify-center md:justify-end items-center space-x-3 md:space-x-5">
            <a href="{{ route('docs.index') }}"
               class="text-white hover:text-gray-200 text-sm py-1 {{ request()->routeIs('docs.*') ? 'font-medium' : '' }}">
                API Docs
            </a>

            <a href="{{ route('api-keys.index') }}"
               class="text-white hover:text-gray-200 text-sm py-1 {{ request()->routeIs('api-keys.*') ? 'font-medium' : '' }}">
                API Keys
            </a>

            <a href="{{ route('suggestions.board') }}"
               class="text-white hover:text-gray-200 text-sm py-1 {{ request()->routeIs('suggestions.board') ? 'font-medium' : '' }}">
                Community Ideas
            </a>

            @auth
                <span class="text-white text-sm py-1">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="ml-2">
                    @csrf
                    <button type="submit"
                            class="bg-white text-[#0A2240] hover:bg-gray-100 px-3 md:px-4 py-1 rounded text-sm">
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ url('/login') }}"    class="text-white hover:text-gray-200 text-sm py-1">Log In</a>
                <a href="{{ url('/register') }}" class="bg-white text-[#0A2240] hover:bg-gray-100 px-3 md:px-4 py-1 rounded text-sm ml-2">
                    Sign Up
                </a>
            @endguest
        </div>
    </div>
</header>

<!-- Mobile menu toggle -->
<div class="md:hidden bg-gray-100 border-b border-gray-200 py-2 px-4">
    <button id="mobile-menu-toggle" class="flex text-left items-center text-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16m-7 6h7"/>
        </svg>
        Menu
    </button>
</div>

<!-- Mobile Nav ---------------------------------------------------------->
<div id="mobile-menu" class="hidden md:hidden bg-white shadow-md">
    <div class="px-4 py-2 space-y-2">
        <a href="{{ route('docs.index') }}"        class="block py-2 text-gray-800 hover:bg-gray-100 rounded">API Docs</a>
        <a href="{{ route('api-keys.index') }}"    class="block py-2 text-gray-800 hover:bg-gray-100 rounded">API Keys</a>

        {{-- NEW link --}}
        <a href="{{ route('suggestions.board') }}" class="block py-2 text-gray-800 hover:bg-gray-100 rounded">Community Ideas</a>

        @auth
            <div class="py-2 px-3 text-gray-600">{{ Auth::user()->name }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="block w-full text-left py-2 px-3 text-red-600 hover:bg-gray-100 rounded">
                    Log Out
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"    class="block py-2 text-gray-800 hover:bg-gray-100 rounded">Log In</a>
            <a href="{{ route('register') }}" class="block py-2 text-blue-600 hover:bg-gray-100 rounded">Sign Up</a>
        @endauth
    </div>
</div>


<!-- Main Content -->
<main class="flex-1 container mx-auto px-4 py-8">
    @yield('content')
</main>

<!-- Footer -->
<footer class="text-center text-sm text-gray-500 py-4">
    &copy; {{ date('Y') }} Apixies. All rights reserved.
</footer>

<!-- Mobile menu script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('mobile-menu-toggle');
        const menu   = document.getElementById('mobile-menu');
        if (toggle && menu) {
            toggle.addEventListener('click', () => menu.classList.toggle('hidden'));
        }
    });
    const SANDBOX_KEY = 'apixies_sandbox';

    async function fetchNewSandbox() {
        const res = await fetch('{{ route('sandbox.token') }}');
        const json = await res.json();
        const data = {
            token: json.token,
            expires: Date.now() + json.expires_in * 1000,
            quota: json.quota
        };
        localStorage.setItem(SANDBOX_KEY, JSON.stringify(data));
        return data;
    }

    async function initSandbox() {
        let sb = null;
        try {
            const stored = localStorage.getItem(SANDBOX_KEY);
            if (stored) {
                sb = JSON.parse(stored);
                // if expired, fetch new
                if (Date.now() > sb.expires) {
                    sb = await fetchNewSandbox();
                }
            } else {
                sb = await fetchNewSandbox();
            }
        } catch (e) {
            console.error('Sandbox init error', e);
            sb = await fetchNewSandbox();
        }

        // show bar
        window.sandbox = sb;
        document.getElementById('sandbox-bar').classList.remove('hidden');
        document.getElementById('sandbox-quota').innerText = sb.quota;
        updateExpiry();
        setInterval(updateExpiry, 1000);
    }

    function updateExpiry() {
        const sec = Math.max(0, Math.ceil((window.sandbox.expires - Date.now()) / 1000));
        document.getElementById('sandbox-expiry').innerText = sec;
        if (sec <= 0) {
            // clear and re-init
            localStorage.removeItem(SANDBOX_KEY);
            initSandbox();
        }
    }

    initSandbox();
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x/dist/cdn.min.js" defer></script>

</body>
</html>
