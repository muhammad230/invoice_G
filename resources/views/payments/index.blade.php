@extends('layouts.dashboard')

@section('title', 'Payments — InvoiceFlow')

@php
    /* Money formatter for the selected currency (used by the summary cards). */
    $symbol = $currency['symbol'];
    $money  = function (int $cents) use ($symbol): string {
        return $symbol . number_format($cents / 100, 2);
    };

    $currencyOptions = $currency['available'] ?? [];
@endphp

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        {{-- Header + currency selector ------------------------------------ --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-cash-coin me-1"></i>
                    Payments
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">Payments received</h1>
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
                                   href="{{ route('payments.index', ['currency' => $opt['code']]) }}">
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
                      title="All your payments are in {{ $currency['name'] }}">
                    <i class="bi bi-coins" aria-hidden="true"></i>
                    <span class="dash-currency-btn__code">{{ $currency['code'] }}</span>
                    <span class="dash-currency-btn__sym">{{ $currency['symbol'] }}</span>
                    <span class="dash-currency-btn__name">{{ $currency['name'] }}</span>
                </span>
            @endif
        </div>

        {{-- Summary cards (scoped to the selected currency) --------------- --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">Total Received</span>
                            <i class="bi bi-cash-stack" style="color: var(--sage);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.6rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ $money($summary['total_cents']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">This Month</span>
                            <i class="bi bi-calendar-check" style="color: var(--saffron-dark);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.6rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ $money($summary['month_cents']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">Payments</span>
                            <i class="bi bi-receipt" style="color: var(--ink-soft);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.6rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ number_format($summary['count']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="ink-soft" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">Average</span>
                            <i class="bi bi-graph-up" style="color: var(--sage);" aria-hidden="true"></i>
                        </div>
                        <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.6rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums;">
                            {{ $money($summary['average_cents']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search + filter bar ------------------------------------------- --}}
        <form method="GET" action="{{ route('payments.index') }}" class="mb-4">
            <input type="hidden" name="currency" value="{{ $currency['code'] }}">
            <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap gap-3 align-items-end">
                        <div class="filter-field filter-field--search">
                            <label for="q" class="form-label">Search</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" aria-hidden="true">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="q" name="q"
                                       class="form-control form-control-lg"
                                       value="{{ $filters['q'] }}"
                                       placeholder="Invoice #, client or reference…">
                            </div>
                        </div>
                        <div class="filter-field">
                            <label for="method" class="form-label">Method</label>
                            <select id="method" name="method" class="form-select form-select-lg">
                                <option value="" @selected($filters['method'] === '')>All methods</option>
                                @foreach ($methods as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['method'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-ink btn-lg justify-content-center">
                                <i class="bi bi-funnel me-1 d-none d-sm-inline"></i>
                                Filter
                            </button>
                            <a href="{{ route('payments.index', ['currency' => $currency['code']]) }}"
                               class="btn btn-outline-ink btn-lg justify-content-center"
                               title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Payments table / empty state ---------------------------------- --}}
        @if ($payments->isEmpty())
            <div class="card dashboard-empty">
                <div class="card-body text-center py-5">
                    <div class="floating-hero-icon mb-4" style="width: 96px; height: 96px; font-size: 2rem;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h2 class="h3 mb-2">No payments yet</h2>
                    <p class="ink-soft mb-4 mx-auto" style="max-width: 460px;">
                        No payments match the current filters. Mark an invoice as paid and it
                        will show up here.
                    </p>
                    <a href="{{ route('invoices.index') }}" class="btn btn-saffron btn-lg">
                        <i class="bi bi-receipt-cutoff me-1"></i>
                        Go to invoices
                    </a>
                </div>
            </div>
        @else
            <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 list-table">
                        <thead>
                            <tr style="font-size: 0.8125rem; color: var(--ink-soft); background: rgba(18,32,46,0.02);">
                                <th scope="col">Paid on</th>
                                <th scope="col">Invoice</th>
                                <th scope="col" class="d-none d-md-table-cell">Client</th>
                                <th scope="col">Method</th>
                                <th scope="col" class="d-none d-lg-table-cell">Reference</th>
                                <th scope="col" class="text-end">Amount</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td class="ink-soft">
                                        {{ $payment->paid_on->format('M j, Y') }}
                                    </td>
                                    <td>
                                        @if ($payment->invoice)
                                            <a href="{{ route('invoices.show', $payment->invoice) }}"
                                               class="fw-semibold ink text-decoration-none">
                                                {{ $payment->invoice->invoice_number }}
                                            </a>
                                        @else
                                            <span class="ink-soft">—</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        {{ optional(optional($payment->invoice)->client)->name ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-light border ink-soft">
                                            {{ $payment->method_label }}
                                        </span>
                                    </td>
                                    <td class="d-none d-lg-table-cell ink-soft">
                                        {{ $payment->reference ?: '—' }}
                                    </td>
                                    {{-- Each row is formatted in its own invoice's currency. --}}
                                    <td class="text-end fw-semibold" style="font-variant-numeric: tabular-nums;">
                                        {{ $payment->amount_formatted }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            @if ($payment->invoice)
                                                <a href="{{ route('invoices.show', $payment->invoice) }}"
                                                   class="btn btn-sm btn-outline-ink"
                                                   title="View invoice">
                                                    <i class="bi bi-eye"></i><span class="d-none d-sm-inline ms-1">View</span>
                                                </a>
                                            @endif
                                            <form method="POST"
                                                  action="{{ route('payments.destroy', $payment) }}"
                                                  class="d-inline m-0"
                                                  onsubmit="return confirm('Undo this payment? The invoice will go back to pending.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm"
                                                        style="background: color-mix(in srgb, var(--overdue) 12%, #fff); color: var(--overdue); border: 1px solid color-mix(in srgb, var(--overdue) 30%, #fff);"
                                                        title="Undo this payment">
                                                    <i class="bi bi-arrow-counterclockwise"></i><span class="d-none d-sm-inline ms-1">Undo</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
