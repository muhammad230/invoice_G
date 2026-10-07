<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * InvoiceController handles the creation, live-preview, and PDF download
 * of invoices. No authentication or database required for this version.
 */
class InvoiceController extends Controller
{
    /**
     * Supported currencies with symbol + currency code mapping.
     * Mirrored in the JS for live preview formatting.
     */
    protected $currencies = [
        'USD' => ['symbol' => '$',  'name' => 'US Dollar'],
        'PKR' => ['symbol' => 'Rs', 'name' => 'Pakistani Rupee'],
        'EUR' => ['symbol' => '€',  'name' => 'Euro'],
        'GBP' => ['symbol' => '£',  'name' => 'British Pound'],
    ];

    /**
     * GET /invoice/create
     * Show the blank Create Invoice form (left) + Live Preview (right).
     * Defaults are set in the Blade view (or via old() input after validation).
     */
    public function create(Request $request)
    {
        $currencies = $this->currencies;

        // Defaults for the initial form render.
        $defaults = [
            'invoice_number' => 'INV-001',
            'invoice_date'   => today()->format('Y-m-d'),
            'due_date'       => today()->addDays(30)->format('Y-m-d'),
            'currency'       => 'USD',
            'tax'            => '0',
            'discount'       => '0',
        ];

        return view('invoice.create', compact('currencies', 'defaults'));
    }

    /**
     * POST /invoice/download
     * Validate, recalculate totals (server side — never trust client JS),
     * render the PDF and return it as a download.
     */
    public function download(Request $request)
    {
        // ---- Validation ---------------------------------------------------
        $validated = $request->validate([
            // Your business
            'business_name'    => 'required|string|max:255',
            'business_email'   => 'nullable|email|max:255',
            'business_phone'   => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:500',

            // Client
            'client_name'      => 'required|string|max:255',
            'client_email'     => 'nullable|email|max:255',
            'client_phone'     => 'nullable|string|max:50',
            'client_address'   => 'nullable|string|max:500',

            // Invoice details
            'invoice_number'   => 'required|string|max:50',
            'invoice_date'     => 'required|date',
            'due_date'         => 'nullable|date|after_or_equal:invoice_date',
            'currency'         => 'required|string|in:USD,PKR,EUR,GBP',

            // Items (arrays — at least one, each item validated)
            'items'            => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.price'       => 'required|numeric|min:0',

            // Totals / extras
            'tax'      => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'notes'    => 'nullable|string|max:1000',

            // Logo (base64 data URL, optional — embedded in the form via FileReader)
            'logo_data' => 'nullable|string',
        ]);

        // ---- Sanitize numeric fields (treat nulls as 0) ------------------
        $taxPct   = (float) ($validated['tax']      ?? 0);
        $discount = (float) ($validated['discount'] ?? 0);

        // ---- Recalculate totals on the server (in cents to avoid floats) -
        $subtotalCents = 0;
        $items = [];

        foreach ($validated['items'] as $row) {
            $qty   = (int)    $row['quantity'];
            $price = (float)  $row['price'];

            // price * 100 then round to avoid float drift.
            $priceCents    = (int) round($price * 100);
            $lineTotalCents = $qty * $priceCents;

            $items[] = [
                'description' => $row['description'],
                'quantity'    => $qty,
                'price'       => $price,               // original, for display
                'line_total'  => $lineTotalCents / 100, // decimal, for display
            ];

            $subtotalCents += $lineTotalCents;
        }

        // Tax = subtotal * taxPct / 100
        $taxCents = (int) round(($subtotalCents * $taxPct) / 100);
        $totalBeforeDiscount = $subtotalCents + $taxCents;

        // Discount is a fixed amount — convert to cents then clamp.
        $discountCents = (int) round($discount * 100);
        $totalCents = max(0, $totalBeforeDiscount - $discountCents);

        // Collect totals as display-ready decimals.
        $totals = [
            'subtotal' => $subtotalCents / 100,
            'tax'      => $taxCents      / 100,
            'tax_pct'  => $taxPct,
            'discount' => $discountCents / 100,
            'total'    => $totalCents    / 100,
        ];

        // ---- Currency symbol for display ---------------------------------
        $currency = $validated['currency'];
        $currencyInfo = $this->currencies[$currency] ?? $this->currencies['USD'];
        $currencySymbol = $currencyInfo['symbol'];

        // ---- Logo: if a data URL was uploaded, pass it through (escaped by Blade).
        $logoData = $validated['logo_data'] ?? null;

        // ---- Build view data ---------------------------------------------
        $viewData = array_merge($validated, [
            'items'           => $items,
            'totals'          => $totals,
            'currency'        => $currency,
            'currency_symbol' => $currencySymbol,
            'logo_data'       => $logoData,
        ]);

        // ---- Render & stream PDF (opens inline in the browser) -----------
        // We use DomPDF with DejaVu Sans so currency symbols render correctly.
        $pdf = Pdf::loadView('invoice.pdf', $viewData)
            ->setPaper('a4', 'portrait');

        // Safe filename — strip characters that are illegal in Windows filenames.
        $safeNumber = preg_replace('/[^\w\-]/', '-', $validated['invoice_number']) ?: 'invoice';
        $filename   = 'invoice-' . $safeNumber . '.pdf';

        return $pdf->stream($filename);
    }
}
