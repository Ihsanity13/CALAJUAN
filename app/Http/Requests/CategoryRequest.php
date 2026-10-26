<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// A FORM REQUEST holds the validation rules for one form, so the controller
// stays short. Laravel runs these rules before the controller method starts.
class CategoryRequest extends FormRequest
{
    // Who may submit this form? The route middleware already limits
    // these pages to admins, so we allow everyone who gets this far.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // When editing, this holds the category being edited.
        // When creating, it is null.
        $category = $this->route('category');

        return [
            // unique: no two categories can share a name.
            // ignore($category): when editing, don't count the category
            // itself as a duplicate of its own name.
            'name' => [
                // bail: stop at the first failed rule, so the check below
                // never receives a value that isn't text
                'bail',
                'required',
                'string',
                'max:255',
                // CUSTOM RULE: Rule::unique is case-sensitive in PostgreSQL.
                // This one lowercases both sides before comparing.
                function (string $attribute, mixed $value, \Closure $fail) use ($category) {
                    $duplicate = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                        // when editing, don't count the category as a duplicate of itself
                        ->when($category, fn ($query) => $query->where('id', '!=', $category->id))
                        ->exists();

                    if ($duplicate) {
                        // $fail() marks the field as invalid and sets the error message
                        $fail('A category with this name already exists.');
                    }
                },
            ],
            // nullable: the icon may be left empty
            'icon' => ['nullable', 'string', 'max:50'],
        ];
    }
}
