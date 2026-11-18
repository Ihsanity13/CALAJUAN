<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Business owners can only manage places once an admin has checked their business.
// Until then, every place page sends them to their verification page instead.
class EnsureOwnerIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isVerifiedOwner()) {
            return redirect()->route('owner.verification.show');
        }

        return $next($request);
    }
}
