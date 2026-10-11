<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

/**
 * Single source of truth for turning an Invoice into the DomPDF document.
 *
 * Both the "download PDF" route (InvoiceController@download) and the
 * email attachment (InvoiceMail) call this service, so the customer always
 * receives the exact same PDF as the app serves. The blade template and
 * its data contract are unchanged.
 */
class InvoicePdfService
{
    /**
     * Build the data array consumed by resources/views/invoice/pdf.blade.php.
     */
    public function viewData(Invoice $invoice): array
    {
        // Make sure everything the template needs is present, regardless of
        // how the caller obtained the invoice (request binding or queue).
        $invoice->loadMissing(['client', 'items', 'user.businessProfile']);

        $currencyInfo = Invoice::currencies()[$invoice->currency]
            ?? Invoice::currencies()['USD'];

        $itemsForView = $invoice->items->map(function (InvoiceItem $item) {
            $details = trim((string) ($item->details ?? ''));

            return [
                'description' => $item->description,
                'details' => $details === '' ? null : $details,
                'quantity' => $item->quantity,
                'price' => $item->price / 100,
                'line_total' => $item->total / 100,
            ];
        })->all();

        $totals = [
            'subtotal' => $invoice->subtotal / 100,
            'tax' => $invoice->tax_amount / 100,
            'tax_pct' => (float) $invoice->tax_percent,
            'discount' => $invoice->discount / 100,
            'total' => $invoice->total / 100,
        ];

        $profile = $invoice->user?->businessProfile;

        return [
            'business_name' => $invoice->business_name ?? ($profile?->business_name ?? $invoice->user?->name ?? 'Your business'),
            'business_email' => $invoice->business_email ?? ($profile?->email ?? null),
            'business_phone' => $invoice->business_phone ?? ($profile?->phone ?? null),
            'business_address' => $invoice->business_address ?? ($profile?->address ?? null),
            'business_website' => $invoice->business_website ?? ($profile?->website ?? null),
            'business_tagline' => $invoice->business_tagline ?? ($profile?->tagline ?? null),
            'business_tax' => $profile?->tax_number,
            'bank_name' => $invoice->business_bank_name ?? ($profile?->bank_name ?? null),
            'account_title' => $invoice->business_account_title ?? ($profile?->account_title ?? null),
            'account_number' => $invoice->business_account_number ?? ($profile?->account_number ?? null),
            'iban' => $invoice->business_iban ?? ($profile?->iban ?? null),
            'payment_method' => $invoice->business_payment_method ?? ($profile?->payment_method ?? null),
            'signature_name' => $invoice->business_signature_name ?? ($profile?->signature_name ?? null),
            'signature_title' => $invoice->business_signature_title ?? ($profile?->signature_title ?? null),
            'client_name' => optional($invoice->client)->name ?? '—',
            'client_email' => optional($invoice->client)->email,
            'client_phone' => optional($invoice->client)->phone,
            'client_address' => optional($invoice->client)->address,
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date->format('M j, Y'),
            'due_date' => optional($invoice->due_date)?->format('M j, Y'),
            'status' => $invoice->display_status,
            'currency' => $invoice->currency,
            'currency_symbol' => $currencyInfo['symbol'],
            'items' => $itemsForView,
            'totals' => $totals,
            'notes' => $invoice->notes,
            'logo_data' => $invoice->logo_data ?? ($profile?->logo_base64 ?? null),
        ];
    }

    /**
     * Render the invoice blade into a DomPDF instance (A4 portrait).
     */
    public function render(Invoice $invoice): DomPdf
    {
        return Pdf::loadView('invoice.pdf', $this->viewData($invoice))
            ->setPaper('a4', 'portrait');
    }

    /**
     * Canonical attachment filename, e.g. "invoice-INV-001.pdf".
     */
    public function filename(Invoice $invoice): string
    {
        $safeNumber = preg_replace('/[^\w\-]/', '-', $invoice->invoice_number) ?: 'invoice';

        return "invoice-{$safeNumber}.pdf";
    }
}
