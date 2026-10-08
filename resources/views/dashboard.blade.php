@extends('layouts.dashboard')

@section('title', 'Dashboard · InvoiceFlow')

@php
    /* Money + delta formatting helpers */
    $symbol = $currency['symbol'];
    $money = function (int $cents) use ($symbol): string {
        return $symbol . number_format($cents / 100, 2);
    };

    $deltaBadge = function (array $delta): string {
        if ($delta['direction'] === 'new') {
            return '<span class="dash-stat__delta dash-stat__delta--new"><i class="bi bi-sparkles"></i> New</span>';
        }
        if ($delta['pct'] === null) {
            return '<span class="dash-stat__delta dash-stat__delta--flat"><i class="bi bi-dash"></i> No data</span>';
        }
        $pct = abs($delta['pct']);
        if ($delta['direction'] === 'up') {
            return '<span class="dash-stat__delta dash-stat__delta--up"><i class="bi bi-arrow-up-short"></i> ' . $pct . '% <small>vs prev 30d</small></span>';
        }
        if ($delta['direction'] === 'down') {
            return '<span class="dash-stat__delta dash-stat__delta--down"><i class="bi bi-arrow-down-short"></i> ' . $pct . '% <small>vs prev 30d</small></span>';
        }
        /* flat, but with a real 0% change → show it instead of "No data" */
        return '<span class="dash-stat__delta dash-stat__delta--flat"><i class="bi bi-dash"></i> ' . $pct . '% <small>vs prev 30d</small></span>';
    };

    $statIcon = function (string $tone): string {
        return match ($tone) {
            'total'   => 'dash-stat__icon--total',
            'paid'    => 'dash-stat__icon--paid',
            'pending' => 'dash-stat__icon--pending',
            'overdue' => 'dash-stat__icon--overdue',
            default   => 'dash-stat__icon--total',
        };
    };

    /* Empty-state check */
    $hasAnyInvoices = ($stats['total_all_count'] ?? 0) > 0;
@endphp

