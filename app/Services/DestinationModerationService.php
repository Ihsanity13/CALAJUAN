<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Destination;
use App\Models\DestinationChange;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

// THE APPROVAL RULES, in one place (used by the owner pages, the admin
// approval pages, and later the mobile API):
//
// 1. An owner's NEW place is saved with status 'pending'. Nobody but the owner
//    and the admins can see it until an admin approves it.
// 2. An owner editing a place that ISN'T live yet (pending or rejected) changes
//    it directly, and it goes back to 'pending' for another review.
// 3. An owner editing a LIVE place creates a change request instead. The public
//    keeps seeing the approved version until an admin approves the change.
class DestinationModerationService
{
    public function __construct(private DestinationPhotoService $photos) {}

    // Rule 1. $fields = the destinations columns, $hours = only the open days.
    /** @param UploadedFile[] $files */
    public function submitNew(User $owner, array $fields, array $interestIds, array $hours, array $files): Destination
    {
        return DB::transaction(function () use ($owner, $fields, $interestIds, $hours, $files) {
            $destination = new Destination(Arr::only($fields, DestinationChange::OWNER_FIELDS));
            // forceFill: these columns are not fillable on purpose (owners can't set them from a form)
            $destination->forceFill([
                'owner_id' => $owner->id,
                'status' => Destination::STATUS_PENDING,
            ])->save();

            $destination->interests()->sync($interestIds);
            $destination->syncHours($hours);
            $this->photos->store($destination, $files);

            return $destination;
        });
    }

    // Rules 2 and 3. Returns 'resubmitted' (rule 2) or 'change' (rule 3),
    // so the page can show the right message.
    /** @param UploadedFile[] $files */
    public function submitUpdate(Destination $destination, User $owner, array $fields, array $interestIds, array $hours, array $files): string
    {
        $fields = Arr::only($fields, DestinationChange::OWNER_FIELDS);

        return DB::transaction(function () use ($destination, $owner, $fields, $interestIds, $hours, $files) {
            if (! $destination->isApproved()) {
                // Rule 2: not public yet, so change it directly and send it back for review
                $destination->fill($fields)->forceFill([
                    'status' => Destination::STATUS_PENDING,
                    'rejection_reason' => null,
                ])->save();
                $destination->interests()->sync($interestIds);
                $destination->syncHours($hours);
                $this->photos->store($destination, $files);

                return 'resubmitted';
            }

            // Rule 3: save (or replace) the one pending change request
            $change = $destination->pendingChange()->first() ?? new DestinationChange([
                'destination_id' => $destination->id,
            ]);
            $change->fill([
                'user_id' => $owner->id,
                'payload' => ['fields' => $fields, 'interests' => $interestIds, 'hours' => $hours],
            ]);
            // Re-submitting moves it to the back of the queue with a fresh date
            $change->created_at = now();
            $change->save();

            $this->photos->store($destination, $files, $change);

            return 'change';
        });
    }

    // The owner takes back their pending change (its new photos are deleted too)
    public function withdrawChange(DestinationChange $change): void
    {
        DB::transaction(function () use ($change) {
            $this->deleteChangePhotos($change);
            $change->delete();
        });
    }

    // ---------------------------------------------------------------
    // ADMIN DECISIONS
    // ---------------------------------------------------------------

    public function approvePlace(Destination $destination, User $admin): void
    {
        $destination->forceFill([
            'status' => Destination::STATUS_APPROVED,
            'rejection_reason' => null,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ])->save();
    }

    // A rejected place stays hidden. The owner sees the reason, can fix it and resubmit.
    public function rejectPlace(Destination $destination, User $admin, string $reason): void
    {
        $destination->forceFill([
            'status' => Destination::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ])->save();
    }

    // Copy the proposed version onto the live place, and make its photos live
    public function approveChange(DestinationChange $change, User $admin): void
    {
        DB::transaction(function () use ($change, $admin) {
            $destination = $change->destination;
            $fields = Arr::only($change->payload['fields'] ?? [], DestinationChange::OWNER_FIELDS);

            // Things may have been deleted while the change waited:
            // keep the current category if the proposed one is gone,
            // and skip interests that no longer exist.
            if (isset($fields['category_id']) && ! Category::whereKey($fields['category_id'])->exists()) {
                unset($fields['category_id']);
            }
            $interestIds = Interest::whereIn('id', $change->interestIds())->pluck('id')->all();

            $destination->update($fields);
            $destination->interests()->sync($interestIds);
            $destination->syncHours($change->hours());

            // The waiting photos become live photos
            $change->images()->update(['destination_change_id' => null]);
            $this->photos->ensurePrimary($destination);

            $change->forceFill([
                'status' => DestinationChange::STATUS_APPROVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ])->save();
        });
    }

    // The live place stays as it is. The change is kept (with the reason) so the
    // owner can see why; its photos are deleted.
    public function rejectChange(DestinationChange $change, User $admin, string $reason): void
    {
        DB::transaction(function () use ($change, $admin, $reason) {
            $this->deleteChangePhotos($change);

            $change->forceFill([
                'status' => DestinationChange::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ])->save();
        });
    }

    // One by one, so each photo's file is deleted too (DestinationImage::booted())
    private function deleteChangePhotos(DestinationChange $change): void
    {
        $change->images()->get()->each->delete();
    }
}
