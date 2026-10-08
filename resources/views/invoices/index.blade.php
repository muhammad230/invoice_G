@extends('layouts.dashboard')

@section('title', 'Invoices — InvoiceFlow')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        {{-- Header --------------------------------------------------------- --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-file-earmark-text me-1"></i>
                    Invoices
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">Your invoices</h1>
            </div>
            <a href="{{ route('invoices.create') }}" class="btn btn-saffron btn-lg">
                <i class="bi bi-plus-lg me-1"></i>
                New invoice
            </a>
        </div>

        {{-- Search + filter bar ------------------------------------------- --}}
        <form method="GET" action="{{ route('invoices.index') }}" class="mb-4">
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
                                       placeholder="Search by invoice # or client name…">
                            </div>
                        </div>
                        <div class="filter-field">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select form-select-lg">
                                <option value="" @selected($filters['status'] === '')>All statuses</option>
                                <option value="draft"   @selected($filters['status'] === 'draft')>Draft</option>
                                <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                                <option value="overdue" @selected($filters['status'] === 'overdue')>Overdue</option>
                                <option value="paid"    @selected($filters['status'] === 'paid')>Paid</option>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-ink btn-lg justify-content-center">
                                <i class="bi bi-funnel me-1 d-none d-sm-inline"></i>
                                Filter
                            </button>
                            <a href="{{ route('invoices.index') }}"
                               class="btn btn-outline-ink btn-lg justify-content-center"
                               title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Invoices table / empty state ---------------------------------- --}}
        @if ($invoices->isEmpty())
            <div class="card dashboard-empty">
                <div class="card-body text-center py-5">
                    <div class="floating-hero-icon mb-4" style="width: 96px; height: 96px; font-size: 2rem;">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <h2 class="h3 mb-2">No invoices yet</h2>
                    <p class="ink-soft mb-4 mx-auto" style="max-width: 460px;">
                        No invoices match the current filters. Clear the search or create
                        your first invoice to get started.
                    </p>
                    <a href="{{ route('invoices.create') }}" class="btn btn-saffron btn-lg">
                        <i class="bi bi-plus-lg me-1"></i>
                        Create your first invoice
                    </a>
                </div>
            </div>
        @else
            <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 list-table">
                        <thead>
                            <tr style="font-size: 0.8125rem; color: var(--ink-soft); background: rgba(18,32,46,0.02);">
                                <th scope="col">Invoice</th>
                                <th scope="col">Client</th>
                                <th scope="col" class="d-none d-md-table-cell">Date</th>
                                <th scope="col" class="d-none d-md-table-cell">Due</th>
                                <th scope="col" class="text-end">Total</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                @php
                                    $status = $invoice->display_status;
                                    $statusBadgeClass = match ($status) {
                                        'paid'    => 'status-paid',
                                        'pending' => 'status-pending',
                                        'overdue' => 'status-overdue',
                                        default   => 'bg-light border ink-soft',
                                    };
                                    $statusLabel = match ($status) {
                                        'paid'    => 'Paid',
                                        'pending' => 'Pending',
                                        'overdue' => 'Overdue',
                                        'draft'   => 'Draft',
                                        default   => ucfirst($status),
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice) }}"
                                           class="fw-semibold ink text-decoration-none">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                        <div class="ink-soft" style="font-size: 0.78rem;">
                                            {{ $invoice->items->count() }} item{{ $invoice->items->count() === 1 ? '' : 's' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ optional($invoice->client)->name ?? '—' }}</div>
                                        @if (optional($invoice->client)->email)
                                            <div class="ink-soft" style="font-size: 0.78rem;">
                                                {{ $invoice->client->email }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell ink-soft">
                                        {{ $invoice->invoice_date->format('M j, Y') }}
                                    </td>
                                    <td class="d-none d-md-table-cell {{ $status === 'overdue' ? '' : 'ink-soft' }}">
                                        @if ($invoice->due_date)
                                            {{ $invoice->due_date->format('M j, Y') }}
                                        @else
                                            <span class="ink-soft">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold" style="font-variant-numeric: tabular-nums;">
                                        {{ $invoice->total_formatted }}
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill {{ $statusBadgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a href="{{ route('invoices.show', $invoice) }}"
                                               class="btn btn-sm btn-outline-ink"
                                               title="View invoice">
                                                <i class="bi bi-eye"></i><span class="d-none d-sm-inline ms-1">View</span>
                                            </a>
                                            <a href="{{ route('invoices.pdf', $invoice) }}"
                                               class="btn btn-sm"
                                               style="background: color-mix(in srgb, var(--saffron) 18%, #fff); color: var(--saffron-dark); border: 1px solid color-mix(in srgb, var(--saffron) 40%, #fff);"
                                               target="_blank" rel="noopener"
                                               title="Download PDF">
                                                <i class="bi bi-filetype-pdf"></i><span class="d-none d-sm-inline ms-1">PDF</span>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('invoices.duplicate', $invoice) }}"
                                                  class="d-inline m-0"
                                                  onsubmit="return confirm('Duplicate invoice {{ $invoice->invoice_number }}? A new invoice number will be generated.');">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-ink"
                                                        title="Duplicate this invoice">
                                                    <i class="bi bi-files"></i><span class="d-none d-sm-inline ms-1">Copy</span>
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
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
