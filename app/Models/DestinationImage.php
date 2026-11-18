<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DestinationImage extends Model
{
    // Laravel automatically maps this class to the "destination_images" table
    // (class name -> snake_case -> plural). No extra setup needed.

    // MASS ASSIGNMENT: columns that may be filled in bulk
    // destination_change_id: set while the photo waits in a change request (hidden from the public)
    protected $fillable = ['destination_id', 'image_path', 'is_primary', 'destination_change_id'];

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

    // $image->url gives the web address of the photo, for <img src="...">.
    // asset() builds it from the address the visitor is using, so it works on
    // 127.0.0.1 AND through the https cloudflared tunnel.
    // (Storage::url() would use APP_URL from .env, which is only right on one of them.)
    // Files live in storage/app/public; "php artisan storage:link" makes them
    // reachable at /storage/...
    protected function url(): Attribute
    {
        return Attribute::get(fn () => asset('storage/' . $this->image_path));
    }

    // When a photo row is deleted, delete its file from storage too,
    // so no orphaned files pile up.
    protected static function booted(): void
    {
        static::deleted(function (DestinationImage $image) {
            Storage::disk('public')->delete($image->image_path);
        });
    }
}
