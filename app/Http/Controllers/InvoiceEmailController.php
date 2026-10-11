<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendInvoiceEmailRequest;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\InvoiceEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

/**
 * Handles emailing an invoice (or a payment reminder) to the client.
 *
 * Authorization is enforced in SendInvoiceEmailRequest via InvoicePolicy@send
 * (non-owners get a 403). Everything else here is scoped to the owner.
 */
class InvoiceEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * POST /invoices/{invoice}/send
     *
     * Logs the attempt, then sends the mailable. Because InvoiceMail is
     * queueable, delivery is pushed onto the queue when one is configured
     * (so the page never waits); with QUEUE_CONNECTION=sync it sends inline.
     */
    public function send(SendInvoiceEmailRequest $request, Invoice $invoice): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $type = $validated['type'];
        $isReminder = $type === InvoiceEmail::TYPE_REMINDER;

        // Paid invoices are done — refuse with a friendly message.
        if ($invoice->status === Invoice::STATUS_PAID) {
            return back()->with(
                'error',
                "Invoice <strong>{$invoice->invoice_number}</strong> is already paid, so there is nothing to send."
            );
        }

        // Record the attempt up-front as "sent". If a queued delivery
        // ultimately fails, InvoiceMail@failed flips this row to "failed".
        $log = $invoice->emails()->create([
            'user_id' => $user->id,
            'to_email' => $validated['to'],
            'subject' => $validated['subject'],
            'type' => $type,
            'status' => InvoiceEmail::STATUS_SENT,
            'sent_at' => Date::now(),
        ]);

        // Load everything the mailable + PDF attachment need.
        $invoice->loadMissing(['client', 'items', 'user.businessProfile']);

        try {
            Mail::to($validated['to'])->send(
                new InvoiceMail($invoice, $log, $validated['message'], $isReminder)
            );

            // Optional courtesy copy to the signed-in user.
            if ($validated['send_copy'] ?? false) {
                Mail::to($user->email)->send(
                    new InvoiceMail($invoice, $log, $validated['message'], $isReminder)
                );
            }
        } catch (\Throwable $e) {
            // Sync delivery failed. Store the technical detail, tell the
            // user only that it did not go through.
            $log->update([
                'status' => InvoiceEmail::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);

            report($e);

            return back()->with(
                'error',
                'We could not send the email. Please try again in a moment.'
            );
        }

        // Stamp the invoice the first time it is ever emailed.
        if ($invoice->sent_at === null) {
            $invoice->forceFill(['sent_at' => Date::now()])->save();
        }

        $label = $isReminder ? 'Reminder' : 'Invoice';

        session()->flash(
            'success',
            "{$label} for <strong>{$invoice->invoice_number}</strong> sent to <strong>{$validated['to']}</strong>."
        );

        return back();
    }
}
