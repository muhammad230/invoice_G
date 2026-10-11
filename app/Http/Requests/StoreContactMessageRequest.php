<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * Anyone (guest or signed-in) may send a contact message.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:2', 'max:120'],
            'email'   => ['required', 'string', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            // The honeypot field (see isSpam()) is deliberately not validated
            // here so it never surfaces an error to a real user and never ends
            // up in validated() output.
        ];
    }

    public function attributes(): array
    {
        return [
            'name'    => 'name',
            'email'   => 'email address',
            'message' => 'message',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Please tell us your name.',
            'email.required'   => 'We need an email address to reply to you.',
            'email.email'      => 'That email address does not look valid.',
            'message.required' => 'Please write a message.',
            'message.min'      => 'Please add a little more detail (at least 10 characters).',
        ];
    }

    /**
     * True when the honeypot field has been filled (almost certainly a bot).
     * Real users never see this field; it is hidden with CSS.
     */
    public function isSpam(): bool
    {
        return filled($this->input('website'));
    }
}
