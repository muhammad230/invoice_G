<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return !is_null(auth()->user());
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            // Client selection
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where('user_id', $userId),
            ],

            // Business details (optional, carried through to PDF)
            'business_name'    => ['nullable', 'string', 'max:255'],
            'business_email'   => ['nullable', 'email', 'max:255'],
            'business_phone'   => ['nullable', 'string', 'max:50'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'logo_data'        => ['nullable', 'string'],

            // Invoice header
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')->where('user_id', $userId),
            ],
            'invoice_date' => ['required', 'date'],
            'due_date'     => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'currency'     => ['required', 'string', Rule::in(array_keys(Invoice::currencies()))],
            'status'       => ['nullable', 'string', Rule::in([
                Invoice::STATUS_DRAFT,
                Invoice::STATUS_PENDING,
                Invoice::STATUS_PAID,
            ])],

            // Items — server recalculates totals anyway, but still validate rows.
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.description'   => ['required', 'string', 'max:255'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.price'         => ['required', 'numeric', 'min:0'],

            // Totals & notes (the totals are ignored in favor of server recalc).
            'tax'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes'    => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'client_id' => 'client',
            'items'     => 'line items',
        ];
    }
}
