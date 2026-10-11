<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'current_password'],
            // Explicit opt-in: the user must tick the confirmation checkbox.
            'confirm'  => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm.accepted' => 'Please confirm that you want to permanently delete your account.',
        ];
    }

    public function attributes(): array
    {
        return [
            'confirm' => 'confirmation',
        ];
    }
}
