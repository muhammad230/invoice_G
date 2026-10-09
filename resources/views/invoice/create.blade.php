@extends(Auth::check() ? 'layouts.dashboard' : 'layouts.app')

@php
    $editing = isset($invoice) && $invoice !== null;
    $pageTitle = $editing ? 'Edit invoice' : 'New Invoice';
    $pageHeading = $editing ? 'Edit invoice' : 'Create an invoice';

    // Prefill defaults: old() > invoice (edit) > controller-provided BusinessProfile.
    $oldOr = function ($key, $default = '') use ($editing, $invoice, $business_name, $business_email, $business_phone, $business_address, $business_website, $business_tagline, $business_bank_name, $business_account_title, $business_account_number, $business_iban, $business_payment_method, $business_signature_name, $business_signature_title) {
        if (old($key, null) !== null) {
            return old($key);
        }
        if (!$editing) {
            // Create mode: use controller passed-in BusinessProfile values as default
            return match ($key) {
                'business_name'             => $business_name             ?? $default,
                'business_email'            => $business_email            ?? $default,
                'business_phone'            => $business_phone            ?? $default,
                'business_address'          => $business_address          ?? $default,
                'business_website'          => $business_website          ?? $default,
                'business_tagline'          => $business_tagline          ?? $default,
                'business_bank_name'        => $business_bank_name        ?? $default,
                'business_account_title'    => $business_account_title    ?? $default,
                'business_account_number'   => $business_account_number   ?? $default,
                'business_iban'             => $business_iban             ?? $default,
                'business_payment_method'   => $business_payment_method   ?? $default,
                'business_signature_name'   => $business_signature_name   ?? $default,
                'business_signature_title'  => $business_signature_title  ?? $default,
                default => $default,
            };
        }
        // Edit mode: fall back to Invoice attributes (no attribute for nested items though).
        return match ($key) {
            'client_id'       => old('client_id', $invoice->client_id),
            'business_name'   => old('business_name', $invoice->business_name    ?? ($business_name    ?? $default)),
            'business_email'  => old('business_email', $invoice->business_email  ?? ($business_email   ?? $default)),
            'business_phone'  => old('business_phone', $invoice->business_phone  ?? ($business_phone   ?? $default)),
            'business_address'=> old('business_address', $invoice->business_address ?? ($business_address ?? $default)),
            'business_website'         => old('business_website', $invoice->business_website ?? ($business_website ?? $default)),
            'business_tagline'         => old('business_tagline', $invoice->business_tagline ?? ($business_tagline ?? $default)),
            'business_bank_name'       => old('business_bank_name', $invoice->business_bank_name ?? ($business_bank_name ?? $default)),
            'business_account_title'   => old('business_account_title', $invoice->business_account_title ?? ($business_account_title ?? $default)),
            'business_account_number'  => old('business_account_number', $invoice->business_account_number ?? ($business_account_number ?? $default)),
            'business_iban'            => old('business_iban', $invoice->business_iban ?? ($business_iban ?? $default)),
            'business_payment_method'  => old('business_payment_method', $invoice->business_payment_method ?? ($business_payment_method ?? $default)),
            'business_signature_name'  => old('business_signature_name', $invoice->business_signature_name ?? ($business_signature_name ?? $default)),
            'business_signature_title' => old('business_signature_title', $invoice->business_signature_title ?? ($business_signature_title ?? $default)),
            'invoice_number'  => old('invoice_number', $invoice->invoice_number),
            'invoice_date'    => old('invoice_date', $invoice->invoice_date->format('Y-m-d')),
            'due_date'        => old('due_date', optional($invoice->due_date)?->format('Y-m-d')),
            'currency'        => old('currency', $invoice->currency),
            'tax'             => old('tax', (string) $invoice->tax_percent),
            'discount'        => old('discount', number_format($invoice->discount / 100, 2, '.', '')),
            'status'          => old('status', $invoice->status),
            'notes'           => old('notes', $invoice->notes),
            default => $default,
        };
    };

    // Logo default (data URI): controller passed BusinessProfile, or invoice, or empty
    if ($editing && !empty($invoice->logo_data)) {
        $defaultLogo = $invoice->logo_data;
    } elseif (!empty($logo_default)) {
        $defaultLogo = $logo_default;
    } else {
        $defaultLogo = '';
    }

    // Item rows: old() on failure, DB rows on edit, empty starter otherwise.
    if ($errors->any() || old('items')) {
        $oldItems = old('items', []);
    } elseif ($editing) {
        $oldItems = $invoice->items->map(function ($i) {
            return [
                'description' => $i->description,
                'details'     => (string) ($i->details ?? ''),
                'quantity'    => $i->quantity,
                'price'       => number_format($i->price / 100, 2, '.', ''),
            ];
        })->all();
    } else {
        $oldItems = [
            ['description' => '', 'details' => '', 'quantity' => 1, 'price' => ''],
        ];
    }

    $oldItems = $oldItems ?: [['description' => '', 'details' => '', 'quantity' => 1, 'price' => '']];

    // Is the user authenticated? If yes -> persist / clients / services available.
    $authed = Auth::check();

    // For the "return to invoice builder" link after adding a new client from modal/dropdown.
    if ($editing) {
        $clientReturnUrl = route('invoices.edit', $invoice);
    } else {
        $clientReturnUrl = $authed ? route('invoices.create') : route('invoice.create');
    }

    // Prepare products JSON for the JS service dropdown (only when authed).
    if ($authed && isset($products)) {
        $productsJson = $products->map(function ($p) {
            return [
                'id'          => $p->id,
                'name'        => $p->name,
                'description' => $p->description ?? '',
                'price'       => number_format($p->price / 100, 2, '.', ''),
            ];
        })->values();
    } else {
        $productsJson = collect();
    }
