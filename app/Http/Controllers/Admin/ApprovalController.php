<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationChange;
use App\Models\OwnerVerification;
use App\Services\DestinationModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// THE APPROVAL QUEUE: new places and changes submitted by business owners.
// The rules themselves are in DestinationModerationService.
class ApprovalController extends Controller
{
    public function __construct(private DestinationModerationService $moderation) {}

    // The tabs on the Approvals page (?tab=owners ...). 'all' shows every section.
    public const TABS = ['all', 'owners', 'places', 'changes'];

    // Everything waiting, oldest first (first come, first served)
    public function index(Request $request): View
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'all';

        // Business owner accounts waiting to be verified
        $owners = OwnerVerification::where('status', OwnerVerification::STATUS_PENDING)
            ->with('user:id,name,email')
            ->orderBy('updated_at')
            ->get();

        $places = Destination::where('status', Destination::STATUS_PENDING)
            ->with(['category', 'owner:id,name,email', 'primaryImage'])
            ->orderBy('updated_at')
            ->get();

        $changes = DestinationChange::where('status', DestinationChange::STATUS_PENDING)
            ->with(['destination.primaryImage', 'user:id,name,email', 'images'])
            ->orderBy('created_at')
            ->get();

        // "Name, Phone, Opening hours": what each change touches, for its card
        $changes->each(function (DestinationChange $change) {
            $change->changed_labels = array_column($change->differences(), 'label');
        });

        return view('admin.approvals.index', [
            'tab' => $tab,
            'owners' => $owners,
            'places' => $places,
            'changes' => $changes,
            'recent' => $this->recentDecisions(),
        ]);
    }

    // The last few approve/reject decisions of all three kinds, newest first:
    // [['kind' => 'Place', 'title' => ..., 'status' => 'approved', 'by' => 'Admin', 'at' => Carbon, 'url' => ...], ...]
    private function recentDecisions(int $limit = 8): array
    {
        // (a rejected place that was sent again is pending once more, so it's left out)
        $places = Destination::whereNotNull('reviewed_at')
            ->whereIn('status', [Destination::STATUS_APPROVED, Destination::STATUS_REJECTED])
            ->with('reviewer:id,name')->latest('reviewed_at')->limit($limit)->get()
            ->map(fn (Destination $d) => [
                'kind' => 'New place', 'title' => $d->name, 'status' => $d->status,
                'by' => $d->reviewer?->name, 'at' => $d->reviewed_at,
                'url' => route('admin.approvals.places.show', $d),
            ]);

        $changes = DestinationChange::where('status', '!=', DestinationChange::STATUS_PENDING)
            ->with(['reviewer:id,name', 'destination:id,name'])->latest('reviewed_at')->limit($limit)->get()
            ->map(fn (DestinationChange $c) => [
                'kind' => 'Changes', 'title' => $c->destination->name, 'status' => $c->status,
                'by' => $c->reviewer?->name, 'at' => $c->reviewed_at,
                'url' => route('admin.approvals.changes.show', $c),
            ]);

        $owners = OwnerVerification::where('status', '!=', OwnerVerification::STATUS_PENDING)
            ->whereNotNull('reviewed_at')
            ->with('reviewer:id,name')->latest('reviewed_at')->limit($limit)->get()
            ->map(fn (OwnerVerification $v) => [
                'kind' => 'Business owner', 'title' => $v->business_name, 'status' => $v->status,
                'by' => $v->reviewer?->name, 'at' => $v->reviewed_at,
                'url' => route('admin.approvals.owners.show', $v),
            ]);

        return $places->concat($changes)->concat($owners)
            ->filter(fn (array $row) => $row['at'] !== null)
            ->sortByDesc('at')
            ->take($limit)
            ->values()
            ->all();
    }

    // ---- NEW PLACES ----

    public function showPlace(Destination $destination): View
    {
        $destination->load(['category', 'owner:id,name,email', 'interests', 'hours', 'images']);

        return view('admin.approvals.place', compact('destination'));
    }

    public function approvePlace(Request $request, Destination $destination): RedirectResponse
    {
        abort_unless($destination->status === Destination::STATUS_PENDING, 404);

        $this->moderation->approvePlace($destination, $request->user());

        return redirect()->route('admin.approvals.index')
            ->with('success', '"' . $destination->name . '" is approved and now visible to everyone.');
    }

    public function rejectPlace(Request $request, Destination $destination): RedirectResponse
    {
        abort_unless($destination->status === Destination::STATUS_PENDING, 404);

        $reason = $this->validatedReason($request);

        $this->moderation->rejectPlace($destination, $request->user(), $reason);

        return redirect()->route('admin.approvals.index')
            ->with('success', '"' . $destination->name . '" was rejected. The owner can see your reason.');
    }

    // ---- CHANGES TO LIVE PLACES ----

    public function showChange(DestinationChange $change): View
    {
        $change->load(['destination', 'user:id,name,email', 'images']);

        return view('admin.approvals.change', [
            'change' => $change,
            'differences' => $change->differences(),
        ]);
    }

    public function approveChange(Request $request, DestinationChange $change): RedirectResponse
    {
        abort_unless($change->status === DestinationChange::STATUS_PENDING, 404);

        $this->moderation->approveChange($change, $request->user());

        return redirect()->route('admin.approvals.index')
            ->with('success', 'The changes to "' . $change->destination->name . '" are approved and live.');
    }

    public function rejectChange(Request $request, DestinationChange $change): RedirectResponse
    {
        abort_unless($change->status === DestinationChange::STATUS_PENDING, 404);

        $reason = $this->validatedReason($request);

        $this->moderation->rejectChange($change, $request->user(), $reason);

        return redirect()->route('admin.approvals.index')
            ->with('success', 'The changes to "' . $change->destination->name . '" were rejected. The owner can see your reason.');
    }

    // A rejection always needs a reason, so the owner knows what to fix
    private function validatedReason(Request $request): string
    {
        return $request->validate(
            ['reason' => ['required', 'string', 'max:1000']],
            ['reason.required' => 'Tell the owner why, so they know what to fix.'],
        )['reason'];
    }
}
