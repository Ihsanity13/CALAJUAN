<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One day of a place's opening hours (see the destination_hours migration for the rules).
class DestinationHour extends Model
{
    protected $fillable = ['destination_id', 'day', 'opens_at', 'closes_at'];

    // Day names, using Carbon's numbering (0 = Sunday)
    public const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    // The order the days are shown in: Monday first, Sunday last
    public const WEEK_ORDER = [1, 2, 3, 4, 5, 6, 0];

    protected function casts(): array
    {
        return ['day' => 'integer'];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    // Same opening and closing time = open all day
    public function isAllDay(): bool
    {
        return $this->opensAt() === $this->closesAt();
    }

    // Closing time earlier than opening time = closes after midnight
    public function isOvernight(): bool
    {
        return $this->closesAt() < $this->opensAt();
    }

    // "08:00" (what <input type="time"> uses). PostgreSQL gives "08:00:00".
    public function opensAt(): string
    {
        return substr($this->opens_at, 0, 5);
    }

    public function closesAt(): string
    {
        return substr($this->closes_at, 0, 5);
    }

    // "8:00 AM – 5:00 PM" or "Open 24 hours"
    public function label(): string
    {
        if ($this->isAllDay()) {
            return 'Open 24 hours';
        }

        return self::format($this->opensAt()) . ' – ' . self::format($this->closesAt());
    }

    // "17:30" -> "5:30 PM"
    public static function format(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time)->format('g:i A');
    }

    // Is this day's opening covering the given moment?
    // $moment must be on this row's day (or the next day, for the after-midnight part).
    public function covers(CarbonInterface $moment): bool
    {
        $time = $moment->format('H:i');
        $sameDay = $moment->dayOfWeek === $this->day;

        if ($this->isAllDay()) {
            return $sameDay;
        }

        if ($this->isOvernight()) {
            // e.g. 18:00 – 02:00: open from 18:00 today, or before 02:00 tomorrow
            return $sameDay
                ? $time >= $this->opensAt()
                : $moment->copy()->subDay()->dayOfWeek === $this->day && $time < $this->closesAt();
        }

        return $sameDay && $time >= $this->opensAt() && $time < $this->closesAt();
    }
}
