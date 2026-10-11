<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $user = $this->user();

        // Changing the email address is a sensitive action — require the
        // current password to prove it is really the account owner.
        $emailChanged = strcasecmp(
            (string) $this->input('email'),
            (string) $user->email
        ) !== 0;

        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => $emailChanged
                ? ['required', 'string', 'current_password']
                : ['nullable'],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'current password',
        ];
    }
}
