<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// SAVED PLACES ("favorites"): visitors save places they like and find them on their profile.
// Stored in the favorites pivot table (User::favorites()).
class SavedPlaceController extends Controller
{
    // The profile page, with the saved places (newest saved first)
    public function index(Request $request): View
    {
        $saved = $request->user()->favorites()
            ->approved()   // a place taken offline later simply drops out of the list
            ->with(['category', 'primaryImage', 'interests'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('user.profile', compact('saved'));
    }

    // Save the place, or un-save it if it's already saved (one button does both)
    public function toggle(Request $request, Destination $destination): RedirectResponse
    {
        // Only live places can be saved
        abort_unless($destination->isApproved(), 404);

        // toggle(): attaches the row if it's missing, removes it if it's there.
        // The unique (user_id, destination_id) index means it can never be saved twice.
        $result = $request->user()->favorites()->toggle($destination->id);
        $saved = ! empty($result['attached']);

        return back()->with('success', $saved
            ? 'Saved "' . $destination->name . '". Find it under Saved on your profile.'
            : 'Removed "' . $destination->name . '" from your saved places.');
    }
}
