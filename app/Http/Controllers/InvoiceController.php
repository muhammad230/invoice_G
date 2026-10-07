<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function __construct()
    {
        // Auth required for all actions except the legacy download route
        // (which still validates the request body — kept for backward compat
        // with the unauthenticated demo form).
        $this->middleware('auth')->except(['downloadLegacy']);
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

            $itemRows[] = [
                'description' => $row['description'],
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
     * GET /invoices/create — invoice builder (w/ client dropdown).
     */
    public function create(): View
    {
        $user = auth()->user();

        $currencies = $this->currencies();
        $clients    = Client::where('user_id', $user->id)->orderBy('name')->get(['id', 'name', 'email']);

        $defaults = [
            'invoice_number' => $user->nextInvoiceNumber(),
            'invoice_date'   => Date::today()->format('Y-m-d'),
            'due_date'       => Date::today()->addDays(30)->format('Y-m-d'),
            'currency'       => 'USD',
            'tax'            => '0',
            'discount'       => '0',
            'status'         => Invoice::STATUS_PENDING,
        ];

        $invoice = null;
        $oldItems = [];

        return view('invoice.create', compact(
            'currencies',
            'clients',
            'defaults',
            'invoice',
            'oldItems'
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

        if ((string) $request->query('action', '') === 'pdf' || (string) $request->input('action', '') === 'pdf') {
            return $this->download($invoice);
        }

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
        $currencies = $this->currencies();
        $clients    = Client::where('user_id', $user->id)->orderBy('name')->get(['id', 'name', 'email']);

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
                'quantity'    => $i->quantity,
                'price'       => number_format($i->price / 100, 2, '.', ''),
            ];
        })->all();

        return view('invoice.create', compact(
            'currencies',
            'clients',
            'defaults',
            'invoice',
            'oldItems'
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

        if ((string) $request->query('action', '') === 'pdf' || (string) $request->input('action', '') === 'pdf') {
            return $this->download($invoice);
        }

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

    // ------------------------------------------------------------------
    // PDF download / streaming (DB + legacy)
    // ------------------------------------------------------------------

    /**
     * GET  /invoices/{invoice}/download — stream PDF from a persisted invoice.
     */
    public function download(Invoice $invoice): StreamedResponse
    {
        $this->authorize('view', $invoice);
        $invoice->load(['client', 'items']);

        $currencyInfo = Invoice::currencies()[$invoice->currency]
            ?? Invoice::currencies()['USD'];

        $itemsForView = $invoice->items->map(function (InvoiceItem $item) {
            return [
                'description' => $item->description,
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

        $viewData = [
            'business_name'    => $invoice->business_name,
            'business_email'   => $invoice->business_email,
            'business_phone'   => $invoice->business_phone,
            'business_address' => $invoice->business_address,
            'client_name'      => optional($invoice->client)->name ?? '—',
            'client_email'     => optional($invoice->client)->email,
            'client_phone'     => optional($invoice->client)->phone,
            'client_address'   => optional($invoice->client)->address,
            'invoice_number'   => $invoice->invoice_number,
            'invoice_date'     => $invoice->invoice_date->format('M j, Y'),
            'due_date'         => optional($invoice->due_date)?->format('M j, Y'),
            'currency'         => $invoice->currency,
            'currency_symbol'  => $currencyInfo['symbol'],
            'items'            => $itemsForView,
            'totals'           => $totals,
            'notes'            => $invoice->notes,
            'logo_data'        => $invoice->logo_data,
        ];

        $pdf = Pdf::loadView('invoice.pdf', $viewData)->setPaper('a4', 'portrait');

        $safeNumber = preg_replace('/[^\w\-]/', '-', $invoice->invoice_number) ?: 'invoice';

        return $pdf->stream("invoice-{$safeNumber}.pdf");
    }

    /**
     * POST /invoice/download — legacy unauthenticated PDF route.
     * Kept as `downloadLegacy` so it can still be reached from the
     * "Get started free" demo invoice builder on the home page.
     */
    public function downloadLegacy(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'business_name'    => 'required|string|max:255',
            'business_email'   => 'nullable|email|max:255',
            'business_phone'   => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:500',
            'client_name'      => 'required|string|max:255',
            'client_email'     => 'nullable|email|max:255',
            'client_phone'     => 'nullable|string|max:50',
            'client_address'   => 'nullable|string|max:500',
            'invoice_number'   => 'required|string|max:50',
            'invoice_date'     => 'required|date',
            'due_date'         => 'nullable|date|after_or_equal:invoice_date',
            'currency'         => 'required|string|in:USD,PKR,EUR,GBP',
            'items'            => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.price'       => 'required|numeric|min:0',
            'tax'      => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'notes'    => 'nullable|string|max:1000',
            'logo_data' => 'nullable|string',
        ]);

        $totals = $this->recalcTotals($validated);
        $currencyInfo = Invoice::currencies()[$validated['currency']]
            ?? Invoice::currencies()['USD'];

        $itemsForView = [];
        foreach ($totals['items'] as $row) {
            $itemsForView[] = [
                'description' => $row['description'],
                'quantity'    => $row['quantity'],
                'price'       => $row['price'] / 100,
                'line_total'  => $row['total'] / 100,
            ];
        }

        $viewData = array_merge($validated, [
            'invoice_date'    => date('M j, Y', strtotime($validated['invoice_date'])),
            'due_date'        => $validated['due_date']
                ? date('M j, Y', strtotime($validated['due_date']))
                : null,
            'currency_symbol' => $currencyInfo['symbol'],
            'items'           => $itemsForView,
            'totals'          => [
                'subtotal' => $totals['subtotal'] / 100,
                'tax'      => $totals['tax_amount'] / 100,
                'tax_pct'  => (float) $totals['tax_percent'],
                'discount' => $totals['discount'] / 100,
                'total'    => $totals['total'] / 100,
            ],
            'logo_data' => $validated['logo_data'] ?? null,
        ]);

        $pdf = Pdf::loadView('invoice.pdf', $viewData)->setPaper('a4', 'portrait');

        $safeNumber = preg_replace('/[^\w\-]/', '-', $validated['invoice_number']) ?: 'invoice';

        return $pdf->stream("invoice-{$safeNumber}.pdf");
    }
}
