<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    // READ (list): show all categories
    public function index()
    {
        // withCount adds a "destinations_count" to each category,
        // so the list can show how many destinations it has.
        $categories = Category::withCount('destinations')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    // CREATE (step 1): show the empty form
    public function create()
    {
        return view('admin.categories.create');
    }

    // CREATE (step 2): save the submitted form
    public function store(CategoryRequest $request)
    {
        // Type-hinting CategoryRequest makes Laravel validate first.
        // validated() returns only the fields that passed the rules,
        // and create() fills them in through $fillable (mass assignment).
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created.');
    }

    // UPDATE (step 1): show the form filled with the current values.
    // Laravel finds the right category from the URL id (route model binding).
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // UPDATE (step 2): save the changes
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated.');
    }

    // DELETE
    public function destroy(Category $category)
    {
        // The database blocks deleting a category that still has destinations
        // (restrictOnDelete). Check first, so the admin sees a friendly
        // message instead of a database error.
        if ($category->destinations()->exists()) {
            return back()->withErrors([
                'category' => 'You cannot delete a category that still has destinations.',
            ]);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }
}