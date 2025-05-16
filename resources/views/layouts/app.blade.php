<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-E91MYB4CWC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', 'G-E91MYB4CWC');
    </script>
    <!-- End Google Tag Manager -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
          content="{{ config('app.name') }} - {{ config('app.tagline', 'Tiny APIs, Mighty Results') }}">

    <title>@yield('title', 'Apixies')</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96"/>
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg"/>
    <link rel="shortcut icon" href="/favicon/favicon.ico"/>
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png"/>
    <link rel="manifest" href="/favicon/site.webmanifest"/>

    <meta name="theme-color" content="var(--color-navy)">

    <!-- Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-blue-50 text-gray-900">

<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MXM728HH"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- Mobile and Desktop Header with Alpine.js -->
<div x-data="mobileMenu">
    <!-- Desktop Header - ONLY visible on md screens and up -->
    <div class="hidden md:block">
        <header class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-navy to-teal">
            <div class="container mx-auto flex items-center justify-between h-12 px-4">
                <!-- Logo with branding -->
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <div class="bg-white rounded px-2 py-1 mr-3">
                            <span class="text-teal font-bold text-sm">Apixies</span>
                        </div>
                        <div class="hidden md:block">
                            <span class="text-white text-sm">Developer API Suite</span>
                            <span
                                class="block text-xs text-blue-100">{{ config('app.tagline', 'Tiny APIs, Mighty Results') }}</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('docs.index') }}"
                       class="text-white text-sm {{ request()->routeIs('docs.*') ? 'font-medium' : '' }} hover:text-blue-100 transition-colors duration-200">
                        API Docs
                    </a>
                    <a href="{{ route('api-keys.index') }}"
                       class="text-white text-sm {{ request()->routeIs('api-keys.*') ? 'font-medium' : '' }} hover:text-blue-100 transition-colors duration-200">
                        API Keys
                    </a>
                    <a href="{{ route('suggestions.board') }}"
                       class="text-white text-sm {{ request()->routeIs('suggestions.board') ? 'font-medium' : '' }} hover:text-blue-100 transition-colors duration-200">
                        Community Ideas
                    </a>
                </div>

                <!-- Auth Links -->
                <div class="flex items-center">
                    @auth
                        <span class="text-white text-sm mr-2">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit"
                                    class="bg-white text-teal hover:bg-blue-50 px-2 py-0.5 rounded text-sm transition-colors duration-200">
                                Log Out
                            </button>
                        </form>
                    @else
                        <a href="{{ url('/login') }}"
                           class="text-white hover:text-blue-100 text-sm mr-3 transition-colors duration-200">
                            Log In
                        </a>
                        <a href="{{ url('/register') }}"
                           class="bg-white text-teal hover:bg-blue-50 px-2 py-0.5 rounded text-sm transition-colors duration-200">
                            Sign Up
                        </a>
                    @endguest
                </div>
            </div>
        </header>
    </div>

    <!-- Mobile Header - ONLY visible on screens smaller than md -->
    <div class="md:hidden">
        <div
            class="flex items-center justify-between h-12 bg-gradient-to-r from-navy to-teal fixed top-0 left-0 right-0 z-50 px-4 shadow-sm">
            <!-- Logo for mobile -->
            <a href="/" class="flex items-center">
                <div class="bg-white rounded px-2 py-0.5 mr-2">
                    <span class="text-teal font-bold text-sm">Apixies</span>
                </div>
            </a>

            <!-- Mobile menu button -->
            <button @click="toggleMenu()" class="text-white p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16m-7 6h7"/>
                    <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu (Full Screen) -->
    <div x-show="open" x-cloak @click.outside="closeMenu()"
         class="md:hidden fixed inset-0 z-40 bg-navy-dark overflow-y-auto pt-16">
        <!-- Mobile Menu Content -->
        <div class="flex flex-col items-center mt-6 px-4">
            <!-- Title and Tagline -->
            <div class="text-center mb-10">
                <h1 class="text-white text-xl font-medium mb-1">Developer API Suite</h1>
                <p class="text-blue-200 text-sm">{{ config('app.tagline', 'Tiny APIs, Mighty Results') }}</p>
            </div>

            <!-- Main navigation links -->
            <div class="w-full max-w-md space-y-4 mb-8">
                <a href="{{ route('docs.index') }}"
                   class="block text-center text-white text-lg py-3 px-4 rounded bg-teal/80 hover:bg-teal">
                    API Docs
                </a>
                <a href="{{ route('api-keys.index') }}"
                   class="block text-center text-white text-lg py-3 px-4 rounded bg-teal/80 hover:bg-teal">
                    API Keys
                </a>
                <a href="{{ route('suggestions.board') }}"
                   class="block text-center text-white text-lg py-3 px-4 rounded bg-teal/80 hover:bg-teal">
                    Community Ideas
                </a>
            </div>

            <!-- Border Separator -->
            <div class="w-full max-w-md border-t border-teal-700 my-6"></div>

            <!-- Authentication Links -->
            <div class="w-full max-w-md">
                @auth
                    <div class="text-center mb-6">
                        <span class="text-blue-300 text-sm block mb-1">Signed in as</span>
                        <span class="text-white text-lg font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ url('/logout') }}" class="flex justify-center">
                        @csrf
                        <button type="submit"
                                class="w-full bg-white text-teal hover:bg-blue-50 py-3 px-8 rounded-md text-lg font-medium">
                            Log Out
                        </button>
                    </form>
                @else
                    <div class="flex flex-col space-y-4">
                        <a href="{{ url('/login') }}"
                           class="block text-center text-white border border-white hover:bg-teal-700 py-3 px-8 rounded-md text-lg">
                            Log In
                        </a>
                        <a href="{{ url('/register') }}"
                           class="block text-center bg-white text-teal hover:bg-blue-50 py-3 px-8 rounded-md text-lg font-medium">
                            Sign Up
                        </a>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</div>

<!-- Spacer to prevent content from being hidden under the fixed header -->
<div class="h-16"></div>

<!-- Main Content -->
<main class="flex-1 container mx-auto px-4 py-6">
    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-blue-50 border-t border-blue-100 mt-auto">
    <div class="container mx-auto py-3">
        <div class="flex flex-col md:flex-row justify-between items-center px-4">
            <!-- Logo and Tagline -->
            <div class="flex items-center mb-3 md:mb-0">
                <div class="bg-teal rounded px-2 py-0.5 mr-3">
                    <span class="text-white font-bold text-xs">Apixies</span>
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center">
                    <span class="text-teal text-xs font-medium mr-2">Developer API Suite</span>
                    <span class="text-gray-500 text-xs">{{ config('app.tagline', 'Tiny APIs, Mighty Results') }}</span>
                </div>
            </div>

            <!-- Links with improved spacing -->
            <div class="flex mb-3 md:mb-0">
                <a href="{{ route('docs.index') }}"
                   class="text-teal hover:text-teal-700 text-xs transition-colors duration-200 px-3 py-1 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    API Docs
                </a>
                <a href="{{ route('api-keys.index') }}"
                   class="text-teal hover:text-teal-700 text-xs transition-colors duration-200 px-3 py-1 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    API Keys
                </a>
                <a href="{{ route('suggestions.board') }}"
                   class="text-teal hover:text-teal-700 text-xs transition-colors duration-200 px-3 py-1 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Community Ideas
                </a>
            </div>

            <!-- Copyright -->
            <div class="text-gray-500 text-xs">
                &copy; {{ date('Y') }} Apixies. All rights reserved.
            </div>
        </div>
    </div>
</footer>

</body>
</html>
