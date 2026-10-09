<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'business_name'   => ['required', 'string', 'max:255'],
            'tagline'         => ['nullable', 'string', 'max:150'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'website'         => ['nullable', 'string', 'max:255'],
            'address'         => ['nullable', 'string', 'max:500'],
            'tax_number'      => ['nullable', 'string', 'max:100'],
            'bank_name'       => ['nullable', 'string', 'max:150'],
            'account_title'   => ['nullable', 'string', 'max:150'],
            'account_number'  => ['nullable', 'string', 'max:50'],
            'iban'            => ['nullable', 'string', 'max:60'],
            'payment_method'  => ['nullable', 'string', 'max:50'],
            'signature_name'  => ['nullable', 'string', 'max:150'],
            'signature_title' => ['nullable', 'string', 'max:150'],
            'logo'            => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'remove_logo'     => ['nullable', 'boolean'],
        ];
    }
}
