<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

// Manages the photos of one destination: choose the primary photo, delete a photo.
// (Uploading happens in DestinationController, through the destination form.)
class DestinationImageController extends Controller
{
    // Make this photo the primary one (the cover shown on cards).
    // Exactly one photo per destination is primary, so the old one is unset first.
    public function makePrimary(Destination $destination, DestinationImage $image): RedirectResponse
    {
        // A transaction: both changes happen together, or neither does
        DB::transaction(function () use ($destination, $image) {
            DestinationImage::where('destination_id', $destination->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);
        });

        return redirect()->route('admin.destinations.edit', $destination)
            ->with('success', 'Primary photo changed.');
    }

    // Delete one photo. Its file is removed from storage by the DestinationImage
    // model (see booted() in DestinationImage.php).
    public function destroy(Destination $destination, DestinationImage $image): RedirectResponse
    {
        DB::transaction(function () use ($destination, $image) {
            $wasPrimary = $image->is_primary;

            $image->delete();

            // If we just deleted the primary photo, the next photo takes over,
            // so the place still has a cover photo.
            if ($wasPrimary) {
                $destination->images()->first()?->update(['is_primary' => true]);
            }
        });

        return redirect()->route('admin.destinations.edit', $destination)
            ->with('success', 'Photo deleted.');
    }
}
