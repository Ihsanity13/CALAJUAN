<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Destination extends Model
{
    // MASS ASSIGNMENT: the columns that can be filled in bulk
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'is_featured',
        'is_verified',
    ];

    // CASTS: convert database values into proper PHP types when reading.
    // Without these, PostgreSQL booleans and decimals may come back as plain strings.
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_verified' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    // QUERY SCOPE: a reusable, named piece of a query.
    // Destination::featured() means 'featured AND verified'.
    // The word 'scope' in the method name is how Laravel recognizes it,
    // and you call it without that word: ->featured()
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->where('is_verified', true);
    }

    // RELATIONSHIP: each destination belongs to one category.
    // $destination->category gives you its category.
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // RELATIONSHIP: one destination has many photos.
    // Sorted with the primary photo first, then in upload order.
    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('id');
    }

    // RELATIONSHIP: the one primary photo (the cover shown on cards), or null.
    public function primaryImage(): HasOne
    {
        return $this->hasOne(DestinationImage::class)->where('is_primary', true);
    }

    // When a destination is deleted, the database removes its photo rows
    // (cascadeOnDelete), but not the files. This deletes its whole photo folder.
    protected static function booted(): void
    {
        static::deleted(function (Destination $destination) {
            Storage::disk('public')->deleteDirectory('destinations/' . $destination->id);
        });
    }

    // RELATIONSHIP: one destination has many reviews.
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // RELATIONSHIP: many users can favorite this destination (many-to-many),
    // stored in the favorites pivot table.
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}
