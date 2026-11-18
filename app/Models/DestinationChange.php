<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// A CHANGE REQUEST: an owner's proposed new version of a live place,
// waiting for an admin to approve or reject it.
//
// payload = [
//   'fields'    => ['name' => ..., 'phone' => ..., ...],   (destinations columns)
//   'interests' => [1, 4],                                  (interest ids)
//   'hours'     => [1 => ['opens_at' => '08:00', 'closes_at' => '17:00'], ...],
// ]
class DestinationChange extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    // The destinations columns an owner may change. The labels
    // (featured / hot / new) and the approval columns are admin-only.
    public const OWNER_FIELDS = [
        'category_id', 'name', 'description', 'address', 'latitude', 'longitude',
        'phone', 'email', 'website', 'facebook', 'price_level', 'price_note',
    ];

    // How each field is named on the review page
    public const FIELD_LABELS = [
        'category_id' => 'Category',
        'name' => 'Name',
        'description' => 'Description',
        'address' => 'Address',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'phone' => 'Phone',
        'email' => 'Email',
        'website' => 'Website',
        'facebook' => 'Facebook page',
        'price_level' => 'Price level',
        'price_note' => 'Price details',
    ];

    protected $fillable = ['destination_id', 'user_id', 'payload'];

    protected function casts(): array
    {
        return [
            // 'array': stored as JSON, read back as a PHP array
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    // The owner who submitted it
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Photos uploaded with this change (hidden from the public until approved)
    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class)->orderBy('id');
    }

    // The proposed interest ids
    public function interestIds(): array
    {
        return array_map('intval', $this->payload['interests'] ?? []);
    }

    // The proposed hours, with day numbers as integers (JSON turns keys into strings)
    public function hours(): array
    {
        $hours = [];
        foreach ($this->payload['hours'] ?? [] as $day => $times) {
            $hours[(int) $day] = $times;
        }
        ksort($hours);

        return $hours;
    }

    // WHAT CHANGED, for the admin's review page:
    // a list of ['label' => 'Phone', 'current' => '0917...', 'proposed' => '0918...'],
    // only for the things that are different. Values are already turned into words.
    public function differences(): array
    {
        $destination = $this->destination->loadMissing(['category', 'interests', 'hours']);
        $rows = [];

        foreach (self::OWNER_FIELDS as $field) {
            if (! array_key_exists($field, $this->payload['fields'] ?? [])) {
                continue;
            }

            $current = $this->describe($field, $destination->$field);
            $proposed = $this->describe($field, $this->payload['fields'][$field]);

            if ($current !== $proposed) {
                $rows[] = ['label' => self::FIELD_LABELS[$field], 'current' => $current, 'proposed' => $proposed];
            }
        }

        // Interests, as a sorted list of names
        $currentInterests = $destination->interests->pluck('name')->sort()->join(', ');
        $proposedInterests = Interest::whereIn('id', $this->interestIds())->orderBy('name')->pluck('name')->join(', ');
        if ($currentInterests !== $proposedInterests) {
            $rows[] = ['label' => 'Interests', 'current' => $currentInterests ?: '—', 'proposed' => $proposedInterests ?: '—'];
        }

        // Opening hours, one line per day
        $currentHours = $this->describeHours($destination->hours->mapWithKeys(
            fn (DestinationHour $hour) => [$hour->day => ['opens_at' => $hour->opensAt(), 'closes_at' => $hour->closesAt()]]
        )->all());
        $proposedHours = $this->describeHours($this->hours());
        if ($currentHours !== $proposedHours) {
            $rows[] = ['label' => 'Opening hours', 'current' => $currentHours, 'proposed' => $proposedHours];
        }

        return $rows;
    }

    // One value, in words: category name, "₱₱ · Mid-range", or "—" when empty
    private function describe(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($field) {
            'category_id' => Category::find($value)?->name ?? '(deleted category)',
            'price_level' => (function () use ($value) {
                $price = Destination::PRICE_LEVELS[(int) $value] ?? null;

                return $price ? ($price['symbol'] === $price['label'] ? $price['label'] : $price['symbol'] . ' · ' . $price['label']) : '—';
            })(),
            // Compare coordinates at the same precision as the database (7 decimals)
            'latitude', 'longitude' => number_format((float) $value, 7, '.', ''),
            default => (string) $value,
        };
    }

    // "Monday: 8:00 AM – 5:00 PM" per line, Monday first; "Not listed" when empty
    private function describeHours(array $hours): string
    {
        if ($hours === []) {
            return 'Not listed';
        }

        $lines = [];
        foreach (DestinationHour::WEEK_ORDER as $day) {
            $times = $hours[$day] ?? null;
            $text = 'Closed';
            if ($times) {
                $text = $times['opens_at'] === $times['closes_at']
                    ? 'Open 24 hours'
                    : DestinationHour::format($times['opens_at']) . ' – ' . DestinationHour::format($times['closes_at']);
            }
            $lines[] = DestinationHour::DAYS[$day] . ': ' . $text;
        }

        return implode("\n", $lines);
    }
}
