@extends('layouts.app')

@section('title', $invoice->invoice_number . ' — InvoiceFlow')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        {{-- Header / Actions ------------------------------------------------- --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-file-earmark-text me-1"></i>
                    Invoice
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">
                    {{ $invoice->invoice_number }}
                </h1>
                <p class="ink-soft mb-0 mt-1">
                    Issued {{ $invoice->invoice_date->format('F j, Y') }}
                    @if ($invoice->due_date)
                        · Due {{ $invoice->due_date->format('F j, Y') }}
                    @endif
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
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
                <span class="badge rounded-pill {{ $statusBadgeClass }}" style="font-size: 0.9rem; padding: 0.5rem 0.85rem;">
                    {{ $statusLabel }}
                </span>

                <a href="{{ route('invoices.pdf', $invoice) }}"
                   class="btn btn-saffron"
                   target="_blank" rel="noopener">
                    <i class="bi bi-filetype-pdf me-1"></i> Download PDF
                </a>

                <form method="POST"
                      action="{{ route('invoices.duplicate', $invoice) }}"
                      class="d-inline m-0"
                      onsubmit="return confirm('Duplicate invoice {{ $invoice->invoice_number }}? A new invoice number will be generated.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-ink">
                        <i class="bi bi-files me-1"></i> Duplicate
                    </button>
                </form>

                @if ($invoice->status !== \App\Models\Invoice::STATUS_PAID)
                    <form method="POST"
                          action="{{ route('invoices.mark-paid', $invoice) }}"
                          class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn btn-ink">
                            <i class="bi bi-check-circle me-1"></i> Mark as paid
                        </button>
                    </form>
                @endif

                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-ink">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>

                <form method="POST"
                      action="{{ route('invoices.destroy', $invoice) }}"
                      class="d-inline m-0"
                      onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn"
                            style="background: color-mix(in srgb, var(--overdue) 12%, #fff); color: var(--overdue); border: 1px solid color-mix(in srgb, var(--overdue) 30%, #fff);">
                        <i class="bi bi-trash3 me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        @include('partials.alerts')

        {{-- Invoice view --------------------------------------------------- --}}
        <div class="row g-4 g-lg-5">
            {{-- Sidebar: meta + totals ---------------------------------------- --}}
            <div class="col-lg-4 order-lg-2">
                <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4">
                        <h2 class="mb-3" style="font-size: 1.125rem;">
                            <i class="bi bi-receipt me-2" style="color: var(--saffron-dark);"></i>
                            Summary
                        </h2>
                        <dl class="row mb-0">
                            <dt class="col-sm-6 ink-soft">Client</dt>
                            <dd class="col-sm-6 fw-semibold mb-2">
                                {{ optional($invoice->client)->name ?? '—' }}
                            </dd>

                            <dt class="col-sm-6 ink-soft">Items</dt>
                            <dd class="col-sm-6 mb-2">{{ $invoice->items->count() }}</dd>

                            <dt class="col-sm-6 ink-soft">Currency</dt>
                            <dd class="col-sm-6 mb-2">{{ $invoice->currency }}</dd>

                            <dt class="col-sm-6 ink-soft">Subtotal</dt>
                            <dd class="col-sm-6 mb-2" style="font-variant-numeric: tabular-nums;">
                                {{ $invoice->subtotal_formatted }}
                            </dd>

                            <dt class="col-sm-6 ink-soft">
                                Tax @if ((float) $invoice->tax_percent > 0)({{ rtrim(rtrim(number_format((float)$invoice->tax_percent, 2), '0'), '.') }}%)@endif
                            </dt>
                            <dd class="col-sm-6 mb-2" style="font-variant-numeric: tabular-nums;">
                                {{ $invoice->tax_amount_formatted }}
                            </dd>

                            <dt class="col-sm-6 ink-soft">Discount</dt>
                            <dd class="col-sm-6 mb-2" style="font-variant-numeric: tabular-nums;">
                                −{{ $invoice->discount_formatted }}
                            </dd>

                            <dt class="col-sm-6 ink-soft pt-2" style="border-top: 1px solid rgba(18,32,46,0.08);">
                                <strong style="color: var(--ink);">Total</strong>
                            </dt>
                            <dd class="col-sm-6 mb-0 pt-2"
                                style="border-top: 1px solid rgba(18,32,46,0.08); font-variant-numeric: tabular-nums;">
                                <strong style="font-family: 'Fraunces', Georgia, serif; font-size: 1.15rem; color: var(--ink);">
                                    {{ $invoice->total_formatted }}
                                </strong>
                            </dd>
                        </dl>
                    </div>
                </div>

                @if ($invoice->notes)
                    <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <h2 class="mb-3" style="font-size: 1.125rem;">
                                <i class="bi bi-chat-left-text me-2" style="color: var(--sage);"></i>
                                Notes
                            </h2>
                            <p class="mb-0 ink" style="white-space: pre-wrap;">
                                {{ $invoice->notes }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Preview-like invoice sheet ------------------------------------ --}}
            <div class="col-lg-8 order-lg-1">
                <div id="previewSheet"
                     class="bg-white"
                     style="
                        border-radius: var(--radius);
                        box-shadow: var(--shadow-lg);
                        padding: 2.25rem 2rem;
                        min-height: 640px;
                        color: var(--ink);
                        font-family: 'Inter', sans-serif;
                        font-size: 0.9375rem;
                     ">

                    {{-- Header: Business | Invoice meta --}}
                    <div class="row mb-4">
                        <div class="col-7">
                            @if ($invoice->logo_data)
                                <div class="mb-2" style="max-height: 60px;">
                                    <img src="{{ $invoice->logo_data }}" alt="Business logo"
                                         style="max-height: 60px; max-width: 180px; display: block;">
                                </div>
                            @endif
                            <div class="fw-bold mb-1" style="font-family: 'Fraunces', Georgia, serif; font-size: 1.125rem; color: var(--ink);">
                                {{ $invoice->business_name ?? 'Your business' }}
                            </div>
                            @if ($invoice->business_email)
                                <div class="mb-1 ink-soft" style="font-size: 0.8125rem;">
                                    {{ $invoice->business_email }}
                                </div>
                            @endif
                            @if ($invoice->business_phone)
                                <div class="mb-1 ink-soft" style="font-size: 0.8125rem;">
                                    {{ $invoice->business_phone }}
                                </div>
                            @endif
                            @if ($invoice->business_address)
                                <div class="ink-soft" style="font-size: 0.8125rem; white-space: pre-line;">
                                    {{ $invoice->business_address }}
                                </div>
                            @endif
                        </div>
                        <div class="col-5 text-end">
                            <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.75rem; font-weight: 700; color: var(--ink); line-height: 1;">
                                Invoice
                            </div>
                            <div class="mt-3">
                                <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft);">Invoice #</div>
                                <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
                            </div>
                            <div class="mt-2">
                                <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft);">Date</div>
                                <div class="fw-medium">{{ $invoice->invoice_date->format('M j, Y') }}</div>
                            </div>
                            <div class="mt-2">
                                <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft);">Due date</div>
                                <div class="fw-medium">
                                    {{ $invoice->due_date ? $invoice->due_date->format('M j, Y') : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bill to --}}
                    <div class="mb-4">
                        <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft); margin-bottom: 0.375rem;">Bill to</div>
                        <div class="fw-bold mb-1" style="font-size: 1rem;">{{ optional($invoice->client)->name ?? '—' }}</div>
                        @if (optional($invoice->client)->email)
                            <div class="mb-1 ink-soft" style="font-size: 0.8125rem;">{{ $invoice->client->email }}</div>
                        @endif
                        @if (optional($invoice->client)->phone)
                            <div class="mb-1 ink-soft" style="font-size: 0.8125rem;">{{ $invoice->client->phone }}</div>
                        @endif
                        @if (optional($invoice->client)->address)
                            <div class="ink-soft" style="font-size: 0.8125rem; white-space: pre-line;">
                                {{ $invoice->client->address }}
                            </div>
                        @endif
                    </div>

                    {{-- Items table --}}
                    <div class="mb-4">
                        <table class="table table-sm mb-0" style="border-collapse: collapse; width: 100%;">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(18,32,46,0.12);">
                                    <th scope="col" style="text-align: left; padding: 0.5rem 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft);">Description</th>
                                    <th scope="col" style="text-align: right; padding: 0.5rem 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft); width: 14%;">Qty</th>
                                    <th scope="col" style="text-align: right; padding: 0.5rem 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft); width: 20%;">Price</th>
                                    <th scope="col" style="text-align: right; padding: 0.5rem 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft); width: 20%;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->items as $item)
                                    <tr style="border-bottom: 1px solid rgba(18,32,46,0.05);">
                                        <td style="padding: 0.6rem 0.25rem;">{{ $item->description }}</td>
                                        <td style="padding: 0.6rem 0.25rem; text-align: right;">{{ $item->quantity }}</td>
                                        <td style="padding: 0.6rem 0.25rem; text-align: right; font-variant-numeric: tabular-nums;">
                                            {{ $invoice->formatCents($item->price) }}
                                        </td>
                                        <td style="padding: 0.6rem 0.25rem; text-align: right; font-weight: 600; font-variant-numeric: tabular-nums;">
                                            {{ $invoice->formatCents($item->total) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="row justify-content-end mb-4">
                        <div class="col-7">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="ink-soft">Subtotal</span>
                                <span style="font-variant-numeric: tabular-nums;">{{ $invoice->subtotal_formatted }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="ink-soft">
                                    Tax ({{ rtrim(rtrim(number_format((float)$invoice->tax_percent, 2), '0'), '.') }}%)
                                </span>
                                <span style="font-variant-numeric: tabular-nums;">{{ $invoice->tax_amount_formatted }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="ink-soft">Discount</span>
                                <span style="font-variant-numeric: tabular-nums;">−{{ $invoice->discount_formatted }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2" style="border-top: 2px solid var(--ink);">
                                <strong style="font-family: 'Fraunces', Georgia, serif; font-size: 1.125rem;">Total</strong>
                                <strong style="font-family: 'Fraunces', Georgia, serif; font-size: 1.375rem; color: var(--ink); font-variant-numeric: tabular-nums;">
                                    {{ $invoice->total_formatted }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @if ($invoice->notes)
                        <div>
                            <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft); margin-bottom: 0.375rem;">Notes</div>
                            <div class="ink-soft" style="font-size: 0.875rem; white-space: pre-line;">
                                {{ $invoice->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
