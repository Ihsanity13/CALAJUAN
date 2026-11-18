<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// An INTEREST is what a place is good for: Nature, Food, Adventure, Relaxation...
// (a place has one category, but can match many interests).
// Visitors see them as "Experiences", each as a colored pill.
class Interest extends Model
{
    // The pill colors an admin can choose (each has a style in app.css: .interest-green ...)
    public const COLORS = [
        'green' => 'Green',
        'orange' => 'Orange',
        'red' => 'Red',
        'blue' => 'Blue',
        'teal' => 'Teal',
        'brown' => 'Brown',
        'purple' => 'Purple',
        'pink' => 'Pink',
        'gold' => 'Gold',
        'gray' => 'Gray',
    ];

    protected $fillable = ['name', 'color'];

    // RELATIONSHIP: many destinations match this interest (many-to-many),
    // stored in the destination_interest pivot table.
    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class);
    }
}
