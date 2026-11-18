<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Interest;
use Illuminate\View\View;

// The admin home page: a few numbers and links to each management page.
class DashboardController extends Controller
{
    public function index(): View
    {
        // One query for all the destination counts: COUNT(*) FILTER (WHERE ...)
        // counts only the matching rows, so we don't run four separate queries.
        $counts = Destination::toBase()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(*) FILTER (WHERE is_featured) AS featured')
            ->selectRaw('COUNT(*) FILTER (WHERE is_hot) AS hot')
            ->selectRaw('COUNT(*) FILTER (WHERE is_new) AS new')
            ->first();

        return view('admin.dashboard', [
            'counts' => $counts,
            'categoryCount' => Category::count(),
            'interestCount' => Interest::count(),
        ]);
    }
}
