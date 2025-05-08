<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Apixies')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-gray-100 text-gray-800">

<header class="bg-gradient-to-r from-[#0A2240] to-[#007C91] py-4">
    <div class="container mx-auto flex items-center justify-between px-4">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('logo_nobg.png') }}" alt="Apixies Logo"
                 class="w-32 bg-white bg-opacity-80 p-2 rounded shadow-md">
            <h1 class="text-white text-2xl font-semibold">Apixies</h1>
        </div>

        <nav>
            @guest
                <a href="{{ url('/login') }}"
                   class="text-white hover:underline px-3 py-1">Log In</a>
                <a href="{{ url('/register') }}"
                   class="text-white hover:underline px-3 py-1">Sign Up</a>
            @else
                <form method="POST" action="{{ url('/logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="text-white hover:underline px-3 py-1">
                        Log Out
                    </button>
                </form>
            @endguest
        </nav>
    </div>
</header>

<main class="flex-1 container mx-auto px-4 py-8">
    @yield('content')
</main>

<footer class="text-center text-sm text-gray-500 py-4">
    &copy; {{ date('Y') }} Apixies. All rights reserved.
</footer>
</body>
</html>
