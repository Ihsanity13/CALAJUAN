<?php

namespace App\Http\Requests;

use App\Models\Destination;
use App\Models\DestinationHour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DestinationRequest extends FormRequest
{
    // The admin middleware already protects these pages, so allow everyone who gets here.
    public function authorize(): bool
    {
        return true;
    }

    // prepareForValidation() runs BEFORE the rules are checked.
    // An unticked checkbox sends nothing at all, so without this the fields
    // would be missing from the request. boolean() turns "present" into true
    // and "missing" into false, so the fields always exist.
    protected function prepareForValidation(): void
    {
        $merge = [
            'is_featured' => $this->boolean('is_featured'),
            'is_hot' => $this->boolean('is_hot'),
            'is_new' => $this->boolean('is_new'),
        ];

        // Same for each day's "Open" checkbox in the opening hours
        $hours = $this->input('hours', []);
        if (is_array($hours)) {
            foreach (array_keys(DestinationHour::DAYS) as $day) {
                $hours[$day] = is_array($hours[$day] ?? null) ? $hours[$day] : [];
                $hours[$day]['open'] = filter_var($hours[$day]['open'] ?? false, FILTER_VALIDATE_BOOLEAN);
            }
            $merge['hours'] = $hours;
        }

        // A web address typed without https:// ("facebook.com/calauan") still counts
        foreach (['website', 'facebook'] as $field) {
            $value = trim((string) $this->input($field));
            if ($value !== '' && ! preg_match('#^https?://#i', $value)) {
                $merge[$field] = 'https://' . $value;
            }
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            // exists: the chosen category must be a real row in the categories table
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'address' => ['nullable', 'string', 'max:255'],
            // Valid GPS ranges: latitude -90 to 90, longitude -180 to 180.
            // Calauan is around latitude 14.14 and longitude 121.31.
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // LABELS
            'is_featured' => ['boolean'],
            'is_hot' => ['boolean'],
            'is_new' => ['boolean'],

            // INTERESTS: a list of interest ids, each one a real interest
            'interests' => ['nullable', 'array'],
            'interests.*' => ['integer', 'distinct', 'exists:interests,id'],

            // PRICE
            'price_level' => ['nullable', 'integer', Rule::in(array_keys(Destination::PRICE_LEVELS))],
            'price_note' => ['nullable', 'string', 'max:255'],

            // CONTACT. The phone allows digits, spaces, +, -, ( ) and / (for two numbers).
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s\/]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'facebook' => ['nullable', 'url:http,https', 'max:255'],

            // OPENING HOURS: hours[1][open], hours[1][opens_at], hours[1][closes_at]... per day.
            // The times are only checked (and kept) on the days ticked as open.
            'hours' => ['array'],
            'hours.*.open' => ['boolean'],
            'hours.*.opens_at' => ['exclude_unless:hours.*.open,true', 'required', 'date_format:H:i'],
            'hours.*.closes_at' => ['exclude_unless:hours.*.open,true', 'required', 'date_format:H:i'],

            // PHOTOS: optional. "photos" is the list, "photos.*" is each file in it.
            // mimes = allowed types (checked from the file's content, not just its name);
            // max:4096 = 4096 KB = 4 MB per photo.
            'photos' => ['nullable', 'array', 'max:' . self::MAX_PHOTOS],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    // Most photos one destination may have in total
    public const MAX_PHOTOS = 10;

    // Extra check after the rules above pass: the new photos plus the ones
    // already saved (when editing) must not go over the limit.
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // When editing, the URL contains the destination; when creating, it doesn't
                $destination = $this->route('destination');
                $existing = $destination ? $destination->images()->count() : 0;
                $new = count($this->file('photos', []));

                if ($existing + $new > self::MAX_PHOTOS) {
                    $left = max(0, self::MAX_PHOTOS - $existing);
                    $validator->errors()->add('photos', $left === 0
                        ? 'This place already has ' . self::MAX_PHOTOS . ' photos. Delete one before adding more.'
                        : "This place can have {$left} more " . ($left === 1 ? 'photo' : 'photos') . ' (' . self::MAX_PHOTOS . ' in total).');
                }
            },
        ];
    }

    // The validated columns of the destinations table only
    // (not the photos, interests or hours, which are saved separately).
    public function destinationData(): array
    {
        return $this->safe()->except(['photos', 'interests', 'hours']);
    }

    // The ticked interest ids, e.g. [1, 4]
    public function interestIds(): array
    {
        return array_map('intval', $this->validated('interests') ?? []);
    }

    // Only the open days: [day => ['opens_at' => '08:00', 'closes_at' => '17:00']]
    public function openingHours(): array
    {
        return collect($this->validated('hours') ?? [])
            ->filter(fn (array $day) => $day['open'])
            ->map(fn (array $day) => ['opens_at' => $day['opens_at'], 'closes_at' => $day['closes_at']])
            ->all();
    }

    // Friendlier error messages. The coordinates now come from the map pin,
    // so "The latitude field is required" would confuse the admin.
    public function messages(): array
    {
        return [
            'latitude.required' => 'Drop a pin on the map to set the location.',
            'longitude.required' => 'Drop a pin on the map to set the location.',
            'latitude.*' => 'The pin\'s latitude is not valid. Move the pin and try again.',
            'longitude.*' => 'The pin\'s longitude is not valid. Move the pin and try again.',
            'phone.regex' => 'Use only numbers, spaces, +, -, ( ) and /.',
            'website.url' => 'Enter a full web address, like https://example.com.',
            'facebook.url' => 'Enter the Facebook page address, like facebook.com/yourpage.',
            'price_level.in' => 'Choose one of the price levels.',
            'interests.*.exists' => 'One of the chosen interests no longer exists. Reload the page.',
            'hours.*.opens_at.required' => 'Set an opening time, or untick this day.',
            'hours.*.closes_at.required' => 'Set a closing time, or untick this day.',
            'hours.*.opens_at.date_format' => 'Enter a time like 08:00.',
            'hours.*.closes_at.date_format' => 'Enter a time like 17:00.',
            'photos.max' => 'You can add up to ' . self::MAX_PHOTOS . ' photos per place.',
            'photos.*.mimes' => 'Each photo must be a JPG, PNG or WebP image.',
            'photos.*.max' => 'Each photo must be 4 MB or smaller.',
            'photos.*.file' => 'One of the photos did not upload. Try again.',
            'photos.*.uploaded' => 'One of the photos did not upload. Try again.',
        ];
    }
}
