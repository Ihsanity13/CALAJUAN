<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestinationRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Interest;
use App\Services\DestinationPhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Admins add and edit places directly: their changes go live right away.
// (Business owners' submissions go through the approval queue instead,
// see Admin\ApprovalController.)
class DestinationController extends Controller
{
    // The photo logic is shared with the owner pages (app/Services).
    // Laravel creates the service and passes it in ("dependency injection").
    public function __construct(private DestinationPhotoService $photos) {}

    // READ (list): show all destinations
    // ?show=featured (or live / hot / new / pending / rejected) narrows the list;
    // the dashboard's numbers link here. An unknown value just shows everything.
    public function index(Request $request)
    {
        $filter = array_key_exists($request->query('show'), Destination::LIST_FILTERS) ? $request->query('show') : null;

        // with(...) loads each destination's category and owner in one extra query each,
        // instead of one query per row. This avoids the "N+1 query problem".
        // withExists adds pending_change_exists = true/false for the "Changes waiting" badge.
        $destinations = Destination::with(['category', 'owner:id,name'])
            ->withExists('pendingChange')
            ->listFilter($filter)
            ->orderBy('name')
            ->get();

        return view('admin.destinations.index', compact('destinations', 'filter'));
    }

    // CREATE (step 1): show the empty form.
    // The form needs the categories (dropdown) and interests (checkboxes).
    public function create()
    {
        return view('admin.destinations.create', $this->formOptions());
    }

    // CREATE (step 2): validate and save
    public function store(DestinationRequest $request)
    {
        // destinationData() = the validated columns of the destinations table
        // (photos, interests and hours aren't columns; they're saved separately).
        // create() fills them through $fillable (mass assignment).
        // A place an admin adds is approved straight away (the column's default).
        // The transaction keeps the place, its interests and its hours together:
        // if one part fails, none of them is saved.
        $destination = DB::transaction(function () use ($request) {
            $destination = Destination::create($request->destinationData());
            $this->saveDetails($destination, $request);

            return $destination;
        });

        $count = $this->photos->store($destination, $request->file('photos', []));

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination created' . $this->photoNote($count) . '.');
    }

    // UPDATE (step 1): show the form with the current values
    public function edit(Destination $destination)
    {
        $destination->load(['interests', 'hours', 'owner:id,name', 'pendingChange']);

        return view('admin.destinations.edit', ['destination' => $destination] + $this->formOptions());
    }

    // UPDATE (step 2): validate and save the changes
    public function update(DestinationRequest $request, Destination $destination)
    {
        DB::transaction(function () use ($request, $destination) {
            $destination->update($request->destinationData());
            $this->saveDetails($destination, $request);
        });

        $count = $this->photos->store($destination, $request->file('photos', []));

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination updated' . $this->photoNote($count) . '.');
    }

    // DELETE
    public function destroy(Destination $destination)
    {
        // The destination's images, favorites, reviews and change requests are
        // deleted too, because those tables use cascadeOnDelete(). Its photo files
        // are deleted by the Destination model (see booted() in Destination.php).
        $destination->delete();

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination deleted.');
    }

    // The lists the form's dropdown and checkboxes need
    private function formOptions(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'interests' => Interest::orderBy('name')->get(),
        ];
    }

    // Interests and opening hours live in their own tables
    private function saveDetails(Destination $destination, DestinationRequest $request): void
    {
        // sync(): keep exactly these interests (adds the ticked ones, removes the unticked ones)
        $destination->interests()->sync($request->interestIds());
        $destination->syncHours($request->openingHours());
    }

    // " with 3 photos" for the success message, or nothing
    private function photoNote(int $count): string
    {
        return $count > 0 ? " with {$count} new " . Str::plural('photo', $count) : '';
    }
}
