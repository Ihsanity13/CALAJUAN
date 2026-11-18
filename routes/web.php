<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\DestinationImageController;
use App\Http\Controllers\Admin\InterestController;
use App\Http\Controllers\Admin\OwnerVerificationController;
use App\Http\Controllers\AuthController;
// "as PublicDestinationController": the admin one above is also called
// DestinationController, and one file can't import two classes with the same name.
use App\Http\Controllers\DestinationController as PublicDestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Owner\PlaceController;
use App\Http\Controllers\Owner\PlaceImageController;
use App\Http\Controllers\Owner\VerificationController;
use App\Http\Controllers\SavedPlaceController;
use Illuminate\Support\Facades\Route;

// PUBLIC: anyone can open the home page.
// The route only points to the controller. The logic lives elsewhere (MVC).
// ->name('home') gives the route a nickname, so other code can write
// route('home') instead of hard-coding the URL "/".
Route::get('/', [HomeController::class, 'index'])->name('home');

// LOGGED-IN ONLY: one destination's page. Guests can only see the home page;
// clicking a place sends them to log in ('auth' middleware), and after logging in
// they land back on that place (Laravel remembers it as the "intended" page).
// {destination} is "route model binding": Laravel finds the Destination with that id,
// or shows a 404 page if there's none.
Route::get('/destinations/{destination}', [PublicDestinationController::class, 'show'])
    ->middleware('auth')
    ->name('destinations.show');

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
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // resource() creates all the CRUD routes at once:
        // index, create, store, edit, update, destroy.
        Route::resource('categories', CategoryController::class)
            ->except('show');

        Route::resource('destinations', DestinationController::class)
            ->except('show');

        // Interests (Nature, Food, Adventure...) that places can be tagged with
        Route::resource('interests', InterestController::class)
            ->except('show');

        // APPROVAL QUEUE: business owners' new places and changes.
        // {change} finds a DestinationChange by id (the type-hint in the controller says which model).
        Route::controller(ApprovalController::class)
            ->prefix('approvals')
            ->name('approvals.')
            ->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('places/{destination}', 'showPlace')->name('places.show');
                Route::post('places/{destination}/approve', 'approvePlace')->name('places.approve');
                Route::post('places/{destination}/reject', 'rejectPlace')->name('places.reject');

                Route::get('changes/{change}', 'showChange')->name('changes.show');
                Route::post('changes/{change}/approve', 'approveChange')->name('changes.approve');
                Route::post('changes/{change}/reject', 'rejectChange')->name('changes.reject');
            });

        // BUSINESS OWNER VERIFICATION (the waiting list is on the Approvals page)
        Route::controller(OwnerVerificationController::class)
            ->prefix('approvals/owners')
            ->name('approvals.owners.')
            ->group(function () {
                Route::get('{verification}', 'show')->name('show');
                Route::get('{verification}/document', 'document')->name('document');
                Route::post('{verification}/approve', 'approve')->name('approve');
                Route::post('{verification}/reject', 'reject')->name('reject');
            });

        // PHOTOS of a destination (the upload itself happens in the destination form).
        // scopeBindings(): the {image} must belong to the {destination} in the URL,
        // otherwise Laravel answers 404. So nobody can change another place's photo
        // by editing the numbers in the address.
        Route::scopeBindings()->group(function () {
            // Final name: admin.destinations.images.primary
            Route::patch('destinations/{destination}/images/{image}/primary', [DestinationImageController::class, 'makePrimary'])
                ->name('destinations.images.primary');

            // Final name: admin.destinations.images.destroy
            Route::delete('destinations/{destination}/images/{image}', [DestinationImageController::class, 'destroy'])
                ->name('destinations.images.destroy');
        });
    });

// BUSINESS OWNER PORTAL: owners manage their own places at /business.
// The role check lets only owners in; DestinationPolicy (in the controller)
// then makes sure each owner only touches their OWN places.
Route::middleware(['auth', 'role:owner'])
    ->prefix('business')
    ->name('owner.')
    ->group(function () {
        // VERIFICATION: open to every owner, verified or not
        Route::get('verification', [VerificationController::class, 'show'])->name('verification.show');
        Route::put('verification', [VerificationController::class, 'update'])->name('verification.update');
    });

// The place pages: only for owners whose business an admin has verified
// (owner.verified sends everyone else to the verification page).
Route::middleware(['auth', 'role:owner', 'owner.verified'])
    ->prefix('business')
    ->name('owner.')
    ->group(function () {
        // Final names: owner.places.index, owner.places.create, ...
        // parameters(): the URL piece is {destination}, matching the Destination type-hint
        Route::resource('places', PlaceController::class)
            ->except('show')
            ->parameters(['places' => 'destination']);

        Route::delete('places/{destination}/pending-changes', [PlaceController::class, 'withdrawChange'])
            ->name('places.withdraw');

        // Live photos of the owner's place (same scoping as the admin photo routes)
        Route::scopeBindings()->group(function () {
            Route::patch('places/{destination}/images/{image}/primary', [PlaceImageController::class, 'makePrimary'])
                ->name('places.images.primary');
            Route::delete('places/{destination}/images/{image}', [PlaceImageController::class, 'destroy'])
                ->name('places.images.destroy');
        });
    });

// VISITORS ONLY: their profile with their saved places, and the Save button.
// (Saving is a visitor feature; owners and admins have their own pages.)
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/profile', [SavedPlaceController::class, 'index'])->name('user.profile');

    // One button saves or un-saves (POST, because it changes data)
    Route::post('/destinations/{destination}/save', [SavedPlaceController::class, 'toggle'])
        ->name('destinations.save');
});
