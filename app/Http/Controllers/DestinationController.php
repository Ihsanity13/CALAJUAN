<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// PUBLIC destination pages (the admin's add/edit/delete live in Admin\DestinationController).
class DestinationController extends Controller
{
    // Show one destination: photos, details, map and reviews.
    // If the id in the URL doesn't exist, route model binding already answered 404.
    public function show(Destination $destination): View
    {
        // A place that isn't approved yet acts as if it doesn't exist (404),
        // except for its owner and the admins, who see a preview (DestinationPolicy::view).
        abort_unless(Gate::allows('view', $destination), 404);

        // load() = eager loading: everything the page needs, in a few queries
        // instead of one query per photo or review (the N+1 problem).
        $destination->load([
            'category',
            'images',
            'interests',
            'hours',
            // newest reviews first, each with its author's name
            'reviews' => fn ($query) => $query->latest()->with('user:id,name'),
        ]);

        // Average rating from our PostgreSQL function destination_avg_rating()
        // (see the 2026_10_14 migration). It returns NULL when there are no reviews yet.
        $averageRating = DB::scalar('SELECT destination_avg_rating(?)', [$destination->id]);

        return view('destinations.show', [
            'destination' => $destination,
            'averageRating' => $averageRating !== null ? (float) $averageRating : null,
            'reviewCount' => $destination->reviews->count(),
            // Only visitors (role 'user') can save; null = no Save button for owners and admins
            'isSaved' => auth()->user()?->role === 'user'
                ? auth()->user()->favorites()->whereKey($destination->id)->exists()
                : null,
        ]);
    }
}
