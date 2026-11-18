<?php

namespace App\Http\Requests;

use App\Models\OwnerVerification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Business owners prove their business is real in TWO steps:
//   1. Sign-up: the basics only (basicRules(), used by RegisterRequest)
//   2. After the account exists: the permit details (this request, on /business/verification)
class OwnerVerificationRequest extends FormRequest
{
    // The route only lets owners in (role:owner)
    public function authorize(): bool
    {
        return true;
    }

    // STEP 2: the basics again (they can correct them) + the permit details
    public function rules(): array
    {
        $current = $this->user()->ownerVerification;
        $userId = $this->user()->id;

        return self::basicRules() + [
            'business_address' => ['required', 'string', 'max:255'],
            'applicant_role' => ['required', Rule::in(array_keys(OwnerVerification::APPLICANT_ROLES))],
            'registration_type' => ['required', Rule::in(array_keys(OwnerVerification::REGISTRATION_TYPES))],
            'registration_number' => [
                'bail', 'required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-\/ ]+$/',
                // Nobody else may already claim this registration number
                // (a rejected claim doesn't count). Matches the unique index in the database.
                function (string $attribute, mixed $value, \Closure $fail) use ($userId) {
                    $taken = OwnerVerification::where('registration_type', $this->input('registration_type'))
                        ->whereRaw('LOWER(registration_number) = ?', [mb_strtolower(trim($value))])
                        ->where('status', '!=', OwnerVerification::STATUS_REJECTED)
                        ->where('user_id', '!=', $userId)
                        ->exists();

                    if ($taken) {
                        $fail('This registration number is already used by another account. If this is your business, contact the Calajuan team.');
                    }
                },
            ],
            // A photo or scan of the permit/certificate. Kept private (admins only).
            // Required unless one was already uploaded (when sending again after a rejection).
            'permit_document' => [$current?->document_path ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            // A ticked "this is true" box: a false claim is then a clear choice, not a mistake
            'confirm_legit' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return self::businessMessages();
    }

    // STEP 1 (sign-up): just the basics about the business
    public static function basicRules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            // Type of business = one of the categories (Café, Restaurant, Resort...)
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'business_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s\/]+$/'],
        ];
    }

    public static function businessMessages(): array
    {
        return [
            'category_id.required' => 'Choose the type of business.',
            'category_id.exists' => 'Choose one of the listed types.',
            'business_phone.regex' => 'Use only numbers, spaces, +, -, ( ) and /.',
            'registration_number.regex' => 'Use only letters, numbers, spaces, - and /.',
            'applicant_role.in' => 'Choose your role in the business.',
            'registration_type.in' => 'Choose the kind of registration you have.',
            'permit_document.required' => 'Upload a photo or scan of your permit or certificate.',
            'permit_document.mimes' => 'The document must be a JPG, PNG, WebP or PDF file.',
            'permit_document.max' => 'The document must be 5 MB or smaller.',
            'permit_document.uploaded' => 'The document did not upload. Try again.',
            'confirm_legit.accepted' => 'Please confirm that the information is true.',
        ];
    }
}
