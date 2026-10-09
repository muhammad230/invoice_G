<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct()
    {
        // Auth required for all persistence + PDF routes. Guests can still
        // reach /invoice/create for the demo form on the landing page.
        $this->middleware('auth')->except(['create']);
    }

    /**
     * Currency metadata (symbol + display name).
     */
    protected function currencies(): array
    {
        return Invoice::currencies();
    }

    /**
     * Take a validated form body, recalculate all monetary totals in cents,
     * and return a structured array with both raw cents (for DB) and the
     * item rows prepared for DB insert.
     */
    protected function recalcTotals(array $validated): array
    {
        $taxPct   = (float) ($validated['tax']      ?? 0);
        $discount = (float) ($validated['discount'] ?? 0);

        $subtotalCents = 0;
        $itemRows = [];

        foreach ($validated['items'] as $row) {
            $qty          = (int)   $row['quantity'];
            $priceCents   = (int)   round(((float) $row['price']) * 100);
            $lineCents    = $qty * $priceCents;

            // Optional short second line under the description.
            $details      = trim((string) ($row['details'] ?? ''));
            $details      = $details === '' ? null : $details;

            $itemRows[] = [
                'description' => $row['description'],
                'details'     => $details,
                'quantity'    => $qty,
                'price'       => $priceCents,
                'total'       => $lineCents,
            ];

            $subtotalCents += $lineCents;
        }

        $taxCents          = (int) round(($subtotalCents * $taxPct) / 100);
        $totalBeforeDisc   = $subtotalCents + $taxCents;
        $discountCents     = (int) round($discount * 100);
        $totalCents        = max(0, $totalBeforeDisc - $discountCents);

        return [
            'subtotal'      => $subtotalCents,
            'tax_percent'   => round($taxPct, 2),
            'tax_amount'    => $taxCents,
            'discount'      => $discountCents,
            'total'         => $totalCents,
            'items'         => $itemRows,
        ];
    }

    // ------------------------------------------------------------------
    // CRUD (Authenticated)
    // ------------------------------------------------------------------

    /**
     * GET /invoices — list, search, filter, paginate.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Invoice::where('user_id', $user->id)
            ->with(['client', 'items']);

        // Search by invoice number OR client name.
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($subq) use ($q) {
                $subq->where('invoice_number', 'like', '%' . $q . '%')
                     ->orWhereHas('client', fn ($c) => $c->where('name', 'like', '%' . $q . '%'));
            });
        }

        // Filter by status. "overdue" is synthetic (pending + due_date < today).
        $status = (string) $request->input('status', '');
        if (in_array($status, ['draft', 'pending', 'paid'], true)) {
            $query->where('status', $status);
        } elseif ($status === 'overdue') {
            $query->overdue();
        }

        $query->orderByDesc('invoice_date')->orderByDesc('id');

        $invoices = $query->paginate(10)->withQueryString();

        return view('invoices.index', [
            'invoices'    => $invoices,
            'filters'     => [
                'q'      => $q,
                'status' => $status,
            ],
        ]);
    }

    /**
     * GET /invoice/create OR GET /invoices/create
     * - Guests: legacy demo form (just to avoid broken landing-page links).
     * - Authed: builder with business profile prefill + client/service dropdowns.
     */
    public function create(): View
    {
        if (!Auth()->check()) {
            // Guest preview — no profile prefill, no saved clients/services.
            $currencies = $this->currencies();
            $defaults = [
                'invoice_number' => 'INV-001',
                'invoice_date'   => Date::today()->format('Y-m-d'),
                'due_date'       => Date::today()->addDays(30)->format('Y-m-d'),
                'currency'       => 'USD',
                'tax'            => '0',
                'discount'       => '0',
                'status'         => Invoice::STATUS_PENDING,
            ];
            $clients   = collect();
            $products  = collect();
            $businessDefaults = [
                'business_name'             => '',
                'business_email'            => '',
                'business_phone'            => '',
                'business_address'          => '',
                'business_website'          => '',
                'business_tagline'          => '',
                'business_bank_name'        => '',
                'business_account_title'    => '',
                'business_account_number'   => '',
                'business_iban'             => '',
                'business_payment_method'   => '',
                'business_signature_name'   => '',
                'business_signature_title'  => '',
                'logo_default'              => null,
            ];
            $invoice    = null;
            $oldItems   = [];

            return view('invoice.create', array_merge(
                compact('currencies', 'clients', 'products', 'defaults', 'invoice', 'oldItems'),
                $businessDefaults
            ));
        }

        $user = auth()->user();
        $user->load('businessProfile');

        $currencies = $this->currencies();
        $clients    = Client::where('user_id', $user->id)->orderBy('name')->get(['id', 'name', 'email']);
        $products   = Product::where('user_id', $user->id)->orderBy('name')->get(['id', 'name', 'description', 'price']);

        $defaults = [
            'invoice_number' => $user->nextInvoiceNumber(),
            'invoice_date'   => Date::today()->format('Y-m-d'),
            'due_date'       => Date::today()->addDays(30)->format('Y-m-d'),
            'currency'       => 'USD',
            'tax'            => '0',
            'discount'       => '0',
            'status'         => Invoice::STATUS_PENDING,
        ];

        // Prefill "Your business" / payment blocks from BusinessProfile.
        $profile = $user->businessProfile;
        $businessDefaults = [
            'business_name'            => $profile?->business_name   ?? $user->name,
            'business_email'           => $profile?->email           ?? $user->email,
            'business_phone'           => $profile?->phone           ?? '',
            'business_address'         => $profile?->address         ?? '',
            'business_website'         => $profile?->website         ?? '',
            'business_tagline'         => $profile?->tagline         ?? '',
            'business_bank_name'       => $profile?->bank_name       ?? '',
            'business_account_title'   => $profile?->account_title   ?? '',
            'business_account_number'  => $profile?->account_number  ?? '',
            'business_iban'            => $profile?->iban            ?? '',
            'business_payment_method'  => $profile?->payment_method  ?? '',
            'business_signature_name'  => $profile?->signature_name  ?? '',
            'business_signature_title' => $profile?->signature_title ?? '',
            'logo_default'             => $profile?->logo_base64     ?? null,
        ];

        $invoice = null;
        $oldItems = [];

        return view('invoice.create', array_merge(
            compact('currencies', 'clients', 'products', 'defaults', 'invoice', 'oldItems'),
            $businessDefaults
        ));
    }

    /**
     * POST /invoices — validate, recalc, persist in transaction, redirect.
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $totals    = $this->recalcTotals($validated);
        $user      = auth()->user();

        DB::beginTransaction();
        try {
            /** @var Invoice $invoice */
            $invoice = Invoice::create([
                'user_id'         => $user->id,
                'client_id'       => (int) $validated['client_id'],
                'invoice_number'  => $validated['invoice_number'],
                'invoice_date'    => $validated['invoice_date'],
                'due_date'        => $validated['due_date'] ?? null,
                'currency'        => $validated['currency'],
                'status'          => $validated['status'] ?? Invoice::STATUS_PENDING,
                'subtotal'        => $totals['subtotal'],
                'tax_percent'     => $totals['tax_percent'],
                'tax_amount'      => $totals['tax_amount'],
                'discount'        => $totals['discount'],
                'total'           => $totals['total'],
                'business_name'    => $validated['business_name']    ?? null,
                'business_email'   => $validated['business_email']   ?? null,
                'business_phone'   => $validated['business_phone']   ?? null,
                'business_address' => $validated['business_address'] ?? null,
                'business_website'         => $validated['business_website']         ?? null,
                'business_tagline'         => $validated['business_tagline']         ?? null,
                'business_bank_name'       => $validated['business_bank_name']       ?? null,
                'business_account_title'   => $validated['business_account_title']   ?? null,
                'business_account_number'  => $validated['business_account_number']  ?? null,
                'business_iban'            => $validated['business_iban']            ?? null,
                'business_payment_method'  => $validated['business_payment_method']  ?? null,
                'business_signature_name'  => $validated['business_signature_name']  ?? null,
                'business_signature_title' => $validated['business_signature_title'] ?? null,
                'logo_data'        => isset($validated['logo_data']) && $validated['logo_data'] !== ''
                    ? $validated['logo_data']
                    : null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            $itemRows = [];
            $now      = Date::now();
            foreach ($totals['items'] as $row) {
                $itemRows[] = [
                    'invoice_id'  => $invoice->id,
                    'description' => $row['description'],
                    'details'     => $row['details'],
                    'quantity'    => $row['quantity'],
                    'price'       => $row['price'],
                    'total'       => $row['total'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            DB::table('invoice_items')->insert($itemRows);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        session()->flash('success', "Invoice <strong>{$invoice->invoice_number}</strong> saved.");

        return redirect()->route('invoices.show', $invoice);
    }

    /**
     * GET /invoices/{invoice} — full invoice view + action buttons.
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $invoice->load(['client', 'items']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * GET /invoices/{invoice}/edit — builder pre-filled.
     */
    public function edit(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);
        $invoice->load(['client', 'items']);

        $user = auth()->user();
        $user->load('businessProfile');

        $currencies = $this->currencies();
        $clients    = Client::where('user_id', $user->id)->orderBy('name')->get(['id', 'name', 'email']);
        $products   = Product::where('user_id', $user->id)->orderBy('name')->get(['id', 'name', 'description', 'price']);

        $defaults = [
            'invoice_number' => $invoice->invoice_number,
            'invoice_date'   => $invoice->invoice_date->format('Y-m-d'),
            'due_date'       => optional($invoice->due_date)->format('Y-m-d'),
            'currency'       => $invoice->currency,
            'tax'            => (string) $invoice->tax_percent,
            'discount'       => number_format($invoice->discount / 100, 2, '.', ''),
            'status'         => $invoice->status,
        ];

        $oldItems = $invoice->items->map(function (InvoiceItem $i) {
            return [
                'description' => $i->description,
                'details'     => (string) ($i->details ?? ''),
                'quantity'    => $i->quantity,
                'price'       => number_format($i->price / 100, 2, '.', ''),
            ];
        })->all();

        // Edit mode keeps any business info that was explicitly written onto
        // the invoice. If the invoice has NO business info saved (e.g. legacy)
        // fall back to the BusinessProfile defaults so the user sees something.
        $profile = $user->businessProfile;
        $businessDefaults = [
            'business_name'            => $invoice->business_name   ?? ($profile?->business_name   ?? $user->name),
            'business_email'           => $invoice->business_email  ?? ($profile?->email           ?? $user->email),
            'business_phone'           => $invoice->business_phone  ?? ($profile?->phone           ?? ''),
            'business_address'         => $invoice->business_address ?? ($profile?->address        ?? ''),
            'business_website'         => $invoice->business_website ?? ($profile?->website        ?? ''),
            'business_tagline'         => $invoice->business_tagline ?? ($profile?->tagline        ?? ''),
            'business_bank_name'       => $invoice->business_bank_name ?? ($profile?->bank_name     ?? ''),
            'business_account_title'   => $invoice->business_account_title ?? ($profile?->account_title ?? ''),
            'business_account_number'  => $invoice->business_account_number ?? ($profile?->account_number ?? ''),
            'business_iban'            => $invoice->business_iban ?? ($profile?->iban               ?? ''),
            'business_payment_method'  => $invoice->business_payment_method ?? ($profile?->payment_method ?? ''),
            'business_signature_name'  => $invoice->business_signature_name ?? ($profile?->signature_name ?? ''),
            'business_signature_title' => $invoice->business_signature_title ?? ($profile?->signature_title ?? ''),
            'logo_default'             => $invoice->logo_data      ?? ($profile?->logo_base64       ?? null),
        ];

        return view('invoice.create', array_merge(
            compact('currencies', 'clients', 'products', 'defaults', 'invoice', 'oldItems'),
            $businessDefaults
        ));
    }

    /**
     * PUT /invoices/{invoice} — update + replace items.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validated();
        $totals    = $this->recalcTotals($validated);

        DB::beginTransaction();
        try {
            $invoice->update([
                'client_id'       => (int) $validated['client_id'],
                'invoice_number'  => $validated['invoice_number'],
                'invoice_date'    => $validated['invoice_date'],
                'due_date'        => $validated['due_date'] ?? null,
                'currency'        => $validated['currency'],
                'status'          => $validated['status'] ?? $invoice->status,
                'subtotal'        => $totals['subtotal'],
                'tax_percent'     => $totals['tax_percent'],
                'tax_amount'      => $totals['tax_amount'],
                'discount'        => $totals['discount'],
                'total'           => $totals['total'],
                'business_name'    => $validated['business_name']    ?? null,
                'business_email'   => $validated['business_email']   ?? null,
                'business_phone'   => $validated['business_phone']   ?? null,
                'business_address' => $validated['business_address'] ?? null,
                'business_website'         => $validated['business_website']         ?? null,
                'business_tagline'         => $validated['business_tagline']         ?? null,
                'business_bank_name'       => $validated['business_bank_name']       ?? null,
                'business_account_title'   => $validated['business_account_title']   ?? null,
                'business_account_number'  => $validated['business_account_number']  ?? null,
                'business_iban'            => $validated['business_iban']            ?? null,
                'business_payment_method'  => $validated['business_payment_method']  ?? null,
                'business_signature_name'  => $validated['business_signature_name']  ?? null,
                'business_signature_title' => $validated['business_signature_title'] ?? null,
                'logo_data'        => isset($validated['logo_data']) && $validated['logo_data'] !== ''
                    ? $validated['logo_data']
                    : null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            $invoice->items()->delete();

            $itemRows = [];
            $now      = Date::now();
            foreach ($totals['items'] as $row) {
                $itemRows[] = [
                    'invoice_id'  => $invoice->id,
                    'description' => $row['description'],
                    'details'     => $row['details'],
                    'quantity'    => $row['quantity'],
                    'price'       => $row['price'],
                    'total'       => $row['total'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            DB::table('invoice_items')->insert($itemRows);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        session()->flash('success', "Invoice <strong>{$invoice->invoice_number}</strong> updated.");

        return redirect()->route('invoices.show', $invoice);
    }

    /**
     * DELETE /invoices/{invoice}
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $number = $invoice->invoice_number;
        $invoice->delete();

        session()->flash('success', "Invoice <strong>{$number}</strong> deleted.");

        return redirect()->route('invoices.index');
    }

    /**
     * POST /invoices/{invoice}/mark-paid — flip status to paid.
     */
    public function markPaid(Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $invoice->update(['status' => Invoice::STATUS_PAID]);

        session()->flash('success', "Invoice <strong>{$invoice->invoice_number}</strong> marked as paid.");

        return redirect()->route('invoices.show', $invoice);
    }

    /**
     * POST /invoices/{invoice}/duplicate — copy an invoice.
     * New invoice number (next sequential), invoice_date = today,
     * due_date = today + 30 days, status = draft.
     */
    public function duplicate(Invoice $invoice): RedirectResponse
    {
        $this->authorize('create', Invoice::class);
        $this->authorize('view', $invoice);

        $invoice->load(['items']);
        $user = auth()->user();

        DB::beginTransaction();
        try {
            /** @var Invoice $copy */
            $copy = Invoice::create([
                'user_id'          => $user->id,
                'client_id'        => $invoice->client_id,
                'invoice_number'   => $user->nextInvoiceNumber(),
                'invoice_date'     => Date::today(),
                'due_date'         => Date::today()->addDays(30),
                'currency'         => $invoice->currency,
                'status'           => Invoice::STATUS_DRAFT,
                'subtotal'         => $invoice->subtotal,
                'tax_percent'      => $invoice->tax_percent,
                'tax_amount'       => $invoice->tax_amount,
                'discount'         => $invoice->discount,
                'total'            => $invoice->total,
                'business_name'    => $invoice->business_name,
                'business_email'   => $invoice->business_email,
                'business_phone'   => $invoice->business_phone,
                'business_address' => $invoice->business_address,
                'business_website'         => $invoice->business_website,
                'business_tagline'         => $invoice->business_tagline,
                'business_bank_name'       => $invoice->business_bank_name,
                'business_account_title'   => $invoice->business_account_title,
                'business_account_number'  => $invoice->business_account_number,
                'business_iban'            => $invoice->business_iban,
                'business_payment_method'  => $invoice->business_payment_method,
                'business_signature_name'  => $invoice->business_signature_name,
                'business_signature_title' => $invoice->business_signature_title,
                'logo_data'        => $invoice->logo_data,
                'notes'            => $invoice->notes,
            ]);

            $now = Date::now();
            $itemRows = $invoice->items->map(function (InvoiceItem $i) use ($copy, $now) {
                return [
                    'invoice_id'  => $copy->id,
                    'description' => $i->description,
                    'details'     => $i->details,
                    'quantity'    => $i->quantity,
                    'price'       => $i->price,
                    'total'       => $i->total,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            })->all();

            DB::table('invoice_items')->insert($itemRows);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        session()->flash('success', "Invoice <strong>{$copy->invoice_number}</strong> created as a copy.");

        return redirect()->route('invoices.edit', $copy);
    }

    // ------------------------------------------------------------------
    // PDF streaming (authenticated persisted invoices only)
    // ------------------------------------------------------------------

    /**
     * GET /invoices/{invoice}/pdf — stream PDF from a persisted invoice.
     * InvoicePolicy@view gates access; falls back to user's BusinessProfile
     * for logo / business info when the invoice has none stored.
     */
    public function download(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);
        $invoice->load(['client', 'items', 'user.businessProfile']);

        $currencyInfo = Invoice::currencies()[$invoice->currency]
            ?? Invoice::currencies()['USD'];

        $itemsForView = $invoice->items->map(function (InvoiceItem $item) {
            $details = trim((string) ($item->details ?? ''));

            return [
                'description' => $item->description,
                'details'     => $details === '' ? null : $details,
                'quantity'    => $item->quantity,
                'price'       => $item->price / 100,
                'line_total'  => $item->total / 100,
            ];
        })->all();

        $totals = [
            'subtotal' => $invoice->subtotal / 100,
            'tax'      => $invoice->tax_amount / 100,
            'tax_pct'  => (float) $invoice->tax_percent,
            'discount' => $invoice->discount / 100,
            'total'    => $invoice->total / 100,
        ];

        $profile = $invoice->user?->businessProfile;

        $viewData = [
            'business_name'    => $invoice->business_name    ?? ($profile?->business_name ?? $invoice->user?->name ?? 'Your business'),
            'business_email'   => $invoice->business_email   ?? ($profile?->email         ?? null),
            'business_phone'   => $invoice->business_phone   ?? ($profile?->phone         ?? null),
            'business_address' => $invoice->business_address ?? ($profile?->address       ?? null),
            'business_website' => $invoice->business_website ?? ($profile?->website       ?? null),
            'business_tagline' => $invoice->business_tagline ?? ($profile?->tagline       ?? null),
            'business_tax'     => $profile?->tax_number,
            'bank_name'          => $invoice->business_bank_name      ?? ($profile?->bank_name       ?? null),
            'account_title'      => $invoice->business_account_title  ?? ($profile?->account_title   ?? null),
            'account_number'     => $invoice->business_account_number ?? ($profile?->account_number  ?? null),
            'iban'               => $invoice->business_iban           ?? ($profile?->iban            ?? null),
            'payment_method'     => $invoice->business_payment_method ?? ($profile?->payment_method  ?? null),
            'signature_name'     => $invoice->business_signature_name ?? ($profile?->signature_name  ?? null),
            'signature_title'    => $invoice->business_signature_title ?? ($profile?->signature_title ?? null),
            'client_name'      => optional($invoice->client)->name ?? '—',
            'client_email'     => optional($invoice->client)->email,
            'client_phone'     => optional($invoice->client)->phone,
            'client_address'   => optional($invoice->client)->address,
            'invoice_number'   => $invoice->invoice_number,
            'invoice_date'     => $invoice->invoice_date->format('M j, Y'),
            'due_date'         => optional($invoice->due_date)?->format('M j, Y'),
            'status'           => $invoice->display_status,
            'currency'         => $invoice->currency,
            'currency_symbol'  => $currencyInfo['symbol'],
            'items'            => $itemsForView,
            'totals'           => $totals,
            'notes'            => $invoice->notes,
            'logo_data'        => $invoice->logo_data ?? ($profile?->logo_base64 ?? null),
        ];

        $pdf = Pdf::loadView('invoice.pdf', $viewData)->setPaper('a4', 'portrait');

        $safeNumber = preg_replace('/[^\w\-]/', '-', $invoice->invoice_number) ?: 'invoice';

        return $pdf->stream("invoice-{$safeNumber}.pdf");
    }
}
