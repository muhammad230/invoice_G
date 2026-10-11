<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the "Mark as paid" modal. The amount is intentionally NOT
 * accepted from the client — a payment settles the invoice for its full
 * total, which the controller reads straight from the invoice record.
 */
class MarkInvoicePaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return !is_null(auth()->user());
    }

    public function rules(): array
    {
        return [
            'paid_on'   => ['required', 'date', 'before_or_equal:today'],
            'method'    => ['required', 'string', Rule::in(array_keys(Payment::methods()))],
            'reference' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'paid_on' => 'payment date',
        ];
    }
}
