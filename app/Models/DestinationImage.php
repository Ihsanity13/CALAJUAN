<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationImage extends Model
{
    // Laravel automatically maps this class to the "destination_images" table
    // (class name -> snake_case -> plural). No extra setup needed.

    // MASS ASSIGNMENT: columns that may be filled in bulk
    protected $fillable = ['destination_id', 'image_path', 'is_primary'];

    // CASTS: read is_primary as a real true/false instead of 1/0 or "t"/"f"
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    // RELATIONSHIP: each image belongs to one destination.
    // $image->destination gives you the destination it's attached to.
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
