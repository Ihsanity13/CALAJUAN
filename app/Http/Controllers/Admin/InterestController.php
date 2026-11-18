<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InterestRequest;
use App\Models\Interest;

// Admin CRUD for interests (Nature, Food, Adventure...), the same pattern as categories.
class InterestController extends Controller
{
    // READ (list), with how many places match each interest
    public function index()
    {
        $interests = Interest::withCount('destinations')->orderBy('name')->get();

        return view('admin.interests.index', compact('interests'));
    }

    public function create()
    {
        return view('admin.interests.create');
    }

    public function store(InterestRequest $request)
    {
        Interest::create($request->validated());

        return redirect()->route('admin.interests.index')
            ->with('success', 'Interest created.');
    }

    public function edit(Interest $interest)
    {
        return view('admin.interests.edit', compact('interest'));
    }

    public function update(InterestRequest $request, Interest $interest)
    {
        $interest->update($request->validated());

        return redirect()->route('admin.interests.index')
            ->with('success', 'Interest updated.');
    }

    // Unlike a category, an interest can be deleted while places use it:
    // the database just removes it from those places (cascadeOnDelete on the pivot).
    // The confirm message on the list page says so.
    public function destroy(Interest $interest)
    {
        $interest->delete();

        return redirect()->route('admin.interests.index')
            ->with('success', 'Interest deleted.');
    }
}
