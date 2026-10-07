<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the authenticated user's dashboard.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $today = Date::today();

        // Counts
        $totalCount   = (int) $user->invoices()->count();
        $paidCount    = (int) $user->invoices()->paid()->count();
        $pendingCount = (int) $user->invoices()->pendingNotOverdue()->count();
        $overdueCount = (int) $user->invoices()->overdue()->count();

        // Currency usage: pick the user's most-used currency for card totals.
        $currencyCounts = $user->invoices()
            ->selectRaw('currency, COUNT(*) as cnt')
            ->groupBy('currency')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'currency')
            ->all();

        $primaryCurrency = array_key_first($currencyCounts) ?: 'USD';
        $allSameCurrency  = count($currencyCounts) <= 1;

        $currencies = Invoice::currencies();

        // Totals (in cents) in the primary currency only.
        $sumTotal = function ($scope) use ($user, $primaryCurrency) {
            return (int) $user->invoices()
                ->{$scope}()
                ->where('currency', $primaryCurrency)
                ->sum('total');
        };

        $totalPaidSum    = $sumTotal('paid');
        $totalPendingSum = $sumTotal('pendingNotOverdue');
        $totalOverdueSum = $sumTotal('overdue');
        $totalAllSum     = (int) $user->invoices()
            ->where('currency', $primaryCurrency)
            ->sum('total');

        $stats = [
            'counts' => [
                'total'   => $totalCount,
                'paid'    => $paidCount,
                'pending' => $pendingCount,
                'overdue' => $overdueCount,
            ],
            'amounts' => [
                'total'   => $totalAllSum,
                'paid'    => $totalPaidSum,
                'pending' => $totalPendingSum,
                'overdue' => $totalOverdueSum,
            ],
            'currency' => $primaryCurrency,
            'currency_symbol' => $currencies[$primaryCurrency]['symbol'] ?? '$',
            'currency_name'   => $currencies[$primaryCurrency]['name']   ?? 'US Dollar',
            'mixed_currencies'   => !$allSameCurrency,
            'currencies_used'    => array_keys($currencyCounts),
        ];

        // Greeting based on local hour.
        $hour   = (int) now()->format('G');
        $greet  = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        // 5 most recent invoices (with client + items counts).
        $recentInvoices = $user->invoices()
            ->with(['client'])
            ->withCount('items')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'user'            => $user,
            'greeting'        => $greet,
            'stats'           => $stats,
            'recentInvoices'  => $recentInvoices,
        ]);
    }
}
