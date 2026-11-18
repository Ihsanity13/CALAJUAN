<?php

namespace App\Services;

use App\Models\OwnerVerification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

// Business owner verification: sending the business details, and the admin's decision.
// Documents go on the PRIVATE disk ('local' = storage/app/private), never the public one.
class OwnerVerificationService
{
    private const DISK = 'local';

    // STEP 1 (sign-up): save the basics as a draft. Nothing for admins to check yet.
    public function startDraft(User $owner, array $basics): OwnerVerification
    {
        $verification = new OwnerVerification(Arr::only($basics, ['business_name', 'category_id', 'business_phone']));
        $verification->forceFill([
            'user_id' => $owner->id,
            'status' => OwnerVerification::STATUS_DRAFT,
        ])->save();

        $owner->setRelation('ownerVerification', $verification);

        return $verification;
    }

    // STEP 2: send (or send again) the full business details. Always goes to 'pending'.
    // $document may be null when re-sending: the earlier upload is kept.
    public function submit(User $owner, array $data, ?UploadedFile $document): OwnerVerification
    {
        $newPath = $document?->store('owner-documents/' . $owner->id, self::DISK);

        $oldPath = null;

        try {
            $verification = DB::transaction(function () use ($owner, $data, $newPath, &$oldPath) {
                $verification = $owner->ownerVerification ?? new OwnerVerification;
                $oldPath = $verification->document_path;

                $data['registration_number'] = trim($data['registration_number']);
                $verification->fill(Arr::only($data, (new OwnerVerification)->getFillable()));
                $verification->forceFill([
                    'user_id' => $owner->id,
                    'document_path' => $newPath ?? $oldPath,
                    'status' => OwnerVerification::STATUS_PENDING,
                    'rejection_reason' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ])->save();

                return $verification;
            });
        } catch (Throwable $e) {
            // Nothing was saved, so don't leave the new file behind
            if ($newPath) {
                Storage::disk(self::DISK)->delete($newPath);
            }
            throw $e;
        }

        // A new upload replaces the old file (deleted only now that the save worked)
        if ($newPath && $oldPath) {
            Storage::disk(self::DISK)->delete($oldPath);
        }

        $owner->setRelation('ownerVerification', $verification);

        return $verification;
    }

    public function approve(OwnerVerification $verification, User $admin): void
    {
        $verification->forceFill([
            'status' => OwnerVerification::STATUS_APPROVED,
            'rejection_reason' => null,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ])->save();
    }

    // The owner sees the reason and can send corrected details
    public function reject(OwnerVerification $verification, User $admin, string $reason): void
    {
        $verification->forceFill([
            'status' => OwnerVerification::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ])->save();
    }
}
