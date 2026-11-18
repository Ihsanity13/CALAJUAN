<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
