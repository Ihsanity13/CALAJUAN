<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    // MASS ASSIGNMENT: only these columns may be filled in bulk,
    // e.g. Category::create($request->all()). Anything not listed here
    // (like id) is ignored, which protects against users sneaking in extra fields.
    protected $fillable = ['name', 'icon'];

    // RELATIONSHIP: one category has many destinations.
    // Lets you write $category->destinations to get them all.
    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    // RELATIONSHIP: many users can like this category (many-to-many),
    // stored in the user_preferences pivot table.
    // withTimestamps() fills created_at/updated_at on the pivot rows.
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_preferences')->withTimestamps();
    }
}