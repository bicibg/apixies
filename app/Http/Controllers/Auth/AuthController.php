<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:10,5')->only(['login', 'register']);
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

        Auth::login($user);

        // Send email verification using direct mail (bypassing notification system)
        $this->sendVerificationEmailDirectly($user);

        return redirect()->route('docs.index')
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
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user needs verification
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&
                !$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended(route('docs.index'));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
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

    /**
     * Manually verify a user's email (for admins/debugging).
     */
    public function manualVerify(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Add security checks here
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403);
        }

        $email = $request->input('email');

        if (empty($email)) {
            return back()->with('error', 'Email is required');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->with('error', 'User not found');
        }

        // Set the email_verified_at timestamp if it's not already set
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return back()->with('status', 'User email has been manually verified');
        }

        return back()->with('status', 'User email was already verified');
    }

    /**
     * Send verification email directly using DirectMailService
     */
    private function sendVerificationEmailDirectly($user)
    {
        \App\Services\DirectMailService::sendEmailVerification($user);
    }
}
