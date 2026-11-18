<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\DestinationChange;
use App\Models\DestinationImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

// Saving, choosing and deleting a destination's photos.
// Used by the admin pages and the business owner pages.
class DestinationPhotoService
{
    // Save uploaded photos to storage/app/public/destinations/{id}/ and add
    // a destination_images row for each. Returns how many were saved.
    //
    // With a $change, the photos wait in that change request (hidden from the
    // public, never primary) until an admin approves it.
    // Without one, they're live right away, and if the place has no primary
    // photo yet the first new one becomes primary.
    /** @param UploadedFile[] $files */
    public function store(Destination $destination, array $files, ?DestinationChange $change = null): int
    {
        if (empty($files)) {
            return 0;
        }

        $storedPaths = [];

        try {
            // A transaction: either ALL the photo rows are saved, or none are
            DB::transaction(function () use ($destination, $files, $change, &$storedPaths) {
                $needsPrimary = ! $change && ! $destination->primaryImage()->exists();

                foreach ($files as $file) {
                    // store() picks a random, unique file name, so two uploads
                    // called "photo.jpg" never overwrite each other
                    $path = $file->store('destinations/' . $destination->id, 'public');
                    $storedPaths[] = $path;

                    DestinationImage::create([
                        'destination_id' => $destination->id,
                        'destination_change_id' => $change?->id,
                        'image_path' => $path,
                        'is_primary' => $needsPrimary,
                    ]);

                    $needsPrimary = false;   // only the first one
                }
            });
        } catch (Throwable $e) {
            // The rows were rolled back, so remove the files we already wrote
            Storage::disk('public')->delete($storedPaths);
            throw $e;
        }

        return count($files);
    }

    // Make this photo the primary one (the cover shown on cards).
    // Exactly one photo per destination is primary, so the old one is unset first.
    public function makePrimary(Destination $destination, DestinationImage $image): void
    {
        DB::transaction(function () use ($destination, $image) {
            DestinationImage::where('destination_id', $destination->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);
        });
    }

    // Delete one photo. Its file is removed by the DestinationImage model (booted()).
    // If it was the primary photo, the next live photo takes over.
    public function delete(Destination $destination, DestinationImage $image): void
    {
        DB::transaction(function () use ($destination, $image) {
            $wasPrimary = $image->is_primary;

            $image->delete();

            if ($wasPrimary) {
                $destination->images()->first()?->update(['is_primary' => true]);
            }
        });
    }

    // Make sure a place with live photos has a primary one
    // (used after the photos of an approved change go live)
    public function ensurePrimary(Destination $destination): void
    {
        if (! $destination->primaryImage()->exists()) {
            $destination->images()->first()?->update(['is_primary' => true]);
        }
    }
}
