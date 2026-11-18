<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// An INTEREST is what a place is good for: Nature, Food, Adventure, Relaxation...
// (a place has one category, but can match many interests)
class Interest extends Model
{
    protected $fillable = ['name'];

    // RELATIONSHIP: many destinations match this interest (many-to-many),
    // stored in the destination_interest pivot table.
    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class);
    }
}
