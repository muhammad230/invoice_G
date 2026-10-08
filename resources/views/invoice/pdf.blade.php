<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice - {{ $invoice_number }}</title>
<style>
    /* --------------------------------------------------------------
       DomPDF-compatible stylesheet.
       Rules: NO flexbox, NO grid, NO CSS variables, NO rem/em.
       Use: tables, floats, absolute positioning, pixel widths.
       Font: DejaVu Sans (built-in to dompdf) — supports €, £, Rs etc.
       -------------------------------------------------------------- */
    @page {
        margin: 2cm 1.8cm;
    }
    * {
        box-sizing: border-box;
    }
    html, body {
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-size: 12px;
        color: #12202E;
        background: #ffffff;
        line-height: 1.5;
    }

    /* --- Header block: business (left float) | invoice meta (right float) --- */
    .header { width: 100%; overflow: hidden; margin-bottom: 30px; }
    .business-block { float: left;  width: 55%; }
    .invoice-meta   { float: right; width: 40%; text-align: right; }

    .business-logo {
        max-width: 180px;
        max-height: 60px;
        margin-bottom: 8px;
        display: block;
    }
    .business-name {
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-weight: bold;
        font-size: 16px;
        color: #12202E;
        margin-bottom: 4px;
    }
    .business-sub {
        font-size: 10px;
        color: #4A5866;
        margin: 1px 0;
    }

    .invoice-title {
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-weight: bold;
        font-size: 28px;
        color: #12202E;
        line-height: 1;
        margin-bottom: 18px;
    }
    .meta-row { margin-bottom: 8px; }
    .meta-label {
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4A5866;
        margin-bottom: 2px;
    }
    .meta-value {
        font-size: 12px;
        font-weight: bold;
        color: #12202E;
    }
    .meta-value-normal {
        font-size: 12px;
        color: #12202E;
        font-weight: normal;
    }

    /* --- Status pill (top-right, floated) ---------------------------- */
    .status-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }
    .status-paid {
        background: #E6F2EC;
        color: #3F6B50;
    }
    .status-pending {
        background: #FBF0D8;
        color: #B3832F;
    }
    .status-overdue {
        background: #FBE4E2;
        color: #B3372F;
    }
    .status-draft {
        background: #F1EEE8;
        color: #4A5866;
    }

    /* --- Bill to block ------------------------------------------------- */
    .bill-to {
        width: 100%;
        margin-bottom: 26px;
    }
    .bill-to .section-label {
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4A5866;
        margin-bottom: 4px;
    }
    .client-name {
        font-weight: bold;
        font-size: 13px;
        color: #12202E;
        margin-bottom: 2px;
    }
    .client-sub {
        font-size: 10px;
        color: #4A5866;
        margin: 1px 0;
    }

    /* --- Items table --------------------------------------------------- */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 22px;
    }
    .items-table thead th {
        border-bottom: 1px solid #12202E;
        text-align: left;
        padding: 8px 6px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4A5866;
    }
    .items-table tbody td {
        border-bottom: 1px solid #E8E4DA;
        padding: 8px 6px;
        font-size: 11px;
        color: #12202E;
        vertical-align: top;
    }
    .items-table .qty,
    .items-table .price,
    .items-table .amount {
        text-align: right;
    }
    .items-table .amount {
        font-weight: bold;
    }

    /* --- Totals block (right-aligned table) --------------------------- */
    .totals-wrap {
        width: 100%;
        margin-bottom: 26px;
    }
    .totals-table {
        width: 45%;
        margin-left: auto;
        border-collapse: collapse;
    }
    .totals-table td {
        padding: 5px 4px;
        font-size: 11px;
    }
    .totals-table .label-col {
        color: #4A5866;
        text-align: left;
    }
    .totals-table .value-col {
        color: #12202E;
        text-align: right;
        font-weight: normal;
    }
    .totals-table .total-row td {
        border-top: 2px solid #12202E;
        padding-top: 8px;
    }
    .totals-table .total-row .label-col {
        font-size: 13px;
        font-weight: bold;
        color: #12202E;
    }
    .totals-table .total-row .value-col {
        font-size: 16px;
        font-weight: bold;
        color: #12202E;
    }

    /* --- Notes block --------------------------------------------------- */
    .notes-wrap { width: 100%; }
    .notes-label {
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4A5866;
        margin-bottom: 4px;
    }
    .notes-text {
        font-size: 10px;
        color: #4A5866;
        white-space: pre-wrap;
        line-height: 1.6;
    }

    /* Utility: hide empty rows */
    .empty-hint { color: #9A9589; font-style: italic; }
</style>
</head>
<body>

{{-- =====================================================================
     HEADER: Business (left) | Invoice meta (right)
     ===================================================================== --}}
<table class="header" cellpadding="0" cellspacing="0" style="width:100%;">
<tr>
    <td style="width:55%; vertical-align: top;">
        {{-- Logo (optional, from invoice OR BusinessProfile, via base64) --}}
        @php
            $logoOk = false;
            if (!empty($logo_data) && is_string($logo_data) && str_starts_with(trim($logo_data), 'data:image/')) {
                $logoOk = true;
            }
        @endphp
        @if ($logoOk)
            <img src="{{ $logo_data }}" class="business-logo" alt="Business logo">
        @endif
        <div class="business-name">{{ $business_name }}</div>
        @if (!empty($business_email))
            <div class="business-sub">{{ $business_email }}</div>
        @endif
        @if (!empty($business_phone))
            <div class="business-sub">{{ $business_phone }}</div>
        @endif
        @if (!empty($business_address))
            <div class="business-sub">{{ nl2br(e($business_address)) }}</div>
        @endif
        @if (!empty($business_tax))
            <div class="business-sub">Tax / VAT: {{ $business_tax }}</div>
        @endif
    </td>
    <td style="width:45%; vertical-align: top; text-align: right;">
        <div class="invoice-title">Invoice</div>

        {{-- Status pill (derived, not stored directly in DB) --}}
        @if (!empty($status))
            @php
                $statusClass = match($status) {
                    'paid'    => 'status-paid',
                    'pending' => 'status-pending',
                    'overdue' => 'status-overdue',
                    'draft'   => 'status-draft',
                    default   => 'status-draft',
                };
                $statusLabel = match($status) {
                    'paid'    => 'Paid',
                    'pending' => 'Pending',
                    'overdue' => 'Overdue',
                    'draft'   => 'Draft',
                    default   => ucfirst($status),
                };
            @endphp
            <span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
        @endif

        <div class="meta-row">
            <div class="meta-label">Invoice #</div>
            <div class="meta-value">{{ $invoice_number }}</div>
        </div>
        <div class="meta-row">
            <div class="meta-label">Date</div>
            <div class="meta-value-normal">{{ \Carbon\Carbon::parse($invoice_date)->format('M j, Y') }}</div>
        </div>
        @if (!empty($due_date))
            <div class="meta-row">
                <div class="meta-label">Due date</div>
                <div class="meta-value-normal">{{ \Carbon\Carbon::parse($due_date)->format('M j, Y') }}</div>
            </div>
        @endif
    </td>
</tr>
</table>

{{-- =====================================================================
     BILL TO
     ===================================================================== --}}
<table class="bill-to" cellpadding="0" cellspacing="0">
<tr>
    <td>
        <div class="section-label">Bill to</div>
        <div class="client-name">{{ $client_name }}</div>
        @if (!empty($client_email))
            <div class="client-sub">{{ $client_email }}</div>
        @endif
        @if (!empty($client_phone))
            <div class="client-sub">{{ $client_phone }}</div>
        @endif
        @if (!empty($client_address))
            <div class="client-sub">{{ nl2br(e($client_address)) }}</div>
        @endif
    </td>
</tr>
</table>

{{-- =====================================================================
     ITEMS
     ===================================================================== --}}
<table class="items-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th style="width:48%;">Description</th>
        <th class="qty"   style="width:14%;">Qty</th>
        <th class="price" style="width:19%;">Price</th>
        <th class="amount" style="width:19%;">Amount</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($items as $it)
        <tr>
            <td>{{ $it['description'] }}</td>
            <td class="qty">{{ $it['quantity'] }}</td>
            <td class="price">{{ $currency_symbol }} {{ number_format($it['price'], 2) }}</td>
            <td class="amount">{{ $currency_symbol }} {{ number_format($it['line_total'], 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- =====================================================================
     TOTALS (right-aligned table)
     ===================================================================== --}}
<table class="totals-wrap" cellpadding="0" cellspacing="0">
<tr>
    <td align="right">
        <table class="totals-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="label-col">Subtotal</td>
                <td class="value-col">{{ $currency_symbol }} {{ number_format($totals['subtotal'], 2) }}</td>
            </tr>
            <tr>
                <td class="label-col">Tax ({{ number_format($totals['tax_pct'], (fmod($totals['tax_pct'], 1) == 0 ? 0 : 2)) }}%)</td>
                <td class="value-col">{{ $currency_symbol }} {{ number_format($totals['tax'], 2) }}</td>
            </tr>
            @if ($totals['discount'] > 0)
                <tr>
                    <td class="label-col">Discount</td>
                    <td class="value-col">- {{ $currency_symbol }} {{ number_format($totals['discount'], 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td class="label-col">Total</td>
                <td class="value-col">{{ $currency_symbol }} {{ number_format($totals['total'], 2) }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

{{-- =====================================================================
     NOTES (optional)
     ===================================================================== --}}
@if (!empty($notes))
    <table class="notes-wrap" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <div class="notes-label">Notes</div>
            <div class="notes-text">{{ e($notes) }}</div>
        </td>
    </tr>
    </table>
@endif

</body>
</html>
