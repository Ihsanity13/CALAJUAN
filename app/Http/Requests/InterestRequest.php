<?php

namespace App\Http\Requests;

use App\Models\Interest;
use Illuminate\Foundation\Http\FormRequest;

// Validation for the interest form (same rules as categories: a unique name).
class InterestRequest extends FormRequest
{
    // The admin middleware already protects these pages.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // The interest being edited, or null when creating
        $interest = $this->route('interest');

        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:50',
                // Case-insensitive duplicate check ('Nature' = 'nature'),
                // matching the LOWER(name) unique index in the database
                function (string $attribute, mixed $value, \Closure $fail) use ($interest) {
                    $duplicate = Interest::whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                        ->when($interest, fn ($query) => $query->where('id', '!=', $interest->id))
                        ->exists();

                    if ($duplicate) {
                        $fail('An interest with this name already exists.');
                    }
                },
            ],
        ];
    }
}