@section('content')
    {{-- =====================================================================
         GREETING ROW — left: Good morning {name}, right: today date card
         ===================================================================== --}}
    <div class="dash-greet row g-3 align-items-center mb-3">
        <div class="col-12 col-lg-8">
            <h1 class="dash-greet__salutation">
                {{ $greeting }}, <span class="accent">{{ $user->name }}</span> 👋
            </h1>
            <p class="dash-greet__subtitle">
                Here&rsquo;s what&rsquo;s happening with your invoices today.
            </p>
            @if (!empty($currency['mixed']))
                <div class="alert alert-sm d-inline-flex align-items-center gap-2 mt-2 mb-0"
                     style="background: rgba(242,163,58,0.12); border: 1px solid rgba(242,163,58,0.35); color: var(--ink); border-radius: 9px; font-size: 0.8rem; padding: 0.5rem 0.75rem;">
                    <i class="bi bi-info-circle-fill" style="color: var(--saffron-dark);"></i>
                    <span>
                        You use multiple currencies (<strong>{{ implode(', ', $currency['list']) }}</strong>).
                        Aggregates shown use your most-used currency:
                        <strong>{{ $currency['name'] }} ({{ $currency['code'] }})</strong>.
                    </span>
                </div>
            @endif
        </div>
        <div class="col-12 col-lg-4 text-lg-end">
            <div class="dash-date-card">
                <span class="dash-date-card__icon" aria-hidden="true"><i class="bi bi-calendar-date"></i></span>
                <span class="d-flex flex-column align-items-start">
                    <span class="dash-date-card__dow">
                        {{ $today->format('l') }}
                    </span>
                    <span class="dash-date-card__date">
                        {{ $today->format('F j, Y') }}
                    </span>
                </span>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         4 STAT CARDS (2x2 on tablet, 4 on desktop, 1 column on mobile)
         Stat 0=total invoices, 1=revenue paid, 2=pending, 3=overdue
         ===================================================================== --}}
    <div class="row g-3 mb-3">
        @foreach ([$stats[0], $stats[1], $stats[2], $stats[3]] as $s)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dash-stat">
                    <div class="dash-stat__top">
                        <span class="dash-stat__icon {{ $statIcon($s['iconTone']) }}" aria-hidden="true">
                            <i class="bi {{ $s['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="dash-stat__label">{{ $s['label'] }}</p>
                    <p class="dash-stat__value">
                        @if ($s['amount'])
                            {{ $money($s['value']) }}
                        @else
                            {{ number_format($s['value']) }}
                        @endif
                    </p>
                    {!! $deltaBadge($s['delta']) !!}
                </div>
            </div>
        @endforeach
    </div>

    {{-- =====================================================================
         MAIN GRID — LEFT (8 col): line chart + recent invoices table
                    RIGHT (4 col): doughnut chart + CTA/Quick links/Activity
         ===================================================================== --}}
    <div class="row g-3">
        {{-- LEFT COLUMN (charts + table) ------------------------------------ --}}
        <div class="col-12 col-xl-8 order-1">

            {{-- Line chart: paid revenue over time -------------------------- --}}
            <div class="card panel-card dash-chart-card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h2 class="card-title">Invoice Overview</h2>
                        <p class="card-subtitle">Paid revenue over time</p>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="dash-chart-controls" role="tablist" aria-label="Chart time range">
                            @foreach (['7d' => '7D', '30d' => '30D', '3m' => '3M', '1y' => '1Y'] as $range => $label)
                                <button type="button"
                                        class="dash-chart-btn {{ $range === '30d' ? 'is-active' : '' }}"
                                        data-range="{{ $range }}"
                                        role="tab"
                                        aria-selected="{{ $range === '30d' ? 'true' : 'false' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <span class="chart-range-total" id="chartRangeTotal">
                            Paid <strong>{{ $money($chart['total_paid_cents']) }}</strong>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if (!$hasAnyInvoices)
                        <div class="chart-empty">
                            <div class="chart-empty__icon"><i class="bi bi-graph-up-arrow"></i></div>
                            <h3 class="chart-empty__title">No invoices yet</h3>
                            <p class="chart-empty__subtitle">As you create invoices and mark them paid, this chart will show your revenue by day.</p>
                            <a href="{{ route('invoices.create') }}" class="btn btn-saffron">
                                <i class="bi bi-plus-lg me-1"></i> Create your first invoice
                            </a>
                        </div>
                    @else
                        <div style="position: relative; height: 250px;">
                            <canvas id="lineChart"
                                    data-chart-url="{{ route('dashboard.chart') }}"
                                    data-labels='@json($chart['labels'])'
                                    data-values='@json($chart['data'])'>
                            </canvas>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent invoices table with actions dropdown ----------------- --}}
            <div class="card panel-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h2 class="card-title">Recent Invoices</h2>
                        <p class="card-subtitle">Last 5 invoices across all statuses</p>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-ink btn-sm">
                        View all <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="card-body p-0">
                    @if ($recentInvoices->isEmpty())
                        <div class="chart-empty py-5">
                            <div class="chart-empty__icon"><i class="bi bi-receipt-cutoff"></i></div>
                            <h3 class="chart-empty__title">No invoices yet</h3>
                            <p class="chart-empty__subtitle">Send your first invoice to a client — it takes less than a minute.</p>
                            <a href="{{ route('invoices.create') }}" class="btn btn-saffron">
                                <i class="bi bi-file-earmark-plus me-1"></i> Create invoice
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr style="background: rgba(18,32,46,0.02); font-size: 0.75rem; color: var(--ink-soft);">
                                        <th scope="col" class="px-3 py-2">Invoice</th>
                                        <th scope="col" class="px-3 py-2">Client</th>
                                        <th scope="col" class="px-3 py-2">Date</th>
                                        <th scope="col" class="px-3 py-2 text-end">Amount</th>
                                        <th scope="col" class="px-3 py-2">Status</th>
                                        <th scope="col" class="px-3 py-2 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentInvoices as $inv)
                                        @php
                                            $s = $inv->display_status;
                                            $badgeClass = match($s) {
                                                'paid'    => 'status-paid',
                                                'pending' => 'status-pending',
                                                'overdue' => 'status-overdue',
                                                default   => 'bg-light border ink-soft',
                                            };
                                            $badgeLabel = match($s) {
                                                'paid'    => 'Paid',
                                                'pending' => 'Pending',
                                                'overdue' => 'Overdue',
                                                'draft'   => 'Draft',
                                                default   => ucfirst($s),
                                            };
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-2">
                                                <a href="{{ route('invoices.show', $inv) }}" class="fw-semibold ink text-decoration-none" style="font-size: 0.88rem;">
                                                    {{ $inv->invoice_number }}
                                                </a>
                                            </td>
                                            <td class="px-3 py-2" style="font-size: 0.86rem;">
                                                {{ optional($inv->client)->name ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 ink-soft" style="font-size: 0.82rem;">
                                                {{ $inv->invoice_date->format('M j, Y') }}
                                            </td>
                                            <td class="px-3 py-2 text-end fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.88rem;">
                                                {{ $inv->total_formatted }}
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="badge rounded-pill {{ $badgeClass }}"
                                                      style="font-size: 0.7rem; padding: 0.28rem 0.6rem;">
                                                    {{ $badgeLabel }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-end">
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn btn-sm btn-outline-ink dropdown-toggle"
                                                            type="button"
                                                            id="dd_inv_{{ $inv->id }}"
                                                            data-bs-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end dash-actions-menu shadow-sm"
                                                        aria-labelledby="dd_inv_{{ $inv->id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('invoices.show', $inv) }}">
                                                                <i class="bi bi-eye"></i> View
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('invoices.edit', $inv) }}">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('invoices.pdf', $inv) }}" target="_blank" rel="noopener">
                                                                <i class="bi bi-filetype-pdf"></i> Download PDF
                                                            </a>
                                                        </li>
                                                        @if ($inv->status !== \App\Models\Invoice::STATUS_PAID)
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form method="POST" action="{{ route('invoices.mark-paid', $inv) }}"
                                                                      class="m-0"
                                                                      onsubmit="event.preventDefault(); this.submit();">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item" style="color: var(--sage);">
                                                                        <i class="bi bi-check-circle" style="color: var(--sage) !important;"></i> Mark as Paid
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (desktop) / BELOW (mobile) ------------------------- --}}
        <div class="col-12 col-xl-4 order-2">

            {{-- Quick actions: Create Invoice + Quick links ----------------- --}}
            <div class="card panel-card mb-3">
                <div class="card-body">
                    <a href="{{ route('invoices.create') }}"
                       class="btn btn-saffron dash-cta-btn">
                        <i class="bi bi-file-earmark-plus"></i>
                        Create New Invoice
                    </a>

                    <div class="mt-3 pt-2" style="border-top: 1px dashed rgba(18,32,46,0.08);">
                        <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                            <span class="fw-semibold" style="color: var(--ink); font-size: 0.88rem;">
                                Quick links
                            </span>
                        </div>
                        <ul class="dash-quick-links mt-1">
                            <li>
                                <a href="{{ route('clients.create') }}">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="bi bi-person-plus-fill"></i>
                                        Add Client
                                    </span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('products.create') }}">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="bi bi-bag-plus-fill"></i>
                                        Add Product / Service
                                    </span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('invoices.index') }}">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="bi bi-receipt-cutoff"></i>
                                        View All Invoices
                                    </span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Payment Status doughnut chart ------------------------------- --}}
            <div class="card panel-card mb-3">
                <div class="card-header">
                    <h2 class="card-title">Payment Status</h2>
                    <p class="card-subtitle">All invoices across statuses</p>
                </div>
                <div class="card-body">
                    @if ($donut['total'] === 0)
                        <div class="chart-empty py-4">
                            <div class="chart-empty__icon"><i class="bi bi-pie-chart-fill"></i></div>
                            <h3 class="chart-empty__title">No data</h3>
                            <p class="chart-empty__subtitle" style="margin-bottom: 0;">
                                Once you have invoices, this chart shows the breakdown between paid, pending, and overdue.
                            </p>
                        </div>
                    @else
                        <div class="dash-donut-wrap">
                            <canvas id="doughnutChart"
                                    data-labels='@json($donut['labels'])'
                                    data-values='@json($donut['counts'])'
                                    data-colors='@json($donut['colors'])'
                                    style="width: 100%; height: 220px;">
                            </canvas>
                            <div class="dash-donut-center" aria-hidden="true">
                                <span class="dash-donut-center__label">Total</span>
                                <span class="dash-donut-center__total">{{ number_format($donut['total']) }}</span>
                            </div>
                        </div>

                        {{-- Legend: rows with count + % --}}
                        <div class="dash-legend">
                            @foreach ($donut['labels'] as $i => $label)
                                @php
                                    $dotColor = $donut['colors'][$i] ?? '#999';
                                    $count    = $donut['counts'][$i] ?? 0;
                                    $pct      = $donut['percents'][$i] ?? 0;
                                @endphp
                                <div class="dash-legend__row">
                                    <span class="dash-legend__swatch">
                                        <span class="dash-legend__dot" style="background: {{ $dotColor }};"></span>
                                        {{ $label }}
                                    </span>
                                    <span class="dash-legend__counts">
                                        <span class="dash-legend__count">{{ number_format($count) }}</span>
                                        <span class="d-block dash-legend__pct">{{ $pct }}%</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent activity list --------------------------------------- --}}
            <div class="card panel-card">
                <div class="card-header">
                    <h2 class="card-title">Recent Activity</h2>
                    <p class="card-subtitle">Latest invoices and client updates</p>
                </div>
                <div class="card-body">
                    @if (count($activity) === 0)
                        <div class="chart-empty py-4">
                            <div class="chart-empty__icon"><i class="bi bi-clock-history"></i></div>
                            <h3 class="chart-empty__title">No activity yet</h3>
                            <p class="chart-empty__subtitle" style="margin-bottom: 0;">
                                Start by adding a client or creating an invoice — your activity will appear here.
                            </p>
                        </div>
                    @else
                        <ul class="dash-activity">
                            @foreach ($activity as $evt)
                                @php
                                    $toneClass = match($evt['tone']) {
                                        'paid'    => 'dash-activity__icon--paid',
                                        'saffron' => 'dash-activity__icon--client',
                                        'overdue' => 'dash-activity__icon--overdue',
                                        default   => 'dash-activity__icon--created',
                                    };
                                @endphp
                                <li class="dash-activity__item">
                                    <span class="dash-activity__icon {{ $toneClass }}" aria-hidden="true">
                                        <i class="bi {{ $evt['icon'] }}"></i>
                                    </span>
                                    <span class="dash-activity__body">
                                        <p class="dash-activity__text">
                                            <a href="{{ $evt['action'] }}">{!! $evt['text'] !!}</a>
                                        </p>
                                        <p class="dash-activity__time">
                                            <i class="bi bi-clock me-1" style="opacity: 0.6;"></i>
                                            {{ $evt['time'] }}
                                        </p>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    // ============================================================
    // 1) LINE CHART — paid revenue over time
    // ============================================================
    const lineCanvas = document.getElementById('lineChart');
    let lineChartInstance = null;

    function buildLineChart(labels, values) {
        if (!lineCanvas) return;
        const ctx = lineCanvas.getContext('2d');
        if (lineChartInstance) lineChartInstance.destroy();

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(242, 163, 58, 0.28)');
        gradient.addColorStop(1, 'rgba(242, 163, 58, 0.02)');

        lineChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Paid revenue',
                    data: values,
                    borderColor: '#F2A33A',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#F2A33A',
                    pointHoverBorderColor: '#FFFFFF',
                    pointHoverBorderWidth: 2,
                    fill: true,
                    tension: 0.35,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#12202E',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                const v = Number(context.parsed.y || 0);
                                const sign = '{{ $symbol }}';
                                return sign + v.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#4A5866',
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8,
                            font: { size: 11 }
                        },
                        border: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(18, 32, 46, 0.05)',
                        },
                        ticks: {
                            color: '#4A5866',
                            font: { size: 11 },
                            callback: function(v) {
                                return '{{ $symbol }}' + Number(v).toFixed(0);
                            }
                        },
                        border: { display: false, dash: [4, 4] }
                    }
                }
            }
        });
    }

    // Initial render from data props (server-rendered 30D default)
    if (lineCanvas) {
        const initLabels = JSON.parse(lineCanvas.getAttribute('data-labels') || '[]');
        const initValues = JSON.parse(lineCanvas.getAttribute('data-values') || '[]');
        buildLineChart(initLabels, initValues);

        // Wire range buttons → fetch JSON from /dashboard/chart?range=xx without refresh
        const chartUrl  = lineCanvas.getAttribute('data-chart-url');
        const rangeBtns = document.querySelectorAll('.dash-chart-btn');
        const totalEl   = document.getElementById('chartRangeTotal');

        function setActiveRange(btn) {
            rangeBtns.forEach(b => {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-selected', 'true');
        }

        rangeBtns.forEach(btn => {
            btn.addEventListener('click', async function () {
                setActiveRange(btn);
                if (!chartUrl) return;
                try {
                    const range = btn.getAttribute('data-range') || '30d';
                    const resp = await fetch(chartUrl + '?range=' + encodeURIComponent(range), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();
                    buildLineChart(data.labels || [], data.data || []);
                    if (totalEl && data.total_for_range) {
                        totalEl.innerHTML = 'Paid <strong>' + data.total_for_range + '</strong>';
                    }
                } catch (err) {
                    console.error('[dashboard chart]', err);
                }
            });
        });
    }

    // ============================================================
    // 2) DOUGHNUT CHART — payment status counts
    // ============================================================
    const donutCanvas = document.getElementById('doughnutChart');
    if (donutCanvas) {
        const labels = JSON.parse(donutCanvas.getAttribute('data-labels') || '[]');
        const values = JSON.parse(donutCanvas.getAttribute('data-values') || '[]');
        const colors = JSON.parse(donutCanvas.getAttribute('data-colors') || '["#3F6B50","#F2A33A","#B3372F"]');

        const donutChart = new Chart(donutCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderColor: '#FFFFFF',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#12202E',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(ctx) {
                                const total = (ctx.chart.data.datasets[0].data || []).reduce((a, b) => a + b, 0);
                                const val = ctx.parsed || 0;
                                const pct = total > 0 ? Math.round((val / total) * 1000) / 10 : 0;
                                return `${ctx.label}: ${val} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
})();
</script>
@endsection
