<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestinationRequest;
use App\Models\Category;
use App\Models\Destination;

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
        // validated() = only the fields that passed the rules;
        // create() fills them through $fillable (mass assignment).
        Destination::create($request->validated());

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination created.');
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
        $destination->update($request->validated());

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination updated.');
    }

    // DELETE
    public function destroy(Destination $destination)
    {
        // The destination's images, favorites, and reviews are deleted too,
        // because those tables use cascadeOnDelete().
        $destination->delete();

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination deleted.');
    }
}
