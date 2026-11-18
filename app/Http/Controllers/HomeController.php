<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationChange;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    // The home page. Its welcome banner is different for each role:
    // - visitors and guests: "Find where to go in Calauan" + the featured places
    // - business owners: their places at a glance (or their verification status)
    // - admins: what's waiting for review
    // The controller only gathers the numbers; the words are in home.blade.php.
    public function index(): View
    {
        $user = auth()->user();

        if ($user?->isAdmin()) {
            return view('home', [
                'heroRole' => 'admin',
                'showFeatured' => false,
                'approvalCount' => User::pendingApprovalCount(),
                'liveCount' => Destination::approved()->count(),
            ]);
        }

        if ($user?->isOwner()) {
            $places = $user->ownedDestinations();

            return view('home', [
                'heroRole' => 'owner',
                'showFeatured' => false,
                // approved / pending / rejected, or null when no details were sent yet
                'verificationStatus' => $user->ownerVerification?->status,
                'liveCount' => (clone $places)->approved()->count(),
                'waitingCount' => (clone $places)->where('status', Destination::STATUS_PENDING)->count()
                    + DestinationChange::whereIn('destination_id', (clone $places)->select('id'))
                        ->where('status', DestinationChange::STATUS_PENDING)->count(),
            ]);
        }

        // VISITORS AND GUESTS: the featured places.
        // with('category') loads the category names in one extra query
        // instead of one query per row (the N+1 problem).
        // withAvg / withCount add reviews_avg_rating and reviews_count to each
        // destination in the same query, so the cards can show the star rating.
        // primaryImage = the cover photo for each card, loaded in one extra query.
        $featured = Destination::with(['category', 'primaryImage', 'interests'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->approved()   // only places an admin approved
            ->featured()
            ->orderBy('name')
            ->limit(6)
            ->get();

        return view('home', [
            'heroRole' => 'visitor',
            'showFeatured' => true,
            'featured' => $featured,
        ]);
    }
}
