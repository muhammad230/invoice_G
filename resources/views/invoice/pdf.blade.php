{{--
    Invoice PDF template (DomPDF) - Minimal Paper Ledger style.
    Matches the InvoiceFlow website theme: #FAF6EE paper background,
    #12202E ink primary, #F2A33A / #C97F14 saffron accents.

    DomPDF rules we must obey:
      - No flexbox / grid. Use table + float.
      - Tables with columns use explicit % widths summing to exactly 100%.
      - @page sets page margins; no wrapper div with padding + width:100%.
      - DejaVu Sans + DejaVu Serif only (no external fonts).

    Data contract (passed from InvoiceController@download, unchanged):
      business_name, business_email, business_phone, business_address,
      business_website, business_tagline, bank_name, account_title,
      account_number, iban, payment_method, signature_name, signature_title,
      client_name, client_email, client_phone, client_address,
      invoice_number, invoice_date, due_date, status, currency,
      currency_symbol, items[], totals[], notes, logo_data.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice {{ $invoice_number }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 28px 36px;
    }

    html, body {
        margin: 0;
        padding: 0;
        background-color: #FAF6EE;
    }

    body {
        color: #12202E;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.45;
    }

    td, th, p, div, li, span {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        color: #12202E;
        font-size: 11px;
        line-height: 1.45;
    }

    /* ---------- Reusable table primitives ---------- */
    .tbl {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }
    .tbl-inner {
        border-collapse: collapse;
        table-layout: auto;
        width: auto;
    }
    .tbl-full {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }

    .cell-wrap { word-wrap: break-word; }
    .money    { text-align: right; white-space: nowrap; }

    /* ---------- Brand palette (Paper Ledger) ---------- */
    .brand-blue       { color: #C97F14; }
    .brand-blue-bg    { background-color: #F2A33A; }
    .brand-blue-soft  { background-color: #FAF6EE; }
    .brand-ink        { color: #12202E; }
    .brand-muted      { color: #4A5866; }

    /* ---------- Typography ---------- */
    .brand-name {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-weight: bold;
        color: #12202E;
        letter-spacing: -0.3px;
        line-height: 1;
    }
    .brand-name .accent { color: #C97F14; }

    .tagline {
        color: #4A5866;
        font-size: 9.5px;
        letter-spacing: 0.2px;
        line-height: 1.2;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    .invoice-title {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-weight: bold;
        color: #12202E;
        letter-spacing: -0.5px;
        line-height: 1;
        font-size: 36px;
    }

    .section-label {
        color: #12202E;
        font-weight: bold;
        font-size: 12px;
        letter-spacing: 0.1px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    .client-name {
        color: #12202E;
        font-weight: bold;
        font-size: 13px;
        line-height: 1.3;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    .client-line {
        color: #4A5866;
        font-size: 11px;
        line-height: 1.45;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Invoice meta (right upper) ---------- */
    .meta-label {
        color: #4A5866;
        font-size: 10.5px;
        text-align: left;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .meta-value {
        color: #12202E;
        font-weight: bold;
        font-size: 11px;
        text-align: right;
        white-space: nowrap;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Brand logo (saffron square + 3 lines) ---------- */
    .brand-logo {
        width: 44px;
        height: 44px;
        background-color: #F2A33A;
        border-radius: 8px;
        text-align: center;
        vertical-align: middle;
        position: relative;
    }
    /* Three white horizontal lines inside the saffron square */
    .brand-logo-lines {
        text-align: center;
        padding-top: 13px;
    }
    .brand-logo-lines div {
        width: 22px;
        height: 3px;
        background-color: #FFFFFF;
        margin: 2px auto 0 auto;
        border-radius: 1px;
    }

    /* ---------- Items table ---------- */
    .items {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
        border: 1px solid #EAE3D2;
        border-radius: 6px;
    }
    .items thead th {
        background-color: #FAF6EE;
        color: #12202E;
        font-weight: bold;
        font-size: 11px;
        letter-spacing: 0.1px;
        text-align: left;
        border-bottom: 1px solid #EAE3D2;
        padding: 10px 10px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .items thead th.center { text-align: center; }
    .items thead th.money  { text-align: right;  }

    .items tbody td {
        padding: 10px 10px;
        border-bottom: 1px solid #EAE3D2;
        color: #12202E;
        font-size: 11px;
        vertical-align: top;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .items tbody td.center   { text-align: center; }
    .items tbody td.money    { text-align: right; white-space: nowrap; }

    .items tbody tr:last-child td { border-bottom: none; }

    .item-name {
        color: #12202E;
        font-weight: 600;
        font-size: 11px;
        line-height: 1.35;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .item-details {
        color: #4A5866;
        font-size: 9.5px;
        line-height: 1.35;
        margin-top: 2px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Totals (right-aligned mini table under items) ---------- */
    .totals-wrap {
        width: 48%;
        margin-left: 52%;
    }
    .totals-tbl {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }
    .totals-tbl td {
        padding: 6px 12px;
        vertical-align: middle;
    }
    .totals-tbl td.lbl {
        color: #4A5866;
        font-size: 11px;
        text-align: left;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .totals-tbl td.val {
        color: #12202E;
        font-size: 11px;
        font-weight: bold;
        text-align: right;
        white-space: nowrap;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .totals-tbl tr.total td {
        background-color: #FAF6EE;
        padding: 9px 12px;
    }
    .totals-tbl tr.total td.lbl {
        color: #C97F14;
        font-weight: bold;
        font-size: 12px;
    }
    .totals-tbl tr.total td.val {
        color: #12202E;
        font-size: 14px;
    }
    .totals-tbl tr.above-total td {
        padding-bottom: 8px;
    }

    /* ---------- Notes ---------- */
    .notes-title {
        color: #12202E;
        font-weight: bold;
        font-size: 13px;
        letter-spacing: 0.1px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .notes-body {
        color: #4A5866;
        font-size: 11px;
        line-height: 1.5;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Footer divider ---------- */
    .soft-divider {
        height: 1px;
        background-color: #F2A33A;
        line-height: 1px;
    }

    /* ---------- Footer: contact icons left / signature right ---------- */
    .contact-row {
        padding: 4px 0;
        vertical-align: middle;
    }
    .contact-icon {
        color: #C97F14;
        font-size: 13px;
        width: 20px;
        text-align: center;
        vertical-align: middle;
    }
    .contact-text {
        color: #4A5866;
        font-size: 10.5px;
        padding-left: 4px;
        vertical-align: middle;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    .signature-name {
        font-family: 'DejaVu Serif', Georgia, serif;
        font-style: italic;
        color: #12202E;
        font-size: 18px;
        line-height: 1.1;
        letter-spacing: 0.4px;
        text-align: right;
    }
    .signature-line {
        border-top: 1.5px solid #12202E;
        width: 190px;
        margin: 6px 0 6px auto;
    }
    .signature-title {
        color: #4A5866;
        font-size: 9.5px;
        letter-spacing: 0.2px;
        text-align: right;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Helpers ---------- */
    .sp { line-height: 1px; height: 1px; }
</style>
</head>
<body>

@php
    $money = fn ($v) => $currency_symbol . '&nbsp;' . number_format((float) $v, 2);

    $statusKey = strtolower(trim((string) ($status ?? '')));

    $taxPct   = (float) ($totals['tax_pct'] ?? 0);
    $taxText  = rtrim(rtrim(number_format($taxPct, 2), '0'), '.');
    $taxLabel = $taxPct > 0 ? "Tax ({$taxText}%)" : 'Tax';

    $hasClient = !empty($client_name) && trim($client_name) !== '—';

    $hasSignature = filled($signature_name ?? null);

    $noteLines = collect(preg_split('/\r\n|\r|\n/', (string) $notes))
        ->map(fn ($l) => trim($l))
        ->filter()
        ->values();

    $displayBusinessTagline = filled($business_tagline ?? null)
        ? $business_tagline
        : 'Simple Invoices. Better Business.';

    $sp = function ($px = 12) {
        echo '<table class="tbl-inner"><tr><td style="padding:' . $px . 'px 0 0 0;"></td></tr></table>';
    };
@endphp

{{-- ===========================================================
     TOP HEADER: Brand (logo + name + tagline) LEFT / "INVOICE" RIGHT
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0">
<colgroup>
    <col style="width: 58%;">
    <col style="width: 42%;">
</colgroup>
<tr>
    <td style="vertical-align: middle;">
        <table class="tbl-full" cellpadding="0" cellspacing="0">
        <tr>
            {{-- Logo mark (saffron square + 3 white lines) --}}
            <td style="width: 56px; vertical-align: middle;">
                <div class="brand-logo">
                    <div class="brand-logo-lines">
                        <div></div>
                        <div style="width: 18px; margin-left: auto; margin-right: auto;"></div>
                        <div style="width: 14px; margin-left: auto; margin-right: auto;"></div>
                    </div>
                </div>
            </td>
            {{-- Brand name + tagline --}}
            <td style="vertical-align: middle; padding: 0 0 0 12px;" class="cell-wrap">
                <div class="brand-name" style="font-size: 26px;">
                    Invoice<span class="accent">Flow</span>
                </div>
                <div class="tagline" style="margin-top: 3px;">
                    {!! $displayBusinessTagline !!}
                </div>
            </td>
        </tr>
        </table>
    </td>

    <td style="vertical-align: middle; text-align: right;">
        <div class="invoice-title">INVOICE</div>
    </td>
</tr>
</table>

{{$sp(24);}}

{{-- ===========================================================
     META: Bill To (LEFT) / Invoice No + Dates (RIGHT)
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<colgroup>
    <col style="width: 58%;">
    <col style="width: 42%;">
</colgroup>
<tr>
    {{-- LEFT: Bill To --}}
    <td style="vertical-align: top;">
        <div class="section-label" style="margin-bottom: 10px;">Bill To</div>
        <table class="tbl-full" cellpadding="0" cellspacing="0">
        <tr>
            <td class="cell-wrap">
                <div class="client-name">
                    {{ $hasClient ? $client_name : '—' }}
                </div>
                @if (!empty($client_email))
                    <div class="client-line">{{ $client_email }}</div>
                @endif
                @if (!empty($client_phone))
                    <div class="client-line">{{ $client_phone }}</div>
                @endif
                @if (!empty($client_address))
                    <div class="client-line">{{ $client_address }}</div>
                @endif
            </td>
        </tr>
        </table>
    </td>

    {{-- RIGHT: Invoice details (label : value, rows aligned) --}}
    <td style="vertical-align: top;">
        <table class="tbl-full" cellpadding="0" cellspacing="0">
        <colgroup>
            <col style="width: 45%;">
            <col style="width: 55%;">
        </colgroup>
        <tr>
            <td class="meta-label" style="padding: 4px 10px 4px 0;">Invoice No:</td>
            <td class="meta-value"  style="padding: 4px 0 4px 10px;">{{ $invoice_number }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding: 4px 10px 4px 0;">Issue Date:</td>
            <td class="meta-value"  style="padding: 4px 0 4px 10px;">{{ $invoice_date }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding: 4px 10px 4px 0;">Due Date:</td>
            <td class="meta-value"  style="padding: 4px 0 4px 10px;">
                @if (!empty($due_date))
                    {{ $due_date }}
                @else
                    <span class="brand-muted">—</span>
                @endif
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

{{$sp(22);}}

{{-- ===========================================================
     ITEMS TABLE
     =========================================================== --}}
<table class="items" cellpadding="0" cellspacing="0">
<colgroup>
    <col style="width: 7%;">
    <col style="width: 45%;">
    <col style="width: 13%;">
    <col style="width: 17.5%;">
    <col style="width: 17.5%;">
</colgroup>
<thead>
<tr>
    <th class="center">#</th>
    <th>Description</th>
    <th class="center">Quantity</th>
    <th class="money">Rate</th>
    <th class="money">Amount</th>
</tr>
</thead>
<tbody>
@forelse ($items as $it)
    <tr style="page-break-inside: avoid;">
        <td class="center" style="font-weight: bold;">
            {{ $loop->iteration }}
        </td>
        <td class="cell-wrap">
            <div class="item-name">{{ $it['description'] }}</div>
            @if (filled($it['details'] ?? null))
                <div class="item-details">{{ $it['details'] }}</div>
            @endif
        </td>
        <td class="center">
            {{ $it['quantity'] }}
        </td>
        <td class="money">
            {!! $money($it['price']) !!}
        </td>
        <td class="money">
            {!! $money($it['line_total']) !!}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" style="text-align: center; padding: 22px 10px; color: #64748B; font-style: italic; font-size: 11px;">
            No items on this invoice.
        </td>
    </tr>
@endforelse
</tbody>
</table>

{{$sp(18);}}

{{-- ===========================================================
     TOTALS: Right-aligned mini panel
     =========================================================== --}}
<table class="tbl-inner" cellpadding="0" cellspacing="0" align="right">
<tr>
<td>
<table class="totals-tbl" cellpadding="0" cellspacing="0" style="width: 340px;">
<colgroup>
    <col style="width: 55%;">
    <col style="width: 45%;">
</colgroup>
    <tr>
        <td class="lbl">Subtotal</td>
        <td class="val">{!! $money($totals['subtotal']) !!}</td>
    </tr>
    @if ($taxPct > 0 || (float) $totals['tax'] > 0)
    <tr>
        <td class="lbl">{{ $taxLabel }}</td>
        <td class="val">{!! $money($totals['tax']) !!}</td>
    </tr>
    @endif
    @if ((float) $totals['discount'] > 0)
    <tr class="above-total">
        <td class="lbl">
            @php
                $subtotalCents = round((float) $totals['subtotal'] * 100);
                $discCents = round((float) $totals['discount'] * 100);
                $discPct = 0;
                if ($subtotalCents > 0) {
                    $discPct = round(($discCents / $subtotalCents) * 100, 2);
                    $discPctText = rtrim(rtrim(number_format($discPct, 2), '0'), '.');
                }
            @endphp
            @if (!empty($discPctText) && $discPct > 0)
                Discount ({{ $discPctText }}%)
            @else
                Discount
            @endif
        </td>
        <td class="val">- {!! $money($totals['discount']) !!}</td>
    </tr>
    @else
        {{-- If no discount row, mark whatever last row above-total so spacing is consistent --}}
        @if (!($taxPct > 0 || (float) $totals['tax'] > 0))
        <tr class="above-total"><td colspan="2" style="padding: 0; height: 0;"></td></tr>
        @endif
    @endif
    <tr class="total">
        <td class="lbl">Total</td>
        <td class="val">{!! $money($totals['total']) !!}</td>
    </tr>
</table>
</td>
</tr>
</table>

{{$sp(28);}}

{{-- ===========================================================
     NOTES (full width, left)
     =========================================================== --}}
@if ($noteLines->isNotEmpty())
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td class="cell-wrap">
        <div class="notes-title" style="margin-bottom: 8px;">Notes</div>
        @foreach ($noteLines as $line)
            <div class="notes-body" style="{{ !$loop->first ? 'margin-top: 1px;' : '' }}">
                {{ $line }}
            </div>
        @endforeach
    </td>
</tr>
</table>
@endif

{{$sp(28);}}

{{-- Soft blue divider before footer --}}
<table class="tbl-full" cellpadding="0" cellspacing="0">
<tr><td><div class="soft-divider"></div></td></tr>
</table>

{{$sp(14);}}

{{-- ===========================================================
     FOOTER: Contact rows (icons + text) LEFT / Signature RIGHT
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<colgroup>
    <col style="width: 58%;">
    <col style="width: 42%;">
</colgroup>
<tr>
    {{-- LEFT: Contact rows (email, web, phone with icons) --}}
    <td style="vertical-align: bottom;">
        <table class="tbl-inner" cellpadding="0" cellspacing="0">
        @if (!empty($business_email))
        <tr class="contact-row">
            <td class="contact-icon">&#9993;</td>
            <td class="contact-text">{{ $business_email }}</td>
        </tr>
        @endif
        @if (filled($business_website ?? null))
        <tr class="contact-row">
            <td class="contact-icon">&#127760;</td>
            <td class="contact-text">{{ $business_website }}</td>
        </tr>
        @endif
        @if (!empty($business_phone))
        <tr class="contact-row">
            <td class="contact-icon">&#128222;</td>
            <td class="contact-text">{{ $business_phone }}</td>
        </tr>
        @endif
        @if (!empty($business_address))
        <tr class="contact-row">
            <td class="contact-icon">&#127968;</td>
            <td class="contact-text">{{ $business_address }}</td>
        </tr>
        @endif
        </table>
    </td>

    {{-- RIGHT: Signature block --}}
    <td style="vertical-align: bottom;">
        @if ($hasSignature)
            <table class="tbl-inner" cellpadding="0" cellspacing="0" align="right">
            <tr>
                <td style="text-align: right;">
                    <div class="signature-name">
                        {{ $signature_name }}
                    </div>
                    <div class="signature-line"></div>
                    <div class="signature-title">
                        {{ filled($signature_title ?? null) ? $signature_title : 'Authorized Signature' }}
                    </div>
                </td>
            </tr>
            </table>
        @endif
    </td>
</tr>
</table>

</body>
</html>
