<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    // MASS ASSIGNMENT: columns that may be filled in bulk.
    // Rule to remember: only ever pass validated data into create()/update(),
    // never the raw request, so nobody can sneak in a different user_id.
    protected $fillable = ['user_id', 'destination_id', 'rating', 'comment'];

    // RELATIONSHIP: each review was written by one user.
    // $review->user gives you the author.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // RELATIONSHIP: each review is about one destination.
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}