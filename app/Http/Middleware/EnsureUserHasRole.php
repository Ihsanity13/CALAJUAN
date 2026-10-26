<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    // handle() runs on every request that passes through this middleware.
    // $next is "the rest of the journey": the controller or page after it.
    // $role comes from the route, e.g. 'role:admin' sends 'admin' here.
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // If nobody is logged in, or their role doesn't match, stop here
        // and answer "403 Forbidden". The page behind it never runs.
        if (! $request->user() || $request->user()->role !== $role) {
            abort(403);
        }

        // Role matches: let the request continue to the next step.
        return $next($request);
    }
}