@endphp

@section('title', $pageTitle . ' — InvoiceFlow')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        {{-- Page header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-file-earmark-plus me-1"></i>
                    {{ $pageTitle }}
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">{{ $pageHeading }}</h1>
                @if ($editing)
                    <p class="ink-soft mb-0 mt-1">
                        Invoice <strong>{{ $invoice->invoice_number }}</strong> · updating will recalculate all totals.
                    </p>
                @elseif (!$authed)
                    <p class="ink-soft mb-0 mt-1">
                        Quick preview — to save or download,
                        <a href="{{ route('register') }}" class="auth-link">create a free account</a>.
                    </p>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ $authed ? route('invoices.index') : route('home') }}" class="btn btn-outline-ink">
                    <i class="bi bi-arrow-left"></i>
                    {{ $authed ? 'Back to invoices' : 'Back to home' }}
                </a>
                @if ($editing)
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-ink">
                        <i class="bi bi-eye me-1"></i> View
                    </a>
                @endif
            </div>
        </div>

        @include('partials.alerts')

        {{-- Two-column layout: FORM (left) | LIVE PREVIEW (right) --}}
        <div class="row g-4 g-lg-5">

            {{-- ======================================================================
                 LEFT COLUMN — The form (5 Bootstrap cards + action buttons)
                 ====================================================================== --}}
            <div class="col-lg-6">

                {{-- Form action differs: create (POST invoices.store) + edit (PUT invoices.update) --}}
                @if ($authed)
                    <form id="invoiceForm"
                          action="{{ $editing ? route('invoices.update', $invoice) : route('invoices.store') }}"
                          method="POST"
                          novalidate>
                        @csrf
                        @if ($editing)
                            @method('PUT')
                        @endif
                @else
                    {{-- Legacy guest-only demo — no DB write (route removed, show message) --}}
                    <form id="invoiceForm"
                          action="{{ route('register') }}"
                          method="GET"
                          novalidate>
                @endif

                    {{-- Hidden logo data URL (populated by JS on file select OR default from profile) --}}
                    <input type="hidden" name="logo_data" id="logoData" value="{{ e($defaultLogo) }}">

                    {{-- 1. Your business ------------------------------------------------ --}}
                    <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <h2 class="mb-3" style="font-size: 1.125rem;">
                                <i class="bi bi-building me-2" style="color: var(--saffron);"></i>
                                Your business
                                @if ($authed)
                                    <span class="ms-2 text-sm" style="font-size: 0.78rem; font-weight: normal; color: var(--ink-soft);">
                                        (<a href="{{ route('settings.business-profile.edit') }}" class="auth-link">prefilled from Settings</a>)
                                    </span>
                                @endif
                            </h2>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="business_name" class="form-label">Business name <span style="color: var(--overdue);">*</span></label>
                                    <input type="text" id="business_name" name="business_name"
                                           class="form-control form-control-lg"
                                           placeholder="Your Studio Inc."
                                           value="{{ $oldOr('business_name') }}" required>
                                </div>
                                <div class="col-12">
                                    <label for="business_tagline" class="form-label">Tagline</label>
                                    <input type="text" id="business_tagline" name="business_tagline"
                                           class="form-control"
                                           placeholder="e.g. Design that converts"
                                           value="{{ $oldOr('business_tagline') }}"
                                           maxlength="150">
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
                                    <label for="business_website" class="form-label">Website</label>
                                    <input type="text" id="business_website" name="business_website"
                                           class="form-control"
                                           placeholder="https://yourstudio.com"
                                           value="{{ $oldOr('business_website') }}"
                                           maxlength="255">
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
                                        @if ($authed && $defaultLogo)
                                            <span class="d-block mt-1">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Your saved business logo is preloaded — upload a different one to override just for this invoice.
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Client (authed: dropdown + quick-create link; guest: free-form) --}}
                    <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <h2 class="mb-3" style="font-size: 1.125rem;">
                                <i class="bi bi-person me-2" style="color: var(--ink);"></i>
                                Client
                            </h2>
                            @if ($authed)
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="client_id" class="form-label">Client <span style="color: var(--overdue);">*</span></label>
                                        <div class="d-flex align-items-end gap-2">
                                            <select id="client_id" name="client_id"
                                                    class="form-select form-select-lg flex-grow-1 @error('client_id') is-invalid @enderror"
                                                    required>
                                                <option value="">— Select a client —</option>
                                                @foreach ($clients as $c)
                                                    <option value="{{ $c->id }}"
                                                        data-name="{{ $c->name }}"
                                                        data-email="{{ $c->email ?? '' }}"
                                                        @selected((string) $oldOr('client_id', '') === (string) $c->id)>
                                                        {{ $c->name }}
                                                        @if ($c->email)
                                                            &lt;{{ $c->email }}&gt;
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <a href="{{ route('clients.create', ['return' => $clientReturnUrl]) }}"
                                               class="btn btn-outline-ink btn-lg"
                                               title="Add a new client">
                                                <i class="bi bi-person-plus me-1"></i>
                                                Add new client
                                            </a>
                                        </div>
                                        @error('client_id')
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text mt-2">
                                            Don't see the right client?
                                            <a href="{{ route('clients.create', ['return' => $clientReturnUrl]) }}" class="auth-link">Add them first</a>
                                            and they'll appear here automatically.
                                        </div>
                                    </div>
                                </div>

                                {{-- Hidden guest-style client fields: not needed for authed users --}}
                                <input type="hidden" name="client_name" value="">
                                <input type="hidden" name="client_email" value="">
                                <input type="hidden" name="client_phone" value="">
                                <input type="hidden" name="client_address" value="">
                            @else
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
                            @endif
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
                                           class="form-control @error('invoice_number') is-invalid @enderror"
                                           value="{{ $oldOr('invoice_number', $defaults['invoice_number']) }}">
                                    @error('invoice_number')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
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
                                @if ($authed)
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <label for="status" class="form-label">Status</label>
                                        <select id="status" name="status" class="form-select">
                                            <option value="draft"   @selected($oldOr('status', $defaults['status']) === 'draft')>Draft</option>
                                            <option value="pending" @selected($oldOr('status', $defaults['status']) === 'pending')>Pending</option>
                                            <option value="paid"    @selected($oldOr('status', $defaults['status']) === 'paid')>Paid</option>
                                        </select>
                                    </div>
                                @endif
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
                                    @if ($authed && $productsJson->count() > 0)
                                        <span class="ms-2 text-sm" style="font-size: 0.78rem; font-weight: normal; color: var(--ink-soft);">
                                            — use the <strong>Pick a service</strong> dropdown to fill rows quickly.
                                        </span>
                                    @endif
                                </h2>
                                <button type="button" id="addItemBtn" class="btn btn-outline-ink btn-sm">
                                    <i class="bi bi-plus-lg"></i>
                                    Add item
                                </button>
                            </div>

                            {{-- Hidden: service data available for JS (only when authed) --}}
                            @if ($authed)
                                <script id="servicesData" type="application/json">@json($productsJson)</script>
                            @endif

                            <div class="table-responsive">
                                <table class="table align-middle mb-0" id="itemsTable">
                                    <thead>
                                        <tr style="font-size: 0.8125rem; color: var(--ink-soft);">
                                            <th style="width: 42%;">Description
                                                @if ($authed && $productsJson->count() > 0)
                                                    <div style="font-size: 0.75rem; font-weight: normal; color: var(--sage); margin-top: 2px;">
                                                        Pick a saved service ↓
                                                    </div>
                                                @endif
                                            </th>
                                            <th style="width: 10%;">Qty</th>
                                            <th style="width: 18%;">Price</th>
                                            <th style="width: 18%;">Line total</th>
                                            <th style="width: 8%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @foreach ($oldItems as $idx => $row)
                                            <tr class="item-row" data-index="{{ $idx }}">
                                                <td>
                                                    @if ($authed && $productsJson->count() > 0)
                                                        <div class="mb-2">
                                                            <select class="form-select form-select-sm item-service"
                                                                    data-row-index="{{ $idx }}"
                                                                    aria-label="Pick a saved service for this row">
                                                                <option value="">— Pick a saved service (optional) —</option>
                                                                @foreach ($productsJson as $svc)
                                                                    <option value="{{ $svc['id'] }}"
                                                                            data-description="{{ e($svc['description']) }}"
                                                                            data-price="{{ e($svc['price']) }}">
                                                                        {{ $svc['name'] }} — ${{ $svc['price'] }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endif
                                                    <input type="text" name="items[{{ $idx }}][description]"
                                                           class="form-control item-description"
                                                           placeholder="Website design"
                                                           value="{{ $row['description'] ?? '' }}">
                                                    <input type="text" name="items[{{ $idx }}][details]"
                                                           class="form-control item-details mt-2"
                                                           placeholder="Optional short detail line"
                                                           value="{{ $row['details'] ?? '' }}"
                                                           maxlength="1000">
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

                    {{-- 6. Payment details & signature -------------------------------- --}}
                    <div class="card mb-4" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4">
                            <h2 class="mb-1" style="font-size: 1.125rem;">
                                <i class="bi bi-bank me-2" style="color: var(--saffron);"></i>
                                Payment details &amp; signature
                            </h2>
                            <p class="ink-soft mb-3" style="font-size: 0.8125rem;">
                                Optional — shown on the PDF. Leave blank to hide.
                                @if ($authed)
                                    Prefilled from <a href="{{ route('settings.business-profile.edit') }}" class="auth-link">Settings</a>.
                                @endif
                            </p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="business_bank_name" class="form-label">Bank name</label>
                                    <input type="text" id="business_bank_name" name="business_bank_name"
                                           class="form-control" placeholder="e.g. Chase Bank"
                                           value="{{ $oldOr('business_bank_name') }}" maxlength="150">
                                </div>
                                <div class="col-md-6">
                                    <label for="business_account_title" class="form-label">Account title</label>
                                    <input type="text" id="business_account_title" name="business_account_title"
                                           class="form-control" placeholder="e.g. Your Studio Inc."
                                           value="{{ $oldOr('business_account_title') }}" maxlength="150">
                                </div>
                                <div class="col-md-6">
                                    <label for="business_account_number" class="form-label">Account number</label>
                                    <input type="text" id="business_account_number" name="business_account_number"
                                           class="form-control" placeholder="e.g. 0001 2345 6789"
                                           value="{{ $oldOr('business_account_number') }}" maxlength="50">
                                </div>
                                <div class="col-md-6">
                                    <label for="business_iban" class="form-label">IBAN / SWIFT</label>
                                    <input type="text" id="business_iban" name="business_iban"
                                           class="form-control" placeholder="e.g. GB29 NWBK 6016 1331 9268 19"
                                           value="{{ $oldOr('business_iban') }}" maxlength="60">
                                </div>
                                <div class="col-md-6">
                                    <label for="business_payment_method" class="form-label">Payment method</label>
                                    <input type="text" id="business_payment_method" name="business_payment_method"
                                           class="form-control" placeholder="e.g. Bank Transfer"
                                           value="{{ $oldOr('business_payment_method') }}" maxlength="50">
                                </div>
                                <div class="col-md-6">
                                    <label for="business_signature_name" class="form-label">Signature name</label>
                                    <input type="text" id="business_signature_name" name="business_signature_name"
                                           class="form-control" placeholder="e.g. Jane Doe"
                                           value="{{ $oldOr('business_signature_name') }}" maxlength="150">
                                </div>
                                <div class="col-md-6">
                                    <label for="business_signature_title" class="form-label">Signature title</label>
                                    <input type="text" id="business_signature_title" name="business_signature_title"
                                           class="form-control" placeholder="e.g. Founder &amp; CEO"
                                           value="{{ $oldOr('business_signature_title') }}" maxlength="150">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action buttons: Save (authed) or "Sign up to save" (guest) --- --}}
                    <div class="row g-3">
                        @if ($authed)
                            <div class="col-12">
                                <button type="submit"
                                        class="btn btn-saffron btn-lg w-100 justify-content-center">
                                    <i class="bi bi-check2"></i>
                                    {{ $editing ? 'Update invoice' : 'Save invoice' }}
                                </button>
                                <div class="form-text mt-2 text-center">
                                    <i class="bi bi-info-circle me-1"></i>
                                    After saving you'll be able to
                                    <a href="{{ route('invoices.index') }}" class="auth-link">download the PDF</a>
                                    from the invoice view.
                                </div>
                            </div>
                        @else
                            <div class="col-12">
                                <a href="{{ route('register') }}" class="btn btn-saffron btn-lg w-100 justify-content-center">
                                    <i class="bi bi-person-plus me-1"></i>
                                    Create account to save &amp; download PDF
                                </a>
                                <div class="form-text mt-2 text-center">
                                    Already have an account?
                                    <a href="{{ route('login') }}" class="auth-link">Sign in</a>
                                </div>
                            </div>
                        @endif
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
                                <div id="p_business_logo" class="mb-2" style="max-height: 60px; @if ($defaultLogo) display: block; @else display: none; @endif">
                                    <img id="p_logo_img" alt="Business logo"
                                         @if ($defaultLogo) src="{{ $defaultLogo }}" @endif
                                         style="max-height: 60px; max-width: 180px; display: block;">
                                </div>
                                <div id="p_business_name" class="fw-bold mb-1" style="font-family: 'Fraunces', Georgia, serif; font-size: 1.125rem; color: var(--ink);">Your business name</div>
                                <div id="p_business_tagline" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem; font-style: italic;"></div>
                                <div id="p_business_email" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                                <div id="p_business_phone" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                                <div id="p_business_website" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
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

                        {{-- PREVIEW: PAYMENT DETAILS --}}
                        <div id="p_payment_wrap" class="mt-4" style="display: none;">
                            <div style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-soft); margin-bottom: 0.375rem;">Payment details</div>
                            <div id="p_payment_method" class="mb-1" style="font-weight: 600; font-size: 0.875rem;"></div>
                            <div id="p_bank_name" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                            <div id="p_account_title" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                            <div id="p_account_number" class="mb-1" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                            <div id="p_iban" style="color: var(--ink-soft); font-size: 0.8125rem;"></div>
                        </div>

                        {{-- PREVIEW: SIGNATURE --}}
                        <div id="p_signature_wrap" class="mt-4 text-end" style="display: none;">
                            <div style="border-top: 1px solid rgba(18,32,46,0.15); display: inline-block; padding-top: 0.375rem; min-width: 180px; text-align: center;">
                                <div id="p_signature_name" class="fw-bold" style="font-size: 0.875rem;"></div>
                                <div id="p_signature_title" style="color: var(--ink-soft); font-size: 0.75rem;"></div>
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
