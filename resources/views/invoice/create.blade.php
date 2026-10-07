@extends('layouts.app')

@section('title', 'Create Invoice — InvoiceFlow')

@php
    // Shortcut helper so we don't keep writing old(...) with defaults.
    $oldOr = function ($key, $default = '') {
        return old($key, $default);
    };

    // After a failed validation we keep item rows. Otherwise start with 1 row.
    $oldItems = old('items', [
        ['description' => '', 'quantity' => 1, 'price' => ''],
    ]);
@endphp

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        {{-- Page header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-file-earmark-plus me-1"></i>
                    New Invoice
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">Create an invoice</h1>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-ink">
                <i class="bi bi-arrow-left"></i>
                Back to home
            </a>
        </div>

        {{-- Validation errors (from PDF download submit) --}}
        @if ($errors->any())
            <div class="alert alert-danger mb-4 rounded-0" style="border-radius: var(--radius);">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <strong>Please fix the following issues and try again:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Two-column layout: FORM (left) | LIVE PREVIEW (right) --}}
        <div class="row g-4 g-lg-5">

            {{-- ======================================================================
                 LEFT COLUMN — The form (5 Bootstrap cards + Download button)
                 ====================================================================== --}}
            <div class="col-lg-6">

                {{-- Download PDF form — wraps all inputs so they all POST together. --}}
                <form id="invoiceForm" action="{{ route('invoice.download') }}" method="POST" novalidate target="_blank">
                    @csrf

                    {{-- Hidden logo data URL (populated by JS on file select) --}}
                    <input type="hidden" name="logo_data" id="logoData">

                    {{-- 1. Your business ------------------------------------------------ --}}
                    <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <h2 class="mb-3" style="font-size: 1.125rem;">
                                <i class="bi bi-building me-2" style="color: var(--saffron);"></i>
                                Your business
                            </h2>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="business_name" class="form-label">Business name <span style="color: var(--overdue);">*</span></label>
                                    <input type="text" id="business_name" name="business_name"
                                           class="form-control form-control-lg"
                                           placeholder="Your Studio Inc."
                                           value="{{ $oldOr('business_name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="business_email" class="form-label">Email</label>
                                    <input type="email" id="business_email" name="business_email"
                                           class="form-control"
                                           placeholder="hello@yourstudio.com"
                                           value="{{ $oldOr('business_email') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="business_phone" class="form-label">Phone</label>
                                    <input type="tel" id="business_phone" name="business_phone"
                                           class="form-control"
                                           placeholder="+1 (555) 000-0000"
                                           value="{{ $oldOr('business_phone') }}">
                                </div>
                                <div class="col-12">
                                    <label for="business_address" class="form-label">Address</label>
                                    <textarea id="business_address" name="business_address" rows="2"
                                              class="form-control"
                                              placeholder="123 Main St, City, Country">{{ $oldOr('business_address') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="logoFile" class="form-label">Logo (optional)</label>
                                    <input type="file" id="logoFile" accept="image/*"
                                           class="form-control">
                                    <div class="form-text" id="logoFileHint">
                                        Will appear on the invoice preview and PDF.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Client ------------------------------------------------------- --}}
                    <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <h2 class="mb-3" style="font-size: 1.125rem;">
                                <i class="bi bi-person me-2" style="color: var(--ink);"></i>
                                Client
                            </h2>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="client_name" class="form-label">Client name <span style="color: var(--overdue);">*</span></label>
                                    <input type="text" id="client_name" name="client_name"
                                           class="form-control form-control-lg"
                                           placeholder="Ahmed Khan"
                                           value="{{ $oldOr('client_name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="client_email" class="form-label">Email</label>
                                    <input type="email" id="client_email" name="client_email"
                                           class="form-control"
                                           placeholder="client@email.com"
                                           value="{{ $oldOr('client_email') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="client_phone" class="form-label">Phone</label>
                                    <input type="tel" id="client_phone" name="client_phone"
                                           class="form-control"
                                           placeholder="+1 (555) 000-0001"
                                           value="{{ $oldOr('client_phone') }}">
                                </div>
                                <div class="col-12">
                                    <label for="client_address" class="form-label">Address</label>
                                    <textarea id="client_address" name="client_address" rows="2"
                                              class="form-control"
                                              placeholder="456 Client Ave, City, Country">{{ $oldOr('client_address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Invoice details --------------------------------------------- --}}
                    <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <h2 class="mb-3" style="font-size: 1.125rem;">
                                <i class="bi bi-calendar3 me-2" style="color: var(--sage);"></i>
                                Invoice details
                            </h2>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="invoice_number" class="form-label">Invoice number</label>
                                    <input type="text" id="invoice_number" name="invoice_number"
                                           class="form-control"
                                           value="{{ $oldOr('invoice_number', $defaults['invoice_number']) }}">
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="invoice_date" class="form-label">Invoice date</label>
                                    <input type="date" id="invoice_date" name="invoice_date"
                                           class="form-control"
                                           value="{{ $oldOr('invoice_date', $defaults['invoice_date']) }}">
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="due_date" class="form-label">Due date</label>
                                    <input type="date" id="due_date" name="due_date"
                                           class="form-control"
                                           value="{{ $oldOr('due_date', $defaults['due_date']) }}">
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="currency" class="form-label">Currency</label>
                                    <select id="currency" name="currency" class="form-select">
                                        @foreach ($currencies as $code => $meta)
                                            <option value="{{ $code }}"
                                                @selected($oldOr('currency', $defaults['currency']) === $code)
                                                data-symbol="{{ $meta['symbol'] }}">
                                                {{ $meta['name'] }} ({{ $meta['symbol'] }} {{ $code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Items -------------------------------------------------------- --}}
                    <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                <h2 class="mb-0" style="font-size: 1.125rem;">
                                    <i class="bi bi-list-check me-2" style="color: var(--saffron-dark);"></i>
                                    Items
                                </h2>
                                <button type="button" id="addItemBtn" class="btn btn-outline-ink btn-sm">
                                    <i class="bi bi-plus-lg"></i>
                                    Add item
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle mb-0" id="itemsTable">
                                    <thead>
                                        <tr style="font-size: 0.8125rem; color: var(--ink-soft);">
                                            <th style="width: 42%;">Description</th>
                                            <th style="width: 14%;">Qty</th>
                                            <th style="width: 18%;">Price</th>
                                            <th style="width: 18%;">Line total</th>
                                            <th style="width: 8%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @foreach ($oldItems as $idx => $row)
                                            <tr class="item-row" data-index="{{ $idx }}">
                                                <td>
                                                    <input type="text" name="items[{{ $idx }}][description]"
                                                           class="form-control item-description"
                                                           placeholder="Website design"
                                                           value="{{ $row['description'] ?? '' }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $idx }}][quantity]"
                                                           class="form-control item-quantity"
                                                           min="1" step="1"
                                                           value="{{ $row['quantity'] ?? 1 }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $idx }}][price]"
                                                           class="form-control item-price"
                                                           min="0" step="0.01"
                                                           placeholder="0.00"
                                                           value="{{ isset($row['price']) && $row['price'] !== '' ? $row['price'] : '' }}">
                                                </td>
                                                <td class="item-line-total text-end pe-3" style="font-variant-numeric: tabular-nums; font-weight: 600;">
                                                    0.00
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                            class="btn btn-sm btn-link remove-item-btn"
                                                            style="color: var(--overdue);"
                                                            aria-label="Remove this item">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Tax / Discount / Notes + totals row ------------------------- --}}
                    <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <h2 class="mb-3" style="font-size: 1.125rem;">
                                <i class="bi bi-calculator me-2" style="color: var(--sage);"></i>
                                Totals &amp; notes
                            </h2>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="tax" class="form-label">Tax (%)</label>
                                    <input type="number" id="tax" name="tax" min="0" max="100" step="0.01"
                                           class="form-control"
                                           value="{{ $oldOr('tax', $defaults['tax']) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="discount" class="form-label">Discount (fixed amount)</label>
                                    <input type="number" id="discount" name="discount" min="0" step="0.01"
                                           class="form-control"
                                           value="{{ $oldOr('discount', $defaults['discount']) }}">
                                </div>
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes (optional)</label>
                                    <textarea id="notes" name="notes" rows="3"
                                              class="form-control"
                                              placeholder="Thank you for your business! Payment is due within 30 days.">{{ $oldOr('notes') }}</textarea>
                                </div>
                            </div>

                            {{-- Totals summary in the form (right-aligned list) --}}
                            <div class="row justify-content-end">
                                <div class="col-md-8 col-lg-7">
                                    <div class="p-3" style="background: rgba(18,32,46,0.03); border-radius: var(--radius-sm);">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="color: var(--ink-soft);">Subtotal</span>
                                            <span id="subtotal_display" style="font-variant-numeric: tabular-nums;">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="color: var(--ink-soft);">Tax <span id="tax_pct_label">(0%)</span></span>
                                            <span id="tax_display" style="font-variant-numeric: tabular-nums;">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="color: var(--ink-soft);">Discount</span>
                                            <span id="discount_display" style="font-variant-numeric: tabular-nums;">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between pt-2" style="border-top: 1px solid rgba(18,32,46,0.1);">
                                            <strong style="font-family: 'Fraunces', Georgia, serif; font-size: 1.125rem;">Total</strong>
                                            <strong id="total_display"
                                                    style="font-family: 'Fraunces', Georgia, serif; font-size: 1.375rem; color: var(--ink); font-variant-numeric: tabular-nums;">
                                                0.00
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Download PDF button -------------------------------------------- --}}
                    <div class="text-start">
                        <button type="submit" id="downloadBtn" class="btn btn-saffron btn-lg w-100 justify-content-center">
                            <i class="bi bi-filetype-pdf"></i>
                            Download PDF
                        </button>
                    </div>
                </form>
            </div>

            {{-- ======================================================================
                 RIGHT COLUMN — Live Preview (white invoice "paper")
                 On desktop: sticky so it scrolls with the form.
                 ====================================================================== --}}
            <div class="col-lg-6">
                <div id="previewContainer" class="sticky-top" style="top: 96px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="section-label mb-0">
                            <i class="bi bi-eye me-1"></i>
                            Live preview
                        </span>
                        <span style="font-size: 0.8125rem; color: var(--ink-soft);">
                            <i class="bi bi-arrow-repeat"></i> Updates as you type
                        </span>
                    </div>

                    {{-- Preview sheet — styled exactly like the PDF will look. --}}
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

                        {{-- PREVIEW HEADER: LOGO + BUSINESS | INVOICE META --}}
                        <div class="row mb-4">
                            <div class="col-7">
                                <div id="p_business_logo" class="mb-2" style="max-height: 60px; display: none;">
                                    <img id="p_logo_img" alt="Business logo" style="max-height: 60px; max-width: 180px; display: block;">
                                </div>
                                <div id="p_business_name" class="fw-bold mb-1" style="font-family: 'Fraunces', Georgia, serif; font-size: 1.125rem; color: var(--ink);">Your business name</div>
                                <div id="p_business_email" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                                <div id="p_business_phone" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                                <div id="p_business_address" style="color: var(--ink-soft); font-size: 0.8125rem; white-space: pre-line;"></div>
                            </div>
                            <div class="col-5 text-end">
                                <div style="font-family: 'Fraunces', Georgia, serif; font-size: 1.75rem; font-weight: 700; color: var(--ink); line-height: 1;">Invoice</div>
                                <div class="mt-3">
                                    <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft);">Invoice #</div>
                                    <div id="p_invoice_number" style="font-weight: 600;">INV-001</div>
                                </div>
                                <div class="mt-2">
                                    <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft);">Date</div>
                                    <div id="p_invoice_date" style="font-weight: 500;">–</div>
                                </div>
                                <div class="mt-2">
                                    <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft);">Due date</div>
                                    <div id="p_due_date" style="font-weight: 500;">–</div>
                                </div>
                            </div>
                        </div>

                        {{-- PREVIEW: BILL TO --}}
                        <div class="mb-4">
                            <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft); margin-bottom: 0.375rem;">Bill to</div>
                            <div id="p_client_name" class="fw-bold mb-1" style="font-size: 1rem;">Client name</div>
                            <div id="p_client_email" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                            <div id="p_client_phone" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                            <div id="p_client_address" style="color: var(--ink-soft); font-size: 0.8125rem; white-space: pre-line;"></div>
                        </div>

                        {{-- PREVIEW: ITEMS TABLE --}}
                        <div class="mb-4">
                            <table class="table table-sm mb-0" id="p_items_table" style="border-collapse: collapse; width: 100%;">
                                <thead>
                                    <tr style="border-bottom: 1px solid rgba(18,32,46,0.12);">
                                        <th scope="col" style="text-align: left; padding: 0.5rem 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft);">Description</th>
                                        <th scope="col" style="text-align: right; padding: 0.5rem 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft); width: 14%;">Qty</th>
                                        <th scope="col" style="text-align: right; padding: 0.5rem 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft); width: 20%;">Price</th>
                                        <th scope="col" style="text-align: right; padding: 0.5rem 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft); width: 20%;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="p_items_body">
                                    {{-- Populated by JS --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- PREVIEW: TOTALS --}}
                        <div class="row justify-content-end mb-4">
                            <div class="col-7">
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color: var(--ink-soft);">Subtotal</span>
                                    <span id="p_subtotal" style="font-variant-numeric: tabular-nums;">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span id="p_tax_label" style="color: var(--ink-soft);">Tax (0%)</span>
                                    <span id="p_tax" style="font-variant-numeric: tabular-nums;">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span style="color: var(--ink-soft);">Discount</span>
                                    <span id="p_discount" style="font-variant-numeric: tabular-nums;">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2" style="border-top: 2px solid var(--ink);">
                                    <strong style="font-family: 'Fraunces', Georgia, serif; font-size: 1.125rem;">Total</strong>
                                    <strong id="p_total" style="font-family: 'Fraunces', Georgia, serif; font-size: 1.375rem; color: var(--ink); font-variant-numeric: tabular-nums;">0.00</strong>
                                </div>
                            </div>
                        </div>

                        {{-- PREVIEW: NOTES --}}
                        <div id="p_notes_wrap">
                            <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft); margin-bottom: 0.375rem;">Notes</div>
                            <div id="p_notes" style="color: var(--ink-soft); font-size: 0.875rem; white-space: pre-line; display: none;"></div>
                            <div id="p_notes_placeholder" style="color: rgba(74,88,102,0.4); font-size: 0.875rem; font-style: italic;">
                                No notes added.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

@section('scripts')
    <script src="{{ asset('js/invoice.js') }}"></script>
@endsection
