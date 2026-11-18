<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerVerificationRequest;
use App\Models\Category;
use App\Services\OwnerVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// The owner's own verification: its status, and the form to send details again
// (when they were rejected, or were never sent).
class VerificationController extends Controller
{
    public function __construct(private OwnerVerificationService $verifications) {}

    public function show(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        // Already verified: nothing to do here
        if ($user->isVerifiedOwner()) {
            return redirect()->route('owner.places.index');
        }

        return view('owner.verification', [
            'verification' => $user->ownerVerification,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(OwnerVerificationRequest $request): RedirectResponse
    {
        $verification = $request->user()->ownerVerification;

        // While it's being checked or after approval, the details are locked
        if ($verification && ! $verification->canBeSent()) {
            return redirect()->route('owner.verification.show');
        }

        $this->verifications->submit($request->user(), $request->validated(), $request->file('permit_document'));

        return redirect()->route('owner.verification.show')
            ->with('success', 'Your business details were sent. We\'ll check them soon.');
    }
}
