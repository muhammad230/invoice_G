<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\InvoiceEmail;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Emails a single invoice (or reminder) to the client.
 *
 * The PDF attached here is produced by the exact same DomPDF view as the
 * "download PDF" route (via InvoicePdfService), so there is one source of
 * truth for the document.
 *
 * Implements ShouldQueue so delivery is pushed onto the queue when one is
 * configured (QUEUE_CONNECTION=database) and runs immediately when the
 * connection is "sync".
 */
class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Invoice  $invoice  The invoice the PDF is built from.
     * @param  InvoiceEmail  $log  The audit row this delivery updates.
     * @param  string  $bodyMessage  The user-written message (plain text).
     * @param  bool  $isReminder  Whether this is a reminder email.
     */
    public function __construct(
        public Invoice $invoice,
        public InvoiceEmail $log,
        public string $bodyMessage,
        public bool $isReminder = false,
    ) {}

    /**
     * Subject + Reply-To. Replies go to the business email so the client's
     * response reaches the user, not the application.
     */
    public function envelope(): Envelope
    {
        $invoice = $this->invoice;
        $business = $this->businessIdentity();

        return new Envelope(
            subject: $this->log->subject,
            replyTo: $business['email'] ? [new Address($business['email'], $business['name'])] : [],
        );
    }

    public function content(): Content
    {
        $invoice = $this->invoice;
        $business = $this->businessIdentity();

        return new Content(
            view: 'emails.invoice',
            with: [
                'greetingName' => optional($invoice->client)->name,
                'bodyMessage' => $this->bodyMessage,
                'isReminder' => $this->isReminder,
                'businessName' => $business['name'],
                'businessEmail' => $business['email'],
                'businessPhone' => $business['phone'],
                'businessWebsite' => $business['website'],
                'invoiceNumber' => $invoice->invoice_number,
                'amount' => $invoice->total_formatted,
                'currency' => $invoice->currency,
                'dueDate' => optional($invoice->due_date)?->format('M j, Y'),
                'status' => ucfirst($invoice->display_status),
            ],
        );
    }

    /**
     * Attach the invoice PDF using the shared service.
     */
    public function attachments(): array
    {
        $pdf = app(InvoicePdfService::class);

        return [
            Attachment::fromData(
                fn () => $pdf->render($this->invoice)->output(),
                $pdf->filename($this->invoice),
            )->withMime('application/pdf'),
        ];
    }

    /**
     * Called by the queued mailable job if delivery ultimately fails, so we
     * can flag the audit row. Internal error text is stored only in the DB.
     */
    public function failed(\Throwable $e): void
    {
        $log = InvoiceEmail::find($this->log->id);

        if ($log) {
            $log->update([
                'status' => InvoiceEmail::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Resolve the business name / email / phone / website with the same
     * fallbacks used across the app (invoice snapshot → profile → user).
     */
    private function businessIdentity(): array
    {
        $invoice = $this->invoice;
        $profile = $invoice->user?->businessProfile;

        return [
            'name' => $invoice->business_name
                ?: ($profile?->business_name ?: ($invoice->user?->name ?: config('app.name'))),
            'email' => $invoice->business_email
                ?: ($profile?->email ?: $invoice->user?->email),
            'phone' => $invoice->business_phone
                ?: $profile?->phone,
            'website' => $invoice->business_website
                ?: $profile?->website,
        ];
    }
}
