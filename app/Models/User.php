<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// MASS ASSIGNMENT: only these columns can be filled in bulk.
// 'role' is deliberately NOT here. If it were, anyone registering could send
// role=admin in the form and make themselves an admin.
#[Fillable(['name', 'email', 'password'])]
// HIDDEN: these columns are left out whenever a user is converted to JSON,
// so passwords never leak through the API.
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    // CASTS: convert values when reading/writing.
    // 'hashed' automatically encrypts the password whenever it's set.
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // HELPER: true if this user is an admin. Used later by the role check
    // on admin-only routes: if ($user->isAdmin()) { ... }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // The page each role starts on after logging in:
    // admins -> dashboard, business owners -> My places, visitors -> home
    public function homeUrl(): string
    {
        return match (true) {
            $this->isAdmin() => route('admin.dashboard'),
            $this->isOwner() => route('owner.places.index'),
            default => route('home'),
        };
    }

    // How many submissions wait for an admin: owners to verify + new places + changes.
    // Shown on the dashboard and as a badge on the Approvals menu link.
    public static function pendingApprovalCount(): int
    {
        return OwnerVerification::where('status', OwnerVerification::STATUS_PENDING)->count()
            + Destination::where('status', Destination::STATUS_PENDING)->count()
            + DestinationChange::where('status', DestinationChange::STATUS_PENDING)->count();
    }

    // HELPER: true if this user is a business owner (manages their own places)
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    // HELPER: an owner whose business an admin has checked and approved.
    // Only verified owners may add or edit places.
    public function isVerifiedOwner(): bool
    {
        return $this->isOwner() && $this->ownerVerification?->isApproved() === true;
    }

    // RELATIONSHIP: the business details this owner sent for checking (or null)
    public function ownerVerification(): HasOne
    {
        return $this->hasOne(OwnerVerification::class);
    }

    // RELATIONSHIP: the places this business owner manages
    public function ownedDestinations(): HasMany
    {
        return $this->hasMany(Destination::class, 'owner_id');
    }

    // RELATIONSHIP: destinations this user saved (many-to-many via favorites).
    // $user->favorites gives you the list.
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'favorites')->withTimestamps();
    }

    // RELATIONSHIP: categories this user likes (many-to-many via user_preferences).
    // The recommendation logic will read this.
    public function preferredCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_preferences')->withTimestamps();
    }

    // RELATIONSHIP: reviews this user wrote (one user, many reviews).
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
