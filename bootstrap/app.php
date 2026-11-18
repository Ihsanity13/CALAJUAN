<?php

use App\Http\Middleware\EnsureOwnerIsVerified;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register the nickname 'role', so routes can write 'role:admin'
        // instead of the full class name.
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            // 'owner.verified': business owners whose business an admin has checked
            'owner.verified' => EnsureOwnerIsVerified::class,
        ]);

        // Logged-in people who open /login or /register (the 'guest' pages)
        // are sent to their own start page: admins to the dashboard, and so on.
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()->homeUrl());

        // TRUSTED PROXIES: the cloudflared tunnel runs on this same machine and
        // forwards visitors to 127.0.0.1:8000, adding "X-Forwarded-Proto: https".
        // Trusting only localhost makes Laravel believe that header, so asset()
        // and route() build https:// links behind the tunnel (no mixed content).
        // Opening http://127.0.0.1:8000 directly sends no such header, so it stays http.
        // Only localhost is trusted, so outside visitors can't fake the header.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
