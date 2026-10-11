@extends('layouts.dashboard')

@section('title', 'Reports — InvoiceFlow')

@php
    /* Money formatter for the selected currency (used by the summary cards). */
    $symbol = $currency['symbol'];
    $money  = function (int $cents) use ($symbol): string {
        return $symbol . number_format($cents / 100, 2);
    };

    $currencyOptions = $currency['available'] ?? [];

    /* A currency drop-down swap should keep the current range filter. */
    $currencyHref = function (string $code) use ($query): string {
        $q = $query;
        $q['currency'] = $code;

        return route('reports.index', $q);
    };
@endphp

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">

        {{-- Header + currency selector ------------------------------------- --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-bar-chart-line-fill me-1"></i>
                    Reports
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">
                    {{ $range['label'] }} · {{ $currency['name'] }}
                </h1>
                <p class="ink-soft mb-0 mt-1">
                    {{ $range['from_text'] }} – {{ $range['to_text'] }}
                </p>
            </div>

            @if (count($currencyOptions) > 1)
                <div class="dropdown">
                    <button type="button"
                            class="dash-currency-btn dropdown-toggle"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="Select display currency">
                        <i class="bi bi-coins" aria-hidden="true"></i>
                        <span class="dash-currency-btn__code">{{ $currency['code'] }}</span>
                        <span class="dash-currency-btn__sym">{{ $currency['symbol'] }}</span>
                        <span class="dash-currency-btn__name">{{ $currency['name'] }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dash-currency-menu shadow-sm">
                        @foreach ($currencyOptions as $opt)
                            <li>
                                <a class="dropdown-item {{ $opt['code'] === $currency['code'] ? 'active' : '' }}"
                                   href="{{ $currencyHref($opt['code']) }}">
                                    <span class="dash-currency-menu__code">{{ $opt['code'] }}</span>
                                    <span class="dash-currency-menu__sym">{{ $opt['symbol'] }}</span>
                                    <span class="dash-currency-menu__name">{{ $opt['name'] }}</span>
                                    <span class="dash-currency-menu__count">{{ $opt['count'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @elseif (count($currencyOptions) === 1)
                <span class="dash-currency-btn dash-currency-btn--static"
                      title="All your invoices are in {{ $currency['name'] }}">
                    <i class="bi bi-coins" aria-hidden="true"></i>
                    <span class="dash-currency-btn__code">{{ $currency['code'] }}</span>
                    <span class="dash-currency-btn__sym">{{ $currency['symbol'] }}</span>
                    <span class="dash-currency-btn__name">{{ $currency['name'] }}</span>
                </span>
            @endif
        </div>

        {{-- Range filter + CSV export --------------------------------------- --}}
        <form method="GET" action="{{ route('reports.index') }}" class="mb-4" role="search">
            <input type="hidden" name="currency" value="{{ $currency['code'] }}">
            <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap gap-3 align-items-end">
                        <div class="filter-field">
                            <label for="range" class="form-label">Date range</label>
                            <select id="range" name="range" class="form-select form-select-lg">
                                @foreach ($ranges as $value => $label)
                                    <option value="{{ $value }}" @selected($range['range'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="customDates" class="{{ $range['range'] === 'custom' ? 'd-flex' : 'd-none' }} flex-wrap gap-3 align-items-end">
                            <div class="filter-field">
                                <label for="from" class="form-label">From</label>
                                <input type="date" id="from" name="from"
                                       class="form-control form-control-lg"
                                       value="{{ $range['from_text'] }}">
                            </div>
                            <div class="filter-field">
                                <label for="to" class="form-label">To</label>
                                <input type="date" id="to" name="to"
                                       class="form-control form-control-lg"
                                       value="{{ $range['to_text'] }}">
                            </div>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn btn-ink btn-lg justify-content-center">
                                <i class="bi bi-funnel me-1 d-none d-sm-inline"></i>
                                Filter
                            </button>
                            <a href="{{ route('reports.index', ['currency' => $currency['code']]) }}"
                               class="btn btn-outline-ink btn-lg justify-content-center"
                               title="Reset to this month">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>

                        <div class="ms-auto">
                            <a href="{{ route('reports.export', $query) }}"
                               class="btn btn-saffron btn-lg justify-content-center"
                               title="Download the filtered invoices as CSV">
                                <i class="bi bi-download me-1"></i>
                                Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Summary cards (scoped to the selected currency + range) --------- --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">Total Revenue</span>
                            <i class="bi bi-cash-stack" style="color: var(--sage);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.55rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ $money($cards['revenue']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">Pending</span>
                            <i class="bi bi-clock-history" style="color: var(--saffron-dark);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.55rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ $money($cards['pending']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">Overdue</span>
                            <i class="bi bi-exclamation-triangle" style="color: var(--overdue);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.55rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ $money($cards['overdue']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">Invoices Created</span>
                            <i class="bi bi-receipt" style="color: var(--ink-soft);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.55rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ number_format($cards['invoices_count']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">Average Invoice</span>
                            <i class="bi bi-graph-up" style="color: var(--sage);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.55rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ $money($cards['average']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts — data fetched from the JSON endpoints via fetch(), so the
             charts refresh without a full page reload. ------------------------- --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-3">
                            <i class="bi bi-bar-chart-line me-2" style="color: var(--saffron-dark);"></i>
                            Revenue by month
                        </h2>
                        <div class="report-chart">
                            <canvas id="revenueMonthChart" role="img"
                                    aria-label="Bar chart of revenue by month for {{ $currency['name'] }}"></canvas>
                            <div class="report-chart__empty d-none" data-empty-for="revenueMonthChart">
                                <i class="bi bi-chart-line" aria-hidden="true"></i>
                                <span class="report-chart__empty-text">No data in this period.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-3">
                            <i class="bi bi-pie-chart-fill me-2" style="color: var(--sage);"></i>
                            Paid vs unpaid
                        </h2>
                        <div class="report-chart">
                            <canvas id="paidUnpaidChart" role="img"
                                    aria-label="Doughnut chart of paid, pending and overdue invoices in {{ $currency['name'] }}"></canvas>
                            <div class="report-chart__empty d-none" data-empty-for="paidUnpaidChart">
                                <i class="bi bi-pie-chart" aria-hidden="true"></i>
                                <span class="report-chart__empty-text">No invoices in this currency.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-3">
                            <i class="bi bi-people-fill me-2" style="color: var(--ink);"></i>
                            Top clients by revenue
                        </h2>
                        <div class="report-chart">
                            <canvas id="topClientsChart" role="img"
                                    aria-label="Horizontal bar chart of the top 5 clients by paid revenue in {{ $currency['name'] }}"></canvas>
                            <div class="report-chart__empty d-none" data-empty-for="topClientsChart">
                                <i class="bi bi-people" aria-hidden="true"></i>
                                <span class="report-chart__empty-text">No data in this period.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-3">
                            <i class="bi bi-bag-check-fill me-2" style="color: var(--sage);"></i>
                            Revenue by service
                        </h2>
                        <div class="report-chart">
                            <canvas id="serviceChart" role="img"
                                    aria-label="Bar chart of revenue by service for {{ $currency['name'] }}"></canvas>
                            <div class="report-chart__empty d-none" data-empty-for="serviceChart">
                                <i class="bi bi-bag" aria-hidden="true"></i>
                                <span class="report-chart__empty-text">No data in this period.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Chart area = fixed height wrapper; the empty-state text sits on top. */
    .report-chart { position: relative; height: 320px; }
    .report-chart canvas { max-width: 100%; }
    .report-chart__empty {
        position: absolute; inset: 0; z-index: 2;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .5rem; text-align: center; color: var(--ink-soft);
        background: rgba(255, 255, 255, .72);
        border-radius: var(--radius); font-size: .95rem;
    }
    .report-chart__empty i { font-size: 2rem; }
</style>
@endsection

@section('scripts')
<script>
    window.REPORT_FILTERS = @json($query);
</script>
<script>
(function () {
    'use strict';

    var COLORS = { navy: '#12202E', saffron: '#F2A33A', sage: '#3F6B50', red: '#B3372F' };

    var qs = new URLSearchParams(window.REPORT_FILTERS || {}).toString();

    /* Compact axis labels: 1000 → "1k", 2500 → "2.5k", 12m → "12m". */
    function compact(v) {
        var abs = Math.abs(v);
        var fmt = function (n, suffix) {
            return String(+n.toFixed(2)).replace('.00', '').replace(/(\.\d)0$/, '$1') + suffix;
        };
        if (abs >= 1000000) return fmt(v / 1000000, 'm');
        if (abs >= 1000) return fmt(v / 1000, 'k');
        return fmt(v, '');
    }

    /* Full-precision tooltip label, e.g. $1,250.00. */
    function money(v, symbol) {
        return symbol + Number(v).toLocaleString(undefined, {
            minimumFractionDigits: 2, maximumFractionDigits: 2,
        });
    }

    /* Shared axis options: start at 0, whole-number ticks, compact labels. */
    function valueAxis() {
        return {
            beginAtZero: true,
            ticks: { precision: 0, callback: function (value) { return compact(value); } },
        };
    }
    function scales(horizontal) {
        var grid = { display: false };
        return horizontal
            ? { x: valueAxis(), y: { grid: grid } }
            : { y: valueAxis(), x: { grid: grid } };
    }

    function fetchJson(url) {
        return fetch(url + (qs ? '?' + qs : '')).then(function (r) {
            if (!r.ok) throw new Error('request failed');
            return r.json();
        });
    }

    function emptyState(id, show, message) {
        var overlay = document.querySelector('[data-empty-for="' + id + '"]');
        if (!overlay) return;
        overlay.classList.toggle('d-none', !show);
        if (message) {
            var text = overlay.querySelector('.report-chart__empty-text');
            if (text) text.textContent = message;
        }
    }

    function load(id, url, makeConfig) {
        var canvas = document.getElementById(id);
        if (!canvas || typeof Chart === 'undefined') return;

        fetchJson(url)
            .then(function (data) {
                if (data.empty) { emptyState(id, true); return; }
                emptyState(id, false);
                new Chart(canvas.getContext('2d'), makeConfig(id, data));
            })
            .catch(function () { emptyState(id, true, 'Couldn\u2019t load this chart.'); });
    }

    /* Revenue by month — saffron vertical bar. */
    load('revenueMonthChart', '{{ route('reports.chart.revenue-by-month') }}', function (id, data) {
        return {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{ label: 'Revenue', data: data.data, backgroundColor: COLORS.saffron, borderRadius: 4 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: scales(false),
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return 'Revenue: ' + money(c.parsed.y, data.symbol); } } },
                },
            },
        };
    });

    /* Paid vs unpaid — doughnut (sage / saffron / red). */
    load('paidUnpaidChart', '{{ route('reports.chart.paid-unpaid') }}', function (id, data) {
        return {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: [COLORS.sage, COLORS.saffron, COLORS.red],
                    borderWidth: 2, borderColor: '#fff',
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
                    tooltip: {
                        callbacks: {
                            label: function (c) {
                                var total = c.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ' (' + Math.round((c.parsed / total) * 100) + '%)' : '';
                                return c.label + ': ' + c.parsed.toLocaleString() + pct;
                            },
                        },
                    },
                },
            },
        };
    });

    /* Top 5 clients — horizontal navy bar. */
    load('topClientsChart', '{{ route('reports.chart.top-clients') }}', function (id, data) {
        return {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{ label: 'Revenue', data: data.data, backgroundColor: COLORS.navy, borderRadius: 4 }],
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                scales: scales(true),
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return 'Revenue: ' + money(c.parsed.x, data.symbol); } } },
                },
            },
        };
    });

    /* Revenue by service — sage vertical bar, top 8. */
    load('serviceChart', '{{ route('reports.chart.revenue-by-service') }}', function (id, data) {
        return {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{ label: 'Revenue', data: data.data, backgroundColor: COLORS.sage, borderRadius: 4 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: scales(false),
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return 'Revenue: ' + money(c.parsed.y, data.symbol); } } },
                },
            },
        };
    });

    /* Only show the custom from/to date inputs when "Custom range" is picked. */
    var rangeSelect = document.getElementById('range');
    var customDates = document.getElementById('customDates');
    if (rangeSelect && customDates) {
        var sync = function () {
            customDates.classList.toggle('d-none', rangeSelect.value !== 'custom');
        };
        rangeSelect.addEventListener('change', sync);
        sync();
    }
})();
</script>
@endsection