<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarkInvoicePaidRequest;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ------------------------------------------------------------------
    // Currency helpers (mirror DashboardController, scoped to payments)
    // ------------------------------------------------------------------

    /**
     * Payments grouped by their invoice's currency: [code => count],
     * most used first with alphabetical tie-breaks.
     */
    protected function currencyCounts(\App\Models\User $user): array
    {
        return $user->payments()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->selectRaw('invoices.currency as currency, COUNT(*) as cnt')
            ->groupBy('invoices.currency')
            ->orderByDesc('cnt')
            ->orderBy('currency')
            ->pluck('cnt', 'currency')
            ->all();
    }

    /**
     * Resolve ?currency=CODE against the currencies the user actually has
     * payments in, falling back to their most-used one.
     */
    protected function resolveCurrency(\App\Models\User $user, mixed $requested = null): array
    {
        $counts = $this->currencyCounts($user);

        $code = strtoupper((string) $requested);
        if ($code === '' || !isset($counts[$code])) {
            $code = array_key_first($counts) ?: 'USD';
        }

        $all  = Invoice::currencies();
        $meta = $all[$code] ?? $all['USD'];

        $available = [];
        foreach ($counts as $candidate => $cnt) {
            if (!isset($all[$candidate])) {
                continue;
            }
            $available[] = [
                'code'   => $candidate,
                'symbol' => $all[$candidate]['symbol'],
                'name'   => $all[$candidate]['name'],
                'count'  => (int) $cnt,
            ];
        }

        return [
            'code'      => $code,
            'symbol'    => $meta['symbol'],
            'name'      => $meta['name'],
            'available' => $available,
        ];
    }

    // ------------------------------------------------------------------
    // Actions
    // ------------------------------------------------------------------

    /**
     * GET /payments — currency-scoped summary cards + searchable list.
     */
    public function index(Request $request): View
    {
        $user    = auth()->user();
        $primary = $this->resolveCurrency($user, $request->query('currency'));
        $code    = $primary['code'];

        // Summary card totals, scoped to the selected currency.
        $base = fn () => $user->payments()
            ->whereHas('invoice', fn ($inv) => $inv->where('currency', $code));

        $totalCents = (int) $base()->sum('amount');
        $count      = (int) $base()->count();
        $monthCents = (int) $base()
            ->where('paid_on', '>=', Date::today()->startOfMonth())
            ->where('paid_on', '<=', Date::today())
            ->sum('amount');
        $average    = $count > 0 ? intdiv($totalCents, $count) : 0;

        $summary = [
            'total_cents'    => $totalCents,
            'month_cents'    => $monthCents,
            'count'          => $count,
            'average_cents'  => $average,
        ];

        // Filter + list query (list is scoped to the selected currency).
        $q      = trim((string) $request->input('q', ''));
        $method = (string) $request->input('method', '');

        $query = Payment::query()
            ->where('payments.user_id', $user->id)
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.currency', $code)
            ->select('payments.*')
            ->with(['invoice.client']);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('payments.reference', 'like', '%' . $q . '%')
                    ->orWhere('invoices.invoice_number', 'like', '%' . $q . '%')
                    ->orWhereHas('invoice.client', fn ($c) => $c->where('name', 'like', '%' . $q . '%'));
            });
        }

        if ($method !== '' && array_key_exists($method, Payment::methods())) {
            $query->where('payments.method', $method);
        }

        $query->orderByDesc('payments.paid_on')->orderByDesc('payments.id');

        $payments = $query->paginate(15)->withQueryString();

        return view('payments.index', [
            'currency' => $primary,
            'summary'  => $summary,
            'payments' => $payments,
            'methods'  => Payment::methods(),
            'filters'  => [
                'q'      => $q,
                'method' => $method,
            ],
        ]);
    }

    /**
     * POST /invoices/{invoice}/pay — record a payment and settle the invoice.
     * Re-posting on an already-paid invoice is a no-op.
     */
    public function store(MarkInvoicePaidRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if ($invoice->status === Invoice::STATUS_PAID) {
            session()->flash('success', "Invoice <strong>{$invoice->invoice_number}</strong> is already marked as paid.");

            return redirect()->route('invoices.show', $invoice);
        }

        $validated = $request->validated();
        $user      = auth()->user();

        DB::transaction(function () use ($invoice, $validated, $user) {
            Payment::create([
                'user_id'    => $user->id,
                'invoice_id' => $invoice->id,
                'amount'     => $invoice->total,
                'paid_on'    => $validated['paid_on'],
                'method'     => $validated['method'],
                'reference'  => $validated['reference'] ?? null,
            ]);

            $invoice->update(['status' => Invoice::STATUS_PAID]);
        });

        session()->flash('success', "Invoice <strong>{$invoice->invoice_number}</strong> marked as paid.");

        return redirect()->route('invoices.show', $invoice);
    }

    /**
     * DELETE /payments/{payment} — undo a payment and reopen the invoice.
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $payment->loadMissing('invoice');
        $invoice = $payment->invoice;
        $number  = $invoice?->invoice_number ?? '—';

        DB::transaction(function () use ($payment, $invoice) {
            $payment->delete();

            if ($invoice && $invoice->status === Invoice::STATUS_PAID) {
                $invoice->update(['status' => Invoice::STATUS_PENDING]);
            }
        });

        session()->flash('success', "Payment for invoice <strong>{$number}</strong> removed. Invoice is pending again.");

        if ($invoice) {
            return redirect()->route('invoices.show', $invoice);
        }

        return redirect()->route('payments.index');
    }
}
