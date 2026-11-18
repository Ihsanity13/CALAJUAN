<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationImage;
use App\Services\DestinationPhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

// An owner choosing the cover photo or deleting a photo of their own place.
// These take effect right away: they only rearrange or remove photos an admin
// already approved, so there's nothing new to check.
// (New photos always go through approval, via the place form.)
class PlaceImageController extends Controller
{
    public function __construct(private DestinationPhotoService $photos) {}

    public function makePrimary(Destination $destination, DestinationImage $image): RedirectResponse
    {
        Gate::authorize('manage', $destination);

        $this->photos->makePrimary($destination, $image);

        return redirect()->route('owner.places.edit', $destination)
            ->with('success', 'Cover photo changed.');
    }

    public function destroy(Destination $destination, DestinationImage $image): RedirectResponse
    {
        Gate::authorize('manage', $destination);

        $this->photos->delete($destination, $image);

        return redirect()->route('owner.places.edit', $destination)
            ->with('success', 'Photo deleted.');
    }
}
