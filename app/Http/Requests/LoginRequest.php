<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// The login rules, shared by the website and the mobile API.
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
