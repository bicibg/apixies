<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-MXM728HH');</script>
    <!-- End Google Tag Manager -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Apixies')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-gray-800">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MXM728HH"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<header class="bg-gradient-to-r from-[#0A2240] to-[#007C91] py-3">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between px-4">
        <!-- Logo and Title with proper styling -->
        <div class="flex items-center mb-3 md:mb-0">
            <!-- White box with Apixies text - now with link to home -->
            <a href="/" class="flex items-center hover:opacity-95 transition-opacity">
                <div class="bg-white p-2 rounded flex items-center justify-center mr-3">
                    <span class="text-[#0A2240] font-bold text-sm">Apixies</span>
                </div>
                <span class="text-white text-xl font-medium">Developer API Suite</span>
            </a>
        </div>

        <!-- Navigation links -->
        <div class="flex flex-wrap justify-center md:justify-end items-center space-x-3 md:space-x-5">
            <a href="{{ route('dashboard') }}" class="text-white hover:text-gray-200 text-sm py-1">API Docs</a>
            <a href="{{ route('api-keys.index') }}" class="text-white hover:text-gray-200 text-sm py-1">API Keys</a>

            @auth
                <span class="text-white text-sm py-1">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="ml-2">
                    @csrf
                    <button type="submit" class="bg-white text-[#0A2240] hover:bg-gray-100 px-3 md:px-4 py-1 rounded text-sm">
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ url('/login') }}" class="text-white hover:text-gray-200 text-sm py-1">Log In</a>
                <a href="{{ url('/register') }}" class="bg-white text-[#0A2240] hover:bg-gray-100 px-3 md:px-4 py-1 rounded text-sm ml-2">
                    Sign Up
                </a>
            @endguest
        </div>
    </div>
</header>

<!-- Mobile menu toggle button (visible on small screens) -->
<div class="md:hidden bg-gray-100 border-b border-gray-200 py-2 px-4">
    <button id="mobile-menu-toggle" class="flex items-center text-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
        Menu
    </button>
</div>

<!-- Mobile menu (hidden by default, shown when toggled) -->
<div id="mobile-menu" class="hidden md:hidden bg-white shadow-md">
    <div class="container mx-auto px-4 py-2 space-y-2">
        <a href="{{ route('dashboard') }}" class="block py-2 px-3 text-gray-800 hover:bg-gray-100 rounded">API Docs</a>
        <a href="{{ route('api-keys.index') }}" class="block py-2 px-3 text-gray-800 hover:bg-gray-100 rounded">API Keys</a>
        @auth
            <div class="py-2 px-3 text-gray-600">{{ Auth::user()->name }}</div>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left py-2 px-3 text-red-600 hover:bg-gray-100 rounded">
                    Log Out
                </button>
            </form>
        @else
            <a href="{{ url('/login') }}" class="block py-2 px-3 text-gray-800 hover:bg-gray-100 rounded">Log In</a>
            <a href="{{ url('/register') }}" class="block py-2 px-3 text-blue-600 hover:bg-gray-100 rounded">Sign Up</a>
        @endauth
    </div>
</div>

<main class="flex-1 container mx-auto px-4 py-8">
    @yield('content')
</main>

<footer class="text-center text-sm text-gray-500 py-4">
    &copy; {{ date('Y') }} Apixies. All rights reserved.
</footer>

<!-- Mobile menu toggle script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuToggle && mobileMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script>

</body>
</html>
