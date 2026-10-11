<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Date;

/**
 * All report aggregation lives here (not in Blade): date ranges, currency
 * resolution, the summary cards, the four chart datasets and the CSV export.
 *
 * Every query is scoped to the logged-in user and to ONE currency — revenue,
 * pending, overdue and invoice counts are never mixed across currencies.
 *
 * Money is returned as cents (int); converting to display units (cents/100)
 * is the controller's job. The charts consume those already-divided values.
 */
class ReportService
{
    // ------------------------------------------------------------------
    // Date range
    // ------------------------------------------------------------------

    /**
     * The filter options shown in the reports UI.
     */
    public function ranges(): array
    {
        return [
            'this_month'    => 'This month',
            'last_3_months' => 'Last 3 months',
            'this_year'     => 'This year',
            'custom'        => 'Custom range',
        ];
    }

    /**
     * Resolve a (range, from, to) filter into concrete window boundaries.
     *
     * Returns ['range', 'label', 'from' (Carbon, startOfDay), 'to' (Carbon,
     * endOfDay), 'from_text', 'to_text']. An invalid/out-of-order custom
     * range falls back gracefully, and any range longer than ~5 years is
     * clamped so the month-by-month chart never loops over thousands of bars.
     *
     * @return array{range: string, label: string, from: \Illuminate\Support\Carbon, to: \Illuminate\Support\Carbon, from_text: string, to_text: string}
     */
    public function resolveRange(?string $range, mixed $from = null, mixed $to = null): array
    {
        $labels = $this->ranges();
        $today  = Date::today();

        $start = null;
        $end   = null;

        switch ($range) {
            case 'last_3_months':
                $start = $today->copy()->startOfMonth()->subMonths(2);
                $end   = $today->copy();
                break;

            case 'this_year':
                $start = $today->copy()->startOfYear();
                $end   = $today->copy();
                break;

            case 'custom':
                $start = $this->parseDate($from) ?? $today->copy()->startOfMonth();
                $end   = $this->parseDate($to) ?? $today->copy();
                break;

            case 'this_month':
            default:
                $range = 'this_month';
                $start = $today->copy()->startOfMonth();
                $end   = $today->copy();
                break;
        }

        // A reversed range is a common mis-click; swap instead of erroring.
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $start = $start->copy()->startOfDay();
        $end   = $end->copy()->endOfDay();

        // Cap the month-by-month chart at 60 bars for very wide custom ranges.
        $monthDiff = $start->copy()->startOfMonth()->diffInMonths($end->copy()->startOfMonth());
        if ($monthDiff > 60) {
            $start = $end->copy()->subMonths(59)->startOfMonth()->startOfDay();
        }

        return [
            'range'     => $range,
            'label'     => $labels[$range] ?? $labels['this_month'],
            'from'      => $start,
            'to'        => $end,
            'from_text' => $start->format('Y-m-d'),
            'to_text'   => $end->format('Y-m-d'),
        ];
    }

    /**
     * Parse a date query-string value into Carbon, or null when absent/invalid.
     */
    private function parseDate(mixed $value)
    {
        if (!$value) {
            return null;
        }

        try {
            return Date::parse($value)->copy();
        } catch (\Throwable) {
            return null;
        }
    }

    // ------------------------------------------------------------------
    // Currency resolution (invoice-based, matching the dashboard)
    // ------------------------------------------------------------------

    /**
     * Invoices grouped by currency: [code => count], most used first.
     */
    public function currencyCounts(User $user): array
    {
        return $user->invoices()
            ->selectRaw('currency, COUNT(*) as cnt')
            ->groupBy('currency')
            ->orderByDesc('cnt')
            ->orderBy('currency')
            ->pluck('cnt', 'currency')
            ->all();
    }

    /**
     * Resolve ?currency=CODE against the currencies the user actually has
     * invoices in, falling back to their most-used one.
     */
    public function resolveCurrency(User $user, mixed $requested = null): array
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
    // Summary cards
    // ------------------------------------------------------------------

    /**
     * Five currency-scoped summary cards for the selected range:
     *
     *  - revenue         : SUM of payment amounts (paid_on within the range)
     *  - pending         : SUM of pending-not-overdue invoice totals (in range)
     *  - overdue         : SUM of overdue invoice totals (in range)
     *  - invoices_count  : COUNT of invoices dated within the range
     *  - average         : SUM(total)/COUNT of those invoices
     *
     * @return array{revenue: int, pending: int, overdue: int, invoices_count: int, average: int}
     */
    public function cards(User $user, string $currency, array $range): array
    {
        $invoices = fn () => $user->invoices()
            ->where('currency', $currency)
            ->whereBetween('invoice_date', [$range['from'], $range['to']]);

        $count     = (int) $invoices()->count();
        $valueTotal = (int) $invoices()->sum('total');

        $revenue = (int) $user->payments()
            ->whereHas('invoice', fn ($inv) => $inv->where('currency', $currency))
            ->whereBetween('paid_on', [$range['from'], $range['to']])
            ->sum('amount');

        $pending = (int) $invoices()->pendingNotOverdue()->sum('total');
        $overdue = (int) $invoices()->overdue()->sum('total');

        return [
            'revenue'        => $revenue,
            'pending'        => $pending,
            'overdue'        => $overdue,
            'invoices_count' => $count,
            'average'        => $count > 0 ? intdiv($valueTotal, $count) : 0,
        ];
    }

    // ------------------------------------------------------------------
    // Chart datasets
    // ------------------------------------------------------------------

