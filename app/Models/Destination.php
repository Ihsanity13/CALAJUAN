<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Destination extends Model
{
    // APPROVAL STATUS: owners' new places start as pending; only approved ones are public
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    // Calauan's time zone, for "Open now". The app itself runs in UTC.
    public const TIMEZONE = 'Asia/Manila';

    // PRICE LEVELS: the number is saved (so the budget filter can compare them),
    // the symbol and words are shown.
    public const PRICE_LEVELS = [
        0 => ['symbol' => 'Free', 'label' => 'Free'],
        1 => ['symbol' => '₱', 'label' => 'Budget-friendly'],
        2 => ['symbol' => '₱₱', 'label' => 'Mid-range'],
        3 => ['symbol' => '₱₱₱', 'label' => 'Premium'],
    ];

    // MASS ASSIGNMENT: the columns that can be filled in bulk
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'is_featured',
        'is_hot',
        'is_new',
        'phone',
        'email',
        'website',
        'facebook',
        'price_level',
        'price_note',
    ];

    // CASTS: convert database values into proper PHP types when reading.
    // Without these, PostgreSQL booleans and decimals may come back as plain strings.
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_hot' => 'boolean',
            'is_new' => 'boolean',
            'price_level' => 'integer',
            'reviewed_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    // QUERY SCOPE: only the places the public may see (approved by an admin).
    // Every public list must use this: Destination::approved()->...
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    // ADMIN LIST FILTERS: ?show=featured etc. on the destinations list.
    // Featured / Hot / New count live places only (the same as the dashboard numbers).
    public const LIST_FILTERS = [
        'live' => 'Live',
        'featured' => '⭐ Featured',
        'hot' => '🔥 Hot',
        'new' => '✨ New',
        'pending' => 'Waiting for approval',
        'rejected' => 'Not approved',
    ];

    public function scopeListFilter(Builder $query, ?string $filter): Builder
    {
        return match ($filter) {
            'live' => $query->approved(),
            'featured' => $query->approved()->where('is_featured', true),
            'hot' => $query->approved()->where('is_hot', true),
            'new' => $query->approved()->where('is_new', true),
            'pending' => $query->where('status', self::STATUS_PENDING),
            'rejected' => $query->where('status', self::STATUS_REJECTED),
            default => $query,   // no filter: everything
        };
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    // QUERY SCOPE: a reusable, named piece of a query.
    // Destination::featured() = the places the admin marked as featured.
    // The word 'scope' in the method name is how Laravel recognizes it,
    // and you call it without that word: ->featured()
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    // RELATIONSHIP: each destination belongs to one category.
    // $destination->category gives you its category.
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // RELATIONSHIP: a destination matches many interests (Nature, Food...), many-to-many.
    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class)->orderBy('name');
    }

    // RELATIONSHIP: the opening hours, one row per open day.
    public function hours(): HasMany
    {
        return $this->hasMany(DestinationHour::class)->orderBy('day');
    }

    // RELATIONSHIP: the LIVE photos (not the ones waiting in a change request).
    // Sorted with the primary photo first, then in upload order.
    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class)
            ->whereNull('destination_change_id')
            ->orderByDesc('is_primary')
            ->orderBy('id');
    }

    // RELATIONSHIP: the one primary photo (the cover shown on cards), or null.
    public function primaryImage(): HasOne
    {
        return $this->hasOne(DestinationImage::class)
            ->whereNull('destination_change_id')
            ->where('is_primary', true);
    }

    // RELATIONSHIP: the business owner who manages this place (null = admins only)
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // RELATIONSHIP: the admin who last approved or rejected this place
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // RELATIONSHIP: every change request ever submitted for this place
    public function changes(): HasMany
    {
        return $this->hasMany(DestinationChange::class);
    }

    // RELATIONSHIP: the change waiting for approval, or null (there's at most one)
    public function pendingChange(): HasOne
    {
        return $this->hasOne(DestinationChange::class)->where('status', DestinationChange::STATUS_PENDING);
    }

    // RELATIONSHIP: the most recent change request, whatever its status
    // (the owner's list uses it to show "Changes rejected: reason")
    public function latestChange(): HasOne
    {
        return $this->hasOne(DestinationChange::class)->latestOfMany();
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

    // ---------------------------------------------------------------
    // HELPERS used by the pages (and later by the API)
    // ---------------------------------------------------------------

    // ['symbol' => '₱₱', 'label' => 'Mid-range'], or null when no price is set
    public function price(): ?array
    {
        return self::PRICE_LEVELS[$this->price_level] ?? null;
    }

    // Replace all opening hours with the given ones.
    // $hours: [day number => ['opens_at' => '08:00', 'closes_at' => '17:00']], only the open days.
    // Runs in a transaction, so a failure never leaves a half-saved week.
    public function syncHours(array $hours): void
    {
        DB::transaction(function () use ($hours) {
            $this->hours()->delete();

            foreach ($hours as $day => $times) {
                $this->hours()->create([
                    'day' => $day,
                    'opens_at' => $times['opens_at'],
                    'closes_at' => $times['closes_at'],
                ]);
            }
        });

        $this->unsetRelation('hours');
    }

    // Open right now? true / false, or null when the hours aren't listed.
    // Checks today's hours, and yesterday's in case they run past midnight.
    public function isOpenAt(?CarbonInterface $moment = null): ?bool
    {
        if ($this->hours->isEmpty()) {
            return null;
        }

        $moment ??= now(self::TIMEZONE);
        $yesterday = $moment->copy()->subDay()->dayOfWeek;

        return $this->hours
            ->filter(fn (DestinationHour $hour) => in_array($hour->day, [$moment->dayOfWeek, $yesterday], true))
            ->contains(fn (DestinationHour $hour) => $hour->covers($moment));
    }
}
