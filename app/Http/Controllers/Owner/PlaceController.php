<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestinationRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\DestinationHour;
use App\Models\Interest;
use App\Services\DestinationModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// BUSINESS OWNER PORTAL: an owner's own places.
// Everything they submit waits for an admin (see DestinationModerationService).
class PlaceController extends Controller
{
    public function __construct(private DestinationModerationService $moderation) {}

    // My places, with their approval status
    public function index(Request $request): View
    {
        $places = $request->user()->ownedDestinations()
            ->with(['category', 'primaryImage', 'pendingChange', 'latestChange'])
            ->orderBy('name')
            ->get();

        return view('owner.places.index', compact('places'));
    }

    public function create(): View
    {
        return view('owner.places.create', $this->formOptions());
    }

    // A new place starts as 'pending'
    public function store(DestinationRequest $request): RedirectResponse
    {
        $this->moderation->submitNew(
            $request->user(),
            $request->destinationData(),
            $request->interestIds(),
            $request->openingHours(),
            $request->file('photos', []),
        );

        return redirect()->route('owner.places.index')
            ->with('success', 'Thanks! Your place was sent to the Calajuan team. It will appear on the site once it\'s approved.');
    }

    public function edit(Destination $destination): View
    {
        // Gate::authorize runs DestinationPolicy::manage(). Not your place = 403.
        Gate::authorize('manage', $destination);

        $destination->load(['interests', 'hours', 'images', 'pendingChange.images', 'latestChange']);
        $pendingChange = $destination->pendingChange;

        // If changes are already waiting, show THOSE in the form (not the live version),
        // so saving again builds on what the owner submitted last time.
        $formDestination = $pendingChange ? $this->draftFrom($destination, $pendingChange->payload) : $destination;

        return view('owner.places.edit', [
            'destination' => $formDestination,
            'live' => $destination,
            'pendingChange' => $pendingChange,
        ] + $this->formOptions());
    }

    public function update(DestinationRequest $request, Destination $destination): RedirectResponse
    {
        Gate::authorize('manage', $destination);

        $result = $this->moderation->submitUpdate(
            $destination,
            $request->user(),
            $request->destinationData(),
            $request->interestIds(),
            $request->openingHours(),
            $request->file('photos', []),
        );

        $message = $result === 'change'
            ? 'Your changes were sent for approval. Visitors keep seeing the current version until they\'re approved.'
            : 'Saved and sent for approval again.';

        return redirect()->route('owner.places.index')->with('success', $message);
    }

    // Take back the changes waiting for approval
    public function withdrawChange(Destination $destination): RedirectResponse
    {
        Gate::authorize('manage', $destination);

        if ($change = $destination->pendingChange) {
            $this->moderation->withdrawChange($change);
        }

        return redirect()->route('owner.places.edit', $destination)
            ->with('success', 'Your pending changes were withdrawn.');
    }

    // Only places that aren't live yet (see DestinationPolicy::delete)
    public function destroy(Destination $destination): RedirectResponse
    {
        Gate::authorize('delete', $destination);

        $destination->delete();

        return redirect()->route('owner.places.index')
            ->with('success', 'Your submission was deleted.');
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'interests' => Interest::orderBy('name')->get(),
        ];
    }

    // A copy of the place with the pending change's values filled in (never saved).
    // The form reads it like a normal destination.
    private function draftFrom(Destination $destination, array $payload): Destination
    {
        $draft = clone $destination;
        $draft->fill($payload['fields'] ?? []);

        $draft->setRelation('interests', Interest::whereIn('id', $payload['interests'] ?? [])->get());
        $draft->setRelation('hours', collect($payload['hours'] ?? [])->map(
            fn (array $times, $day) => new DestinationHour(['day' => (int) $day] + $times)
        )->values());

        return $draft;
    }
}
