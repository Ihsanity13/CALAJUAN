<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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