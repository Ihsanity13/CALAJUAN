<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// The registration rules, written ONCE and shared by the website
// and the mobile API (DRY: Don't Repeat Yourself).
class RegisterRequest extends FormRequest
{
    // Anyone may try to register, so allow everyone.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            // unique:users,email = no two accounts can share an email
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            // confirmed = must match a field named password_confirmation
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // visitor = tourist or resident; owner = runs a business in Calauan.
            // Optional, so the mobile app can leave it out (then it's 'visitor').
            // 'admin' is NOT a choice: admins are only made by hand.
            'account_type' => ['nullable', 'in:visitor,owner'],
        ];

        // BUSINESS OWNERS: just the basics about their business here. The permit
        // details come after the account is made (OwnerVerificationRequest).
        // exclude_unless: for visitors these fields are skipped completely.
        foreach (OwnerVerificationRequest::basicRules() as $field => $fieldRules) {
            $rules[$field] = ['exclude_unless:account_type,owner', ...$fieldRules];
        }

        return $rules;
    }

    public function messages(): array
    {
        return OwnerVerificationRequest::businessMessages();
    }
}
