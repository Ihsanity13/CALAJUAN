<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestinationRequest;
use App\Models\Category;
use App\Models\Destination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class DestinationController extends Controller
{
    // READ (list): show all destinations
    public function index()
    {
        // with('category') loads each destination's category in one extra query,
        // instead of one query per row. This avoids the "N+1 query problem".
        $destinations = Destination::with('category')->orderBy('name')->get();

        return view('admin.destinations.index', compact('destinations'));
    }

    // CREATE (step 1): show the empty form.
    // The form needs the list of categories for its dropdown.
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.destinations.create', compact('categories'));
    }

    // CREATE (step 2): validate and save
    public function store(DestinationRequest $request)
    {
        // safe()->except('photos') = the validated fields, minus the photo files
        // (those aren't a column; they're saved separately below).
        // create() fills them through $fillable (mass assignment).
        $destination = Destination::create($request->safe()->except('photos'));

        $count = $this->storePhotos($destination, $request->file('photos', []));

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination created' . $this->photoNote($count) . '.');
    }

    // UPDATE (step 1): show the form with the current values
    public function edit(Destination $destination)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.destinations.edit', compact('destination', 'categories'));
    }

    // UPDATE (step 2): validate and save the changes
    public function update(DestinationRequest $request, Destination $destination)
    {
        $destination->update($request->safe()->except('photos'));

        $count = $this->storePhotos($destination, $request->file('photos', []));

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination updated' . $this->photoNote($count) . '.');
    }

    // DELETE
    public function destroy(Destination $destination)
    {
        // The destination's images, favorites, and reviews are deleted too,
        // because those tables use cascadeOnDelete(). Its photo files are
        // deleted by the Destination model (see booted() in Destination.php).
        $destination->delete();

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination deleted.');
    }

    // Save uploaded photos to storage/app/public/destinations/{id}/ and add
    // a destination_images row for each. Returns how many were saved.
    // If the destination has no primary photo yet, the first new one becomes primary,
    // so there is always exactly one primary once a place has photos.
    private function storePhotos(Destination $destination, array $files): int
    {
        if (empty($files)) {
            return 0;
        }

        $storedPaths = [];

        try {
            // A transaction: either ALL the photo rows are saved, or none are
            DB::transaction(function () use ($destination, $files, &$storedPaths) {
                $needsPrimary = ! $destination->images()->where('is_primary', true)->exists();

                foreach ($files as $file) {
                    // store() picks a random, unique file name, so two uploads
                    // called "photo.jpg" never overwrite each other
                    $path = $file->store('destinations/' . $destination->id, 'public');
                    $storedPaths[] = $path;

                    $destination->images()->create([
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

    // " with 3 photos" for the success message, or nothing
    private function photoNote(int $count): string
    {
        return $count > 0 ? " with {$count} new " . Str::plural('photo', $count) : '';
    }
}
