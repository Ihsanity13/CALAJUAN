<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the registration form (runs when the browser opens /register)
    public function showRegister()
    {
        // view('auth.register') loads resources/views/auth/register.blade.php
        return view('auth.register');
    }

    // Handle the submitted registration form
    public function register(RegisterRequest $request)
    {
        // The rules live in RegisterRequest. Laravel runs them BEFORE this
        // method starts, and sends the user back to the form if any rule fails.
        $validated = $request->validated();

        // MODEL: create the user. Only the validated fields are passed in,
        // and 'role' isn't fillable, so new accounts always get the default 'user'.
        $user = User::create($validated);

        // Log the new user in right away
        Auth::login($user);

        // Create a fresh session ID after login (prevents session fixation attacks)
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    // Show the login form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle the submitted login form
    public function login(LoginRequest $request)
    {
        // The rules live in LoginRequest. validated() returns only email and password.
        $credentials = $request->validated();

        // Auth::attempt checks the email and password against the users table.
        // It compares against the hashed password for you.
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // intended(): go back to the page they originally wanted,
            // or to 'home' if they came straight to the login page
            return redirect()->intended(route('home'));
        }

        // Wrong credentials: go back with an error, keeping the typed email
        return back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->onlyInput('email');
    }

    // Log the user out
    public function logout(Request $request)
    {
        Auth::logout();

        // Destroy the old session and issue a new CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
