<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Category;
use App\Models\User;
use App\Services\OwnerVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Show the registration form (runs when the browser opens /register)
    public function showRegister()
    {
        // view('auth.register') loads resources/views/auth/register.blade.php.
        // The categories are the "Type of business" choices for business owners.
        return view('auth.register', ['categories' => Category::orderBy('name')->get()]);
    }

    // Handle the submitted registration form
    public function register(RegisterRequest $request, OwnerVerificationService $verifications)
    {
        // The rules live in RegisterRequest. Laravel runs them BEFORE this
        // method starts, and sends the user back to the form if any rule fails.
        $validated = $request->validated();

        // MODEL: create the user. 'role' isn't fillable, so it's set separately
        // with forceFill, and only to one of the two allowed choices:
        // a business owner gets 'owner', everyone else the default 'user'.
        $isOwner = ($validated['account_type'] ?? 'visitor') === 'owner';

        // A transaction: a business owner's account and business details are saved
        // together, so there's never an owner account without its details.
        $user = DB::transaction(function () use ($validated, $isOwner, $verifications) {
            $user = new User(Arr::only($validated, ['name', 'email', 'password']));
            $user->forceFill(['role' => $isOwner ? 'owner' : 'user'])->save();

            // Owners: keep the basics about their business. They send the permit
            // details next, on their verification page.
            if ($isOwner) {
                $verifications->startDraft($user, $validated);
            }

            return $user;
        });

        // Log the new user in right away
        Auth::login($user);

        // Create a fresh session ID after login (prevents session fixation attacks)
        $request->session()->regenerate();

        // Owners go straight to step 2: verifying their business. Visitors go back
        // to the place they clicked before signing up (intended), or else home.
        return $user->isOwner()
            ? redirect()->route('owner.verification.show')
                ->with('success', 'Your account is ready. One more step: verify your business.')
            : redirect()->intended($user->homeUrl());
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

            $user = $request->user();

            // Admins always start on the dashboard.
            if ($user->isAdmin()) {
                $request->session()->forget('url.intended');

                return redirect($user->homeUrl());
            }

            // Everyone else: intended() goes back to the page they originally wanted,
            // or else to the start page for their role (User::homeUrl())
            return redirect()->intended($user->homeUrl());
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
