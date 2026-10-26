<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// PUBLIC: anyone can open the home page.
// The route only points to the controller. The logic lives elsewhere (MVC).
// ->name('home') gives the route a nickname, so other code can write
// route('home') instead of hard-coding the URL "/".
Route::get('/', [HomeController::class, 'index'])->name('home');

// GUEST ONLY: the 'guest' middleware blocks people who are already
// logged in. Registering or logging in twice makes no sense.
Route::middleware('guest')->group(function () {
    // GET = the browser asks to SEE a page (the empty form)
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    // POST = the browser SUBMITS data (the filled-in form)
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// LOGGED-IN ONLY: the 'auth' middleware sends visitors who aren't logged in
// to the route named 'login'. Logout uses POST because it changes the
// session; GET links can be triggered accidentally by other websites.
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ROLE-PROTECTED PAGES
// All admin pages share one group: they need login + the admin role,
// their URLs start with /admin, and their route names start with "admin.".
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Final name: admin.dashboard, URL: /admin
        Route::view('/', 'admin.dashboard')->name('dashboard');

        // resource() creates all the CRUD routes at once:
        // index, create, store, edit, update, destroy.
        Route::resource('categories', CategoryController::class)
            ->except('show');

        Route::resource('destinations', DestinationController::class)
            ->except('show');
    });

// A page only regular users can open, to show the second role's access.
Route::view('/profile', 'user.profile')
    ->middleware(['auth', 'role:user'])
    ->name('user.profile');
