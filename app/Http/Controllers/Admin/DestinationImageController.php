<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationImage;
use App\Services\DestinationPhotoService;
use Illuminate\Http\RedirectResponse;

// Manages the photos of one destination: choose the primary photo, delete a photo.
// (Uploading happens in DestinationController, through the destination form.)
// The actual work is in DestinationPhotoService, shared with the owner pages.
class DestinationImageController extends Controller
{
    public function __construct(private DestinationPhotoService $photos) {}

    // Make this photo the primary one (the cover shown on cards)
    public function makePrimary(Destination $destination, DestinationImage $image): RedirectResponse
    {
        $this->photos->makePrimary($destination, $image);

        return redirect()->route('admin.destinations.edit', $destination)
            ->with('success', 'Primary photo changed.');
    }

    // Delete one photo (and its file). If it was the primary one, the next photo takes over.
    public function destroy(Destination $destination, DestinationImage $image): RedirectResponse
    {
        $this->photos->delete($destination, $image);

        return redirect()->route('admin.destinations.edit', $destination)
            ->with('success', 'Photo deleted.');
    }
}
