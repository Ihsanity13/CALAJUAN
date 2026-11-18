<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OwnerVerification;
use App\Services\OwnerVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Admins checking that a business owner account belongs to a real business.
// (The list of waiting owners is on the Approvals page.)
class OwnerVerificationController extends Controller
{
    public function __construct(private OwnerVerificationService $verifications) {}

    public function show(OwnerVerification $verification): View
    {
        $verification->load(['user', 'reviewer:id,name', 'category']);

        return view('admin.approvals.owner', compact('verification'));
    }

    // The uploaded permit, shown in the browser. It lives on the private disk,
    // so this admin-only route is the ONLY way to open it.
    public function document(OwnerVerification $verification): StreamedResponse
    {
        abort_unless($verification->document_path && Storage::disk('local')->exists($verification->document_path), 404);

        return Storage::disk('local')->response($verification->document_path);
    }

    public function approve(Request $request, OwnerVerification $verification): RedirectResponse
    {
        abort_unless($verification->status === OwnerVerification::STATUS_PENDING, 404);

        $this->verifications->approve($verification, $request->user());

        return redirect()->route('admin.approvals.index')
            ->with('success', $verification->business_name . ' is verified. The owner can now add places.');
    }

    public function reject(Request $request, OwnerVerification $verification): RedirectResponse
    {
        abort_unless($verification->status === OwnerVerification::STATUS_PENDING, 404);

        $reason = $request->validate(
            ['reason' => ['required', 'string', 'max:1000']],
            ['reason.required' => 'Tell the owner why, so they know what to fix.'],
        )['reason'];

        $this->verifications->reject($verification, $request->user(), $reason);

        return redirect()->route('admin.approvals.index')
            ->with('success', $verification->business_name . ' was not verified. The owner can see your reason.');
    }
}
