@extends('layouts.app')

@section('title', 'Dashboard — InvoiceFlow')

@php
    /** @var array $stats */
    $symbol = $stats['currency_symbol'];
    $fmtCents = function (int $cents) use ($symbol): string {
        return $symbol . number_format($cents / 100, 2);
    };
@endphp

@section('content')
<!-- ==========================================================
     Dashboard — InvoiceFlow
     Greeting + 4 stat cards (count + amount in primary currency)
     + 5 most recent invoices.
     ========================================================== -->
<section class="dashboard-section py-5 py-lg-6">
    <div class="container">
        @include('partials.alerts')

        <!-- Header / Greeting ----------------------------------------- -->
        <div class="row align-items-center g-4 mb-5">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="avatar-ring" aria-hidden="true">
                        <span class="avatar-initials">{{ strtoupper(mb_substr($user->name, 0, 1) ?? 'U') }}</span>
                    </div>
                    <div>
                        <h1 class="h1 mb-0">
                            {{ $greeting }}, <span class="accent-text">{{ $user->name }}</span>
                        </h1>
                        <p class="ink-soft mb-0" style="font-size: 1rem; letter-spacing: 0;">
                            {{ $user->email }}
                        </p>
                    </div>
                </div>

                @if ($stats['mixed_currencies'])
                    <div class="alert alert-warning d-flex align-items-start gap-2 mt-3 mb-0"
                         style="background: rgba(242,163,58,0.12); border-color: rgba(242,163,58,0.35); color: var(--ink); border-radius: var(--radius);"
                         role="alert">
                        <i class="bi bi-info-circle-fill mt-1" style="color: var(--saffron-dark);"></i>
                        <div>
                            <strong>Note:</strong> You use multiple currencies
                            ({{ implode(', ', $stats['currencies_used']) }}).
                            The amounts shown below use your most-used currency,
                            <strong>{{ $stats['currency_name'] }} ({{ $stats['currency'] }})</strong>.
                            Each invoice's list row displays its own currency.
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('invoices.create') }}" class="btn btn-saffron btn-lg">
                    <i class="bi bi-plus-lg me-1"></i>
                    Create invoice
                </a>
            </div>
        </div>

        <!-- Stats grid ------------------------------------------------- -->
        <div class="row g-4 mb-5">
            <!-- Total invoices -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon stat-icon--total" aria-hidden="true">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <span class="badge rounded-pill bg-light ink-soft border">All</span>
                    </div>
                    <p class="stat-label mb-1">Total invoices</p>
                    <p class="stat-value mb-1">{{ number_format($stats['counts']['total']) }}</p>
                    <p class="mb-0" style="font-size: 0.9rem; color: var(--ink-soft); font-variant-numeric: tabular-nums;">
                        {{ $fmtCents($stats['amounts']['total']) }}
                    </p>
                </div>
            </div>

            <!-- Paid -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon stat-icon--paid" aria-hidden="true">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <span class="badge rounded-pill status-paid">Paid</span>
                    </div>
                    <p class="stat-label mb-1">Paid</p>
                    <p class="stat-value mb-1">{{ number_format($stats['counts']['paid']) }}</p>
                    <p class="mb-0" style="font-size: 0.9rem; color: var(--sage); font-weight: 600; font-variant-numeric: tabular-nums;">
                        {{ $fmtCents($stats['amounts']['paid']) }}
                    </p>
                </div>
            </div>

            <!-- Pending -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon stat-icon--pending" aria-hidden="true">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <span class="badge rounded-pill status-pending">Pending</span>
                    </div>
                    <p class="stat-label mb-1">Pending</p>
                    <p class="stat-value mb-1">{{ number_format($stats['counts']['pending']) }}</p>
                    <p class="mb-0" style="font-size: 0.9rem; color: var(--saffron-dark); font-weight: 600; font-variant-numeric: tabular-nums;">
                        {{ $fmtCents($stats['amounts']['pending']) }}
                    </p>
                </div>
            </div>

            <!-- Overdue -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon stat-icon--overdue" aria-hidden="true">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <span class="badge rounded-pill status-overdue">Overdue</span>
                    </div>
                    <p class="stat-label mb-1">Overdue</p>
                    <p class="stat-value mb-1">{{ number_format($stats['counts']['overdue']) }}</p>
                    <p class="mb-0" style="font-size: 0.9rem; color: var(--overdue); font-weight: 600; font-variant-numeric: tabular-nums;">
                        {{ $fmtCents($stats['amounts']['overdue']) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Recent invoices ------------------------------------------- -->
        <div class="row g-4">
            <div class="col-12">
                <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <h2 class="mb-1" style="font-size: 1.5rem;">Recent invoices</h2>
                        <p class="ink-soft mb-0">
                            Last 5 invoices. View all, filter and search on the invoices page.
                        </p>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-ink">
                        View all invoices <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                @if ($recentInvoices->isEmpty())
                    <div class="card dashboard-empty">
                        <div class="card-body">
                            <div class="row align-items-center g-4">
                                <div class="col-md-8">
                                    <h3 class="h4 mb-2">Create your first invoice</h3>
                                    <p class="ink-soft mb-3 mb-md-4" style="max-width: 560px;">
                                        No invoices yet. Create one in under a minute, send it to your client,
                                        and track its payment status right here.
                                    </p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('invoices.create') }}" class="btn btn-saffron">
                                            <i class="bi bi-file-earmark-plus me-1"></i>
                                            Create invoice
                                        </a>
                                        <a href="{{ route('clients.create') }}" class="btn btn-outline-ink">
                                            <i class="bi bi-person-plus me-1"></i>
                                            Add a client first
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="floating-hero-icon" aria-hidden="true">
                                        <i class="bi bi-send-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="font-size: 0.8125rem; color: var(--ink-soft); background: rgba(18,32,46,0.02);">
                                        <th scope="col" class="px-4 py-3">Invoice</th>
                                        <th scope="col" class="px-4 py-3">Client</th>
                                        <th scope="col" class="px-4 py-3">Issued</th>
                                        <th scope="col" class="px-4 py-3">Due</th>
                                        <th scope="col" class="px-4 py-3 text-end">Total</th>
                                        <th scope="col" class="px-4 py-3">Status</th>
                                        <th scope="col" class="px-4 py-3 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentInvoices as $inv)
                                        @php
                                            $status = $inv->display_status;
                                            $badgeClass = match ($status) {
                                                'paid'    => 'status-paid',
                                                'pending' => 'status-pending',
                                                'overdue' => 'status-overdue',
                                                default   => 'bg-light border ink-soft',
                                            };
                                            $badgeLabel = match ($status) {
                                                'paid'    => 'Paid',
                                                'pending' => 'Pending',
                                                'overdue' => 'Overdue',
                                                'draft'   => 'Draft',
                                                default   => ucfirst($status),
                                            };
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('invoices.show', $inv) }}"
                                                   class="fw-semibold ink text-decoration-none">
                                                    {{ $inv->invoice_number }}
                                                </a>
                                                <div class="text-sm ink-soft" style="font-size: 0.78rem;">
                                                    {{ $inv->items_count }} item{{ $inv->items_count === 1 ? '' : 's' }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ optional($inv->client)->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 ink-soft">
                                                {{ $inv->invoice_date->format('M j, Y') }}
                                            </td>
                                            <td class="px-4 py-3 {{ $status === 'overdue' ? '' : 'ink-soft' }}">
                                                {{ $inv->due_date ? $inv->due_date->format('M j, Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-end fw-semibold" style="font-variant-numeric: tabular-nums;">
                                                {{ $inv->total_formatted }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="badge rounded-pill {{ $badgeClass }}">
                                                    {{ $badgeLabel }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('invoices.show', $inv) }}"
                                                       class="btn btn-sm btn-outline-ink">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                    <a href="{{ route('invoices.download', $inv) }}"
                                                       target="_blank" rel="noopener"
                                                       class="btn btn-sm"
                                                       style="background: color-mix(in srgb, var(--saffron) 18%, #fff); color: var(--saffron-dark); border: 1px solid color-mix(in srgb, var(--saffron) 40%, #fff);">
                                                        <i class="bi bi-filetype-pdf me-1"></i> PDF
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
