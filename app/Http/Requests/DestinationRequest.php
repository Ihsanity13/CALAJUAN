<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_verified' => $this->boolean('is_verified'),
        ]);
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
            'is_featured' => ['boolean'],
            'is_verified' => ['boolean'],

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

    // Friendlier error messages. The coordinates now come from the map pin,
    // so "The latitude field is required" would confuse the admin.
    public function messages(): array
    {
        return [
            'latitude.required' => 'Drop a pin on the map to set the location.',
            'longitude.required' => 'Drop a pin on the map to set the location.',
            'latitude.*' => 'The pin\'s latitude is not valid. Move the pin and try again.',
            'longitude.*' => 'The pin\'s longitude is not valid. Move the pin and try again.',
            'photos.max' => 'You can add up to ' . self::MAX_PHOTOS . ' photos per place.',
            'photos.*.mimes' => 'Each photo must be a JPG, PNG or WebP image.',
            'photos.*.max' => 'Each photo must be 4 MB or smaller.',
            'photos.*.file' => 'One of the photos did not upload. Try again.',
            'photos.*.uploaded' => 'One of the photos did not upload. Try again.',
        ];
    }
}
