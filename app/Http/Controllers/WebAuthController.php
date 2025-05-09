<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class WebAuthController extends Controller
{
    public function __construct()
    {
        // Throttle login & registration attempts
        $this->middleware('throttle:10,5')->only(['authenticate', 'register']);
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle new user registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','email','max:255','unique:users'],
            'password' => [
                'required','confirmed',
                Password::min(10)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        // Send email verification
        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('/')
            ->with('status', 'Registration successful. Please verify your email.');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Authenticate an existing user.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($request->only('email','password'), $request->filled('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if (! $user->hasVerifiedEmail()) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Please verify your email address.']);
        }

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Login successful.');
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
