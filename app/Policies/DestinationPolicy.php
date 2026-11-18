<?php

namespace App\Policies;

use App\Models\Destination;
use App\Models\User;

// POLICY: who may do what with a destination. Laravel finds this class by its
// name (Destination -> DestinationPolicy) and runs it on Gate::authorize(...).
class DestinationPolicy
{
    // Seeing the public page: approved places are public. A place still waiting
    // (or rejected) is only visible to its owner and to admins, as a preview.
    // ?User: guests (no user) are allowed to call this too.
    public function view(?User $user, Destination $destination): bool
    {
        return $destination->isApproved()
            || ($user && ($user->isAdmin() || $this->owns($user, $destination)));
    }

    // Editing it from the business owner pages: only its own owner
    public function manage(User $user, Destination $destination): bool
    {
        return $this->owns($user, $destination);
    }

    // An owner may delete a place only while it isn't live yet.
    // Removing a live place goes through the admins.
    public function delete(User $user, Destination $destination): bool
    {
        return $this->owns($user, $destination) && ! $destination->isApproved();
    }

    private function owns(User $user, Destination $destination): bool
    {
        return $user->isOwner() && $destination->owner_id === $user->id;
    }
}
