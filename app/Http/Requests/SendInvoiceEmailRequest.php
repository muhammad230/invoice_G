<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Models\InvoiceEmail;
use App\Rules\NoHeaderInjection;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates and authorizes "send invoice / reminder" submissions.
 *
 * Authorization is checked here with InvoicePolicy@send so a non-owner
 * receives a 403 before any validation or sending happens.
 */
class SendInvoiceEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice instanceof Invoice
            && $this->user() !== null
            && $this->user()->can('send', $invoice);
    }

    /**
     * Normalise the "send me a copy" checkbox to a real boolean so the
     * controller can always call $request->boolean('send_copy').
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'send_copy' => $this->boolean('send_copy'),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:'.InvoiceEmail::TYPE_INVOICE.','.InvoiceEmail::TYPE_REMINDER],
            // Reject CR/LF before the email rule to block header injection.
            'to' => ['required', 'string', 'max:150', new NoHeaderInjection, 'email:rfc'],
            'subject' => ['required', 'string', 'max:150', new NoHeaderInjection],
            'message' => ['required', 'string', 'max:2000'],
            'send_copy' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'to.required' => 'Enter the recipient email address.',
            'to.email' => 'Enter a valid email address for the recipient.',
            'to.max' => 'The recipient email address may not be longer than 150 characters.',
            'subject.required' => 'Enter a subject for the email.',
            'subject.max' => 'The subject may not be longer than 150 characters.',
            'message.max' => 'The message may not be longer than 2000 characters.',
            'type.in' => 'Unknown email type.',
        ];
    }
}