    /**
     * Revenue by month (bar): SUM of payments bucketed by "Y-m" over the
     * selected range, with every intermediate month filled to keep the bars
     * continuous. Values are already in currency units (cents/100).
     *
     * @return array{labels: list<string>, data: list<float>, empty: bool}
     */
    public function revenueByMonth(User $user, string $currency, array $range): array
    {
        // SUBSTR of the ISO date keeps this working on both MySQL and SQLite.
        $rows = $user->payments()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.currency', $currency)
            ->whereBetween('payments.paid_on', [$range['from'], $range['to']])
            ->selectRaw('SUBSTR(payments.paid_on, 1, 7) as bucket, SUM(payments.amount) as total')
            ->groupBy('bucket')
            ->pluck('total', 'bucket')
            ->all();

        $labels = [];
        $data   = [];
        $sum    = 0;

        $cursor = $range['from']->copy()->startOfMonth();
        $end    = $range['to']->copy()->endOfMonth();

        while ($cursor->lte($end)) {
            $key    = $cursor->format('Y-m');
            $cents  = (int) ($rows[$key] ?? 0);
            $labels[] = $cursor->format('M Y');
            $data[]   = round($cents / 100, 2);
            $sum      += $cents;
            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'data'   => $data,
            'empty'  => $sum === 0,
        ];
    }

    /**
     * Paid vs unpaid (doughnut): invoice COUNTS by status. Scoped to ALL
     * invoices in the currency (not just the range) so that
     * paid + pending + overdue === total invoices for that currency.
     *
     * @return array{labels: list<string>, data: list<int>, total: int, empty: bool}
     */
    public function paidUnpaid(User $user, string $currency): array
    {
        // A closure builds a FRESH query each time — scopes like paid() and
        // overdue() mutate the builder they are called on, so reusing one
        // $base would leak the previous status constraint into the next count.
        $base = fn () => $user->invoices()->where('currency', $currency);

        $total   = (int) $base()->count();
        $paid    = (int) $base()->paid()->count();
        $overdue = (int) $base()->overdue()->count();
        $pending = max(0, $total - $paid - $overdue);

        return [
            'labels' => ['Paid', 'Pending', 'Overdue'],
            'data'   => [$paid, $pending, $overdue],
            'total'  => $total,
            'empty'  => $total === 0,
        ];
    }

    /**
     * Top 5 clients by paid revenue (horizontal bar): SUM of payments joined
     * to clients through invoices, within the range and currency.
     *
     * @return array{labels: list<string>, data: list<float>, empty: bool}
     */
    public function topClients(User $user, string $currency, array $range): array
    {
        $rows = Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->where('payments.user_id', $user->id)
            ->where('invoices.currency', $currency)
            ->whereBetween('payments.paid_on', [$range['from'], $range['to']])
            ->selectRaw('COALESCE(clients.name, \'—\') as client_name, SUM(payments.amount) as total')
            ->groupBy('clients.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->pluck('client_name')->all(),
            'data'   => $rows->map(fn ($r) => round($r->total / 100, 2))->all(),
            'empty'  => $rows->isEmpty(),
        ];
    }

    /**
     * Revenue by service (bar, top 8): SUM of invoice_items.total grouped by
     * item description, for invoices in the range and currency.
     *
     * @return array{labels: list<string>, data: list<float>, empty: bool}
     */
    public function revenueByService(User $user, string $currency, array $range): array
    {
        $rows = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.user_id', $user->id)
            ->where('invoices.currency', $currency)
            ->whereBetween('invoices.invoice_date', [$range['from'], $range['to']])
            ->selectRaw('invoice_items.description as service, SUM(invoice_items.total) as total')
            ->groupBy('invoice_items.description')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('service')->all(),
            'data'   => $rows->map(fn ($r) => round($r->total / 100, 2))->all(),
            'empty'  => $rows->isEmpty(),
        ];
    }

    // ------------------------------------------------------------------
    // CSV export
    // ------------------------------------------------------------------

    /**
     * Build the full CSV export for the filtered invoices (one currency,
     * one range). Minor units are converted to decimals and every cell that
     * could trigger a spreadsheet formula (= + - @) is escaped by prefixing
     * it with an apostrophe — a BOM is prepended so Excel reads UTF-8.
     */
    public function csvFor(User $user, string $currency, array $range): string
    {
        $invoices = $user->invoices()
            ->with('client')
            ->where('currency', $currency)
            ->whereBetween('invoice_date', [$range['from'], $range['to']])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get();

        $lines = [implode(',', $this->csvRow([
            'Invoice', 'Client', 'Date', 'Due date',
            'Currency', 'Subtotal', 'Tax', 'Discount', 'Total', 'Status',
        ]))];

        foreach ($invoices as $invoice) {
            $lines[] = implode(',', $this->csvRow([
                $invoice->invoice_number,
                optional($invoice->client)->name ?? '',
                $invoice->invoice_date->format('Y-m-d'),
                optional($invoice->due_date)?->format('Y-m-d') ?? '',
                $invoice->currency,
                number_format($invoice->subtotal / 100, 2, '.', ''),
                number_format($invoice->tax_amount / 100, 2, '.', ''),
                number_format($invoice->discount / 100, 2, '.', ''),
                number_format($invoice->total / 100, 2, '.', ''),
                $invoice->display_status,
            ]));
        }

        return "\xEF\xBB\xBF" . implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Prepare one CSV cell: escape quotes, then neutralise formula triggers.
     */
    private function csvRow(array $cells): array
    {
        return array_map(function (mixed $value): string {
            $value = $value === null ? '' : (string) $value;

            // Cells starting with a formula char get a leading apostrophe so
            // Excel/LibreOffice/Maps never evaluate them as a formula.
            if ($value !== '' && str_contains('=+-@', $value[0])) {
                $value = "'" . $value;
            }

            return '"' . str_replace('"', '""', $value) . '"';
        }, $cells);
    }
}