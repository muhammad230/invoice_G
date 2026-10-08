<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** @return \App\Models\User */
    protected function user()
    {
        /** @var \App\Models\User $u */
        $u = Auth::user();
        return $u;
    }

    /**
     * Choose the user's most-used currency for aggregated stats.
     */
    protected function primaryCurrency(\App\Models\User $user): array
    {
        $currencyCounts = $user->invoices()
            ->selectRaw('currency, COUNT(*) as cnt')
            ->groupBy('currency')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'currency')
            ->all();

        $code     = array_key_first($currencyCounts) ?: 'USD';
        $meta     = Invoice::currencies()[$code] ?? Invoice::currencies()['USD'];

        return [
            'code'           => $code,
            'symbol'         => $meta['symbol'],
            'name'           => $meta['name'],
            'mixed'          => count($currencyCounts) > 1,
            'list'           => array_keys($currencyCounts),
        ];
    }

    /**
     * Compute a "vs previous 30d" percentage delta.
     * If previous is 0, returns ['pct' => null, 'direction' => 'new'] so the
     * view can show the "New" pill and avoid division by zero.
     */
    protected function delta(int $current, int $previous): array
    {
        if ($previous <= 0 && $current <= 0) {
            return ['pct' => null, 'direction' => 'flat'];
        }
        if ($previous <= 0) {
            return ['pct' => null, 'direction' => 'new'];
        }
        if ($current === $previous) {
            return ['pct' => 0, 'direction' => 'flat'];
        }
        $change = (($current - $previous) / $previous) * 100;
        return [
            'pct'       => round($change, 1),
            'direction' => $change > 0 ? 'up' : 'down',
        ];
    }

    /**
     * Build 4 stats cards: current window (30d) vs previous window (30d before).
     *
     * - Total Invoices  : count
     * - Total Revenue   : sum(paid invoices total_cents)
     * - Pending Payments: sum(pending & not overdue total_cents)
     * - Overdue         : sum(overdue total_cents)
     */
    protected function buildStats(\App\Models\User $user, string $currency): array
    {
        $today    = Date::today();
        $endCurr  = $today->copy();
        $startCurr = $today->copy()->subDays(29);              // 30 days inclusive
        $startPrev = $today->copy()->subDays(59);              // 30 days before startCurr
        $endPrev   = $today->copy()->subDays(30);

        // We scope time windows to invoice_date for "revenue" style stats
        // and created_at for "count of invoices" consistency.
        $scopeCurrency = fn ($q) => $q->where('currency', $currency);

        $countIn = function ($start, $end) use ($user, $scopeCurrency) {
            return (int) $scopeCurrency($user->invoices())
                ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                ->count();
        };

        $sumScoped = function ($scope, $start, $end) use ($user, $currency) {
            return (int) $user->invoices()
                ->where('currency', $currency)
                ->{$scope}()
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('total');
        };

        $sumAnyTime = function ($scope) use ($user, $currency) {
            return (int) $user->invoices()
                ->where('currency', $currency)
                ->{$scope}()
                ->sum('total');
        };

        // ---- Counts (created in window) ---------------------------------
        $currCount    = $countIn($startCurr, $endCurr);
        $prevCount    = $countIn($startPrev, $endPrev);
        $totalCountAll= (int) $scopeCurrency($user->invoices())->count();

        // ---- Revenue = PAID invoices within window (invoice_date in range)
        $currRevenue  = $sumScoped('paid', $startCurr, $endCurr);
        $prevRevenue  = $sumScoped('paid', $startPrev, $endPrev);
        $revenueAll   = $sumAnyTime('paid');

        // ---- Pending = pending NOT overdue. For balances we use "all time" totals,
        //      because outstanding balances aren't tied to creation date. But we
        //      still compute a 30d window for the delta *change in balance*.
        $currPending  = $sumScoped('pendingNotOverdue', $startCurr, $endCurr);
        $prevPending  = $sumScoped('pendingNotOverdue', $startPrev, $endPrev);
        $pendingAll   = $sumAnyTime('pendingNotOverdue');

        // ---- Overdue (all-time sum for big number; delta vs window) -----
        $currOverdue  = $sumScoped('overdue', $startCurr, $endCurr);
        $prevOverdue  = $sumScoped('overdue', $startPrev, $endPrev);
        $overdueAll   = $sumAnyTime('overdue');

        return [
            'currency_code'   => $currency,
            'total_all_count' => $totalCountAll,
            [
                'key'       => 'invoices',
                'label'     => 'Total Invoices',
                'icon'      => 'bi-file-earmark-text',
                'iconTone'  => 'total',
                'amount'    => false, // display count, not money
                'value'     => $totalCountAll,
                'delta'     => $this->delta($currCount, $prevCount),
            ],
            [
                'key'       => 'revenue',
                'label'     => 'Total Revenue',
                'icon'      => 'bi-wallet2',
                'iconTone'  => 'paid',
                'amount'    => true,
                'value'     => $revenueAll,
                'delta'     => $this->delta($currRevenue, $prevRevenue),
            ],
            [
                'key'       => 'pending',
                'label'     => 'Pending Payments',
                'icon'      => 'bi-clock-history',
                'iconTone'  => 'pending',
                'amount'    => true,
                'value'     => $pendingAll,
                'delta'     => $this->delta($currPending, $prevPending),
            ],
            [
                'key'       => 'overdue',
                'label'     => 'Overdue',
                'icon'      => 'bi-exclamation-triangle',
                'iconTone'  => 'overdue',
                'amount'    => true,
                'value'     => $overdueAll,
                'delta'     => $this->delta($currOverdue, $prevOverdue),
            ],
        ];
    }

    /**
     * Paid revenue per day (for line chart). Works for any range.
     * Days without paid invoices appear as 0.
     */
    public function buildLineChartData(\App\Models\User $user, string $currency, string $range): array
    {
        $today = Date::today();

        switch ($range) {
            case '7d':
                $days = 7;
                break;
            case '3m':
                $days = 90;
                break;
            case '1y':
                $days = 365;
                break;
            case '30d':
            default:
                $days = 30;
                $range = '30d';
        }

        $start = $today->copy()->subDays($days - 1)->startOfDay();
        $end   = $today->copy()->endOfDay();

        $rows = $user->invoices()
            ->paid()
            ->where('currency', $currency)
            ->whereBetween('invoice_date', [$start, $today])
            ->selectRaw('DATE(invoice_date) as bucket, SUM(total) as total_cents')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('total_cents', 'bucket')
            ->all();

        // Build a full sequence so missing days = 0.
        $labels = [];
        $data   = [];
        $cursor = $start->copy();
        $totalPaidInRange = 0;
        while ($cursor->lte($end)) {
            $key   = $cursor->format('Y-m-d');
            $label = $days <= 31
                ? $cursor->format('M j')          // 7D, 30D → "Oct 1"
                : $cursor->format('M Y');         // 3M, 1Y  → "Oct 2025"
            $cents = (int) ($rows[$key] ?? 0);
            $labels[] = $label;
            $data[]   = (float) number_format($cents / 100, 2, '.', '');
            $totalPaidInRange += $cents;
            $cursor->addDay();
        }

        return [
            'range'            => $range,
            'days'             => $days,
            'labels'           => $labels,
            'data'             => $data,
            'total_paid_cents' => $totalPaidInRange,
        ];
    }

    /**
     * Payment-status doughnut data (counts): paid, pending, overdue.
     * Percentages are relative to all invoices; a row with all 0 is ok.
     */
    public function buildDoughnutData(\App\Models\User $user): array
    {
        $paid    = (int) $user->invoices()->paid()->count();
        $pending = (int) $user->invoices()->pendingNotOverdue()->count();
        $overdue = (int) $user->invoices()->overdue()->count();
        $total   = $paid + $pending + $overdue;

        $pct = fn ($n) => $total <= 0 ? 0 : (float) number_format(($n / $total) * 100, 1, '.', '');

        return [
            'labels'   => ['Paid', 'Pending', 'Overdue'],
            'counts'   => [$paid, $pending, $overdue],
            'colors'   => ['#3F6B50', '#F2A33A', '#B3372F'],
            'percents' => [$pct($paid), $pct($pending), $pct($overdue)],
            'total'    => $total,
        ];
    }

    /**
     * Build the Recent Activity list from existing invoices + clients records.
     * Returns up to 8 events sorted newest-first. Each event carries a
     * formatted Carbon diffForHumans relative time.
     */
    protected function buildActivity(\App\Models\User $user): array
    {
        $events = [];

        // Invoice events: created_at → invoice created, status=paid → paid.
        // Also mark overdue events based on due_date < today.
        $invoices = $user->invoices()
            ->with('client')
            ->latest('created_at')
            ->limit(12)
            ->get();

        foreach ($invoices as $inv) {
            $clientName = optional($inv->client)->name ?: 'a client';

            $events[] = [
                'type'      => 'invoice_created',
                'icon'      => 'bi-file-earmark-plus',
                'tone'      => 'ink',
                'text'      => 'Invoice <strong>' . e($inv->invoice_number) . '</strong> created for ' . e($clientName),
                'action'    => route('invoices.show', $inv),
                'time'      => $inv->created_at->diffForHumans(),
                'sort'      => $inv->created_at->timestamp,
            ];

            if ($inv->status === Invoice::STATUS_PAID && $inv->updated_at) {
                $events[] = [
                    'type'      => 'invoice_paid',
                    'icon'      => 'bi-check-circle-fill',
                    'tone'      => 'paid',
                    'text'      => 'Invoice <strong>' . e($inv->invoice_number) . '</strong> marked as paid',
                    'action'    => route('invoices.show', $inv),
                    'time'      => $inv->updated_at->diffForHumans(),
                    'sort'      => $inv->updated_at->timestamp,
                ];
            }

            if ($inv->display_status === 'overdue' && $inv->due_date) {
                $events[] = [
                    'type'      => 'invoice_overdue',
                    'icon'      => 'bi-exclamation-octagon-fill',
                    'tone'      => 'overdue',
                    'text'      => 'Invoice <strong>' . e($inv->invoice_number) . '</strong> is overdue · ' . e($inv->total_formatted),
                    'action'    => route('invoices.show', $inv),
                    'time'      => $inv->due_date->diffForHumans(),
                    'sort'      => $inv->due_date->timestamp,
                ];
            }
        }

        // Client events
        $clients = $user->clients()
            ->latest('created_at')
            ->limit(8)
            ->get();

        foreach ($clients as $c) {
            $events[] = [
                'type'      => 'client_created',
                'icon'      => 'bi-person-plus-fill',
                'tone'      => 'saffron',
                'text'      => 'New client added: <strong>' . e($c->name) . '</strong>',
                'action'    => route('clients.edit', $c),
                'time'      => $c->created_at->diffForHumans(),
                'sort'      => $c->created_at->timestamp,
            ];
        }

        usort($events, fn ($a, $b) => $b['sort'] - $a['sort']);

        return array_slice($events, 0, 8);
    }

    // ------------------------------------------------------------------
    // HTTP Actions
    // ------------------------------------------------------------------

    /**
     * GET /dashboard — full logged-in dashboard page.
     */
    public function index(Request $request): View
    {
        $user = $this->user();

        $hour    = (int) now()->format('G');
        $greet   = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $today   = Date::today();

        $primary  = $this->primaryCurrency($user);
        $stats    = $this->buildStats($user, $primary['code']);
        $chart    = $this->buildLineChartData($user, $primary['code'], '30d');
        $donut    = $this->buildDoughnutData($user);
        $activity = $this->buildActivity($user);

        $recentInvoices = $user->invoices()
            ->with(['client'])
            ->withCount('items')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'user'             => $user,
            'greeting'         => $greet,
            'today'            => $today,
            'currency'         => $primary,
            'stats'            => $stats,
            'chart'            => $chart,
            'donut'            => $donut,
            'activity'         => $activity,
            'recentInvoices'   => $recentInvoices,
        ]);
    }

    /**
     * GET /dashboard/chart — JSON endpoint for the line chart range buttons.
     * Valid ranges: 7d, 30d (default), 3m, 1y.
     */
    public function chart(Request $request): JsonResponse
    {
        $user    = $this->user();
        $primary = $this->primaryCurrency($user);
        $range   = strtolower((string) $request->input('range', '30d'));
        if (!in_array($range, ['7d', '30d', '3m', '1y'], true)) {
            $range = '30d';
        }

        $chart = $this->buildLineChartData($user, $primary['code'], $range);
        $total = $primary['symbol'] . number_format($chart['total_paid_cents'] / 100, 2);

        return response()->json([
            'labels'         => $chart['labels'],
            'data'           => $chart['data'],
            'range'          => $chart['range'],
            'total_for_range'=> $total,
        ]);
    }
}
