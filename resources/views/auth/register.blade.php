@extends('layouts.app')

@section('title', 'Sign Up – Apixies')

@section('content')
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <h2 class="text-3xl font-bold text-center text-[#0A2240] mb-4">Create an Account</h2>
            <p class="text-center text-gray-600 mb-6">
                Get your free API key and start building.
            </p>

            <div id="signup-error" class="text-red-600 text-sm mb-4"></div>

            <form id="signup-form" class="space-y-5">
                <div>
                    <label class="block text-gray-700 mb-1">Name</label>
                    <input name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400"/>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">Email</label>
                    <input name="email" type="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400"/>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">Password</label>
                    <input name="password" type="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400"/>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">Confirm Password</label>
                    <input name="password_confirmation" type="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400"/>
                </div>

                <button type="submit"
                        class="w-full bg-[#007C91] hover:bg-[#005f6b] text-white font-semibold py-2 rounded-lg transition">
                    Sign Up
                </button>
            </form>
        </div>

        <div class="bg-gray-50 px-6 py-4 text-center text-sm">
            Already have an account?
            <a href="{{ url('/login') }}" class="text-[#0A2240] font-medium hover:underline">
                Log In
            </a>
        </div>
    </div>

    <script>
        document.getElementById('signup-form').onsubmit = async e => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            try {
                const res = await fetch('/api/v1/register', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify(data)
                });
                const json = await res.json();
                if (!res.ok) throw json;
                localStorage.setItem('APIToken', json.data.token);
                window.location = '/';
            } catch (err) {
                document.getElementById('signup-error').textContent =
                    err.errors?.email?.[0] || err.message || 'Registration failed';
            }
        };
    </script>
@endsection
