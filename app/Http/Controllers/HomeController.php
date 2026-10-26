<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\View\View;

class HomeController extends Controller
{
    // Show the home page with the featured places.
    // The controller only asks the model for data and picks the view.
    // The 'how to find featured places' logic lives in the Destination model.
    public function index(): View
    {
        // with('category') loads the category names in one extra query
        // instead of one query per row (the N+1 problem).
        $featured = Destination::with('category')
            ->featured()
            ->orderBy('name')
            ->limit(6)
            ->get();

        // compact('featured') sends the $featured list to the view
        return view('home', compact('featured'));
    }
}
