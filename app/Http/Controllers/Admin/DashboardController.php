<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Interest;
use App\Models\User;
use Illuminate\View\View;

// The admin home page: a few numbers and links to each management page.
class DashboardController extends Controller
{
    public function index(): View
    {
        // One query for all the destination counts: COUNT(*) FILTER (WHERE ...)
        // counts only the matching rows, so we don't run four separate queries.
        $counts = Destination::toBase()
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'approved') AS total")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'pending') AS pending")
            ->selectRaw("COUNT(*) FILTER (WHERE is_featured AND status = 'approved') AS featured")
            ->selectRaw("COUNT(*) FILTER (WHERE is_hot AND status = 'approved') AS hot")
            ->selectRaw("COUNT(*) FILTER (WHERE is_new AND status = 'approved') AS new")
            ->first();

        return view('admin.dashboard', [
            'counts' => $counts,
            'categoryCount' => Category::count(),
            'interestCount' => Interest::count(),
            // Waiting for a decision: new places + changes to live places + owners to verify
            'approvalCount' => User::pendingApprovalCount(),
        ]);
    }
}
