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
        return [
            'name' => ['required', 'string', 'max:255'],
            // unique:users,email = no two accounts can share an email
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            // confirmed = must match a field named password_confirmation
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
