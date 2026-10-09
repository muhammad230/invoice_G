{{--
    ==========================================================================
    Invoice PDF template (DomPDF)

    Constraints (DomPDF-safe):
      - Standalone HTML, one inline <style> block, no external assets.
      - Layout uses only tables / borders / padding — no flex, grid,
        fixed positioning, transforms, gradients or box-shadows.
      - Fonts: DejaVu Sans (body) + DejaVu Serif (wordmark & big numbers),
        both bundled with DomPDF so Rs / € / £ render correctly.
      - A4 with @page margin 0; a white "sheet" floats on a cream strip.
      - Rows/boxes use page-break-inside: avoid so pages split cleanly.

    Variables provided by InvoiceController@download (unchanged):
      invoice_number, invoice_date, due_date, status,
      business_name, business_email, business_phone, business_address,
      business_tax, logo_data,
      client_name, client_email, client_phone, client_address,
      items[] = description, quantity, price, line_total,
      currency_symbol,
      totals = subtotal, tax, tax_pct, discount, total,
      notes
    ==========================================================================
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice {{ $invoice_number }}</title>
<style>
    /* ================= Page setup =====================================
       A4, no printer margin. The body is cream so a thin strip frames
       the white sheet; @page margin 0 keeps edges predictable. */
    @page { margin: 0; size: A4; }

    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }

    body {
        background: #FAF6EE;                              /* cream page strip */
        color: #12202E;                                   /* navy text */
        font-family: "DejaVu Sans", Helvetica, Arial, sans-serif;
        font-size: 12px;
        line-height: 1.35;
    }

    /* White paper sitting on the cream strip */
    .sheet { background: #FFFFFF; margin: 4px; }

    /* Spacers (tables-only layout: use divs with height instead of margins) */
    .gap    { height: 10px; }

    /* ================= 2. Header band =================================
       Full-width navy block; saffron accent line directly beneath it. */
    .band { width: 100%; background: #12202E; padding: 28px 40px; border-collapse: collapse; }
    .band td { vertical-align: middle; }

    .band-logo { max-height: 50px; max-width: 190px; display: block; margin-bottom: 8px; }
    .brand-name {
        color: #FFFFFF;
        font-size: 17px;
        font-weight: bold;
        line-height: 1.3;
    }

    .band-right  { text-align: right; }
    .wordmark {
        font-family: "DejaVu Serif", Georgia, serif;      /* serif title */
        font-weight: bold;
        font-size: 28px;
        color: #FFFFFF;
        line-height: 1;
        letter-spacing: 1px;
    }
    .band-number {
        font-family: "DejaVu Serif", Georgia, serif;
        font-weight: bold;
        font-size: 14px;
        color: #F2A33A;                                   /* saffron number */
        margin-top: 6px;
    }
    .accent { width: 100%; height: 4px; background: #F2A33A; }  /* 4px saffron line */

    /* Inner wrapper: 40px padding around all page content */
    .wrap { padding: 40px; }

    /* ================= 3. Info row (3 columns) ======================= */
    .info { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
    .info td { width: 33.33%; vertical-align: top; padding: 0 20px; }
    .info .col-first  { padding-left: 0; }
    .info .col-last   { padding-right: 0; }

    /* Small grey uppercase labels */
    .lbl {
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #4A5866;
        margin-bottom: 5px;
    }
    .val  { font-size: 12px; color: #12202E; margin-bottom: 2px; }
    .val-strong { font-size: 13px; font-weight: bold; margin-bottom: 3px; }

    /* ================= 4. Status badge =============================== */
    .badge {
        display: inline-block;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 3px 10px;
        border-radius: 3px;                               /* small rounded box */
        border: 1px solid transparent;
    }
    .badge-paid    { color: #3F6B50; background: #E6F2EC; border-color: #CBE3D6; } /* green  */
    .badge-pending { color: #C97F14; background: #FBF0D8; border-color: #F2E1BB; } /* orange */
    .badge-overdue { color: #B3372F; background: #FBE4E2; border-color: #F4D0CC; } /* red    */
    .badge-draft   { color: #4A5866; background: #F1EEE8; border-color: #E4DFD4; } /* grey   */

    /* ================= 5. Items table ================================ */
    .items { width: 100%; border-collapse: collapse; }
    /* Repeat the navy header on every printed page */
    .items thead { display: table-header-group; }
    .items thead th {
        background: #12202E;                              /* navy header row */
        color: #FFFFFF;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 7px 12px;
        text-align: left;
    }
    .items thead th.num { text-align: right; }

    .items tbody td {
        font-size: 12px;
        padding: 10px 12px;                               /* 10px vertical padding */
        border-bottom: 1px solid #E8E4DA;                 /* thin bottom borders */
        vertical-align: top;
        word-wrap: break-word;                            /* long text wraps */
        overflow-wrap: break-word;
    }
    .items tbody td.num { text-align: right; }            /* numbers right-aligned */
    .items tbody tr { page-break-inside: avoid; }         /* rows never split */
    .items tbody tr.alt td { background: #FAF6EE; }       /* zebra: cream rows */

    .items-empty {
        font-size: 12px;
        color: #9A9589;
        font-style: italic;
        padding: 10px 12px;
        border-bottom: 1px solid #E8E4DA;
    }

    /* ================= 6. Totals box (right-aligned, ~45%) =========== */
    .totals-outer { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
    .totals {
        width: 45%;
        border-collapse: collapse;
        border: 1px solid #E8E4DA;
        page-break-inside: avoid;
    }
    .totals td {
        padding: 4px 12px;
        font-size: 12px;
        border-bottom: 1px solid #EFEBE1;
    }
    .totals .t-label { color: #4A5866; text-align: left; }
    .totals .t-value { color: #12202E; text-align: right; white-space: nowrap; }

    /* Final row: saffron background, navy bold 16px serif text */
    .totals .total-row td {
        background: #F2A33A;
        border-bottom: none;
        border-top: 2px solid #12202E;
        padding: 8px 12px;
        font-family: "DejaVu Serif", Georgia, serif;
        font-size: 16px;
        font-weight: bold;
        color: #12202E;
    }

    /* ================= 7. Notes box =================================== */
    .notes { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
    .notes td {
        background: #FAF6EE;                              /* light cream box */
        border: 1px solid #EFE8DA;
        padding: 10px 14px;
    }
    .notes .lbl { margin-bottom: 4px; }
    .notes-text {
        font-size: 11px;
        color: #12202E;
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.5;
    }

    /* ================= 8. Footer (static, after content) ============== */
    .footer {
        margin-top: 14px;
        padding-top: 8px;
        border-top: 1px solid #E8E4DA;                    /* thin line */
        text-align: center;
        font-size: 10px;
        color: #4A5866;
        page-break-inside: avoid;
    }
    .footer .thanks { letter-spacing: 0.5px; margin-bottom: 3px; }
</style>
</head>
<body>

{{-- ====================================================================
     Shared, safe formatting helpers (view-only; controller untouched)
     ==================================================================== --}}
@php
    // Money formatter: symbol + 2 decimals (values arrive in major units
    // because the controller already divided minor units by 100).
    $money = fn ($v) => $currency_symbol . ' ' . number_format((float) $v, 2);

    // Logo is only usable as a data-URI (DomPDF cannot fetch external URLs).
    $logoOk = !empty($logo_data) && is_string($logo_data)
              && str_starts_with(trim($logo_data), 'data:image/');

    // Status → badge class + human label. Unknown/empty values fall back
    // to a neutral grey badge (or are hidden entirely below).
    $statusKey = strtolower(trim((string) ($status ?? '')));
    [$badgeClass, $badgeLabel] = match ($statusKey) {
        'paid'    => ['badge-paid', 'Paid'],
        'pending' => ['badge-pending', 'Pending'],
        'overdue' => ['badge-overdue', 'Overdue'],
        'draft'   => ['badge-draft', 'Draft'],
        default   => ['badge-draft', $statusKey === '' ? '' : ucfirst($statusKey)],
    };

    // Tax label with its percentage, e.g. "Tax (15%)" — trims trailing zeros.
    $taxPct   = (float) ($totals['tax_pct'] ?? 0);
    $taxText  = rtrim(rtrim(number_format($taxPct, 2), '0'), '.');
    $taxLabel = $taxPct > 0 ? "Tax ({$taxText}%)" : 'Tax';

    // Client name guard: controller may put an em-dash placeholder there.
    $hasClient = !empty($client_name) && trim($client_name) !== '—';

    // Email + phone share one line in the info row and the footer (keeps
    // both compact; either may be missing — empty values are omitted).
    $clientLine = implode('  ·  ', array_filter([
        trim((string) ($client_email ?? '')),
        trim((string) ($client_phone ?? '')),
    ]));
    $businessLine = implode('  ·  ', array_filter([
        trim((string) ($business_email ?? '')),
        trim((string) ($business_phone ?? '')),
    ]));
@endphp

{{--
     1. SHEET: white paper on the cream strip (wraps ALL page content)
     ==================================================================== --}}
<div class="sheet">

{{-- ====================================================================
     2. HEADER BAND — navy, logo + business (left) / INVOICE + number (right)
     ==================================================================== --}}
<table class="band" cellpadding="0" cellspacing="0">
<tr>
    {{-- Left: logo (optional) + business name in white --}}
    <td style="width:60%;">
        @if ($logoOk)
            <img src="{{ $logo_data }}" class="band-logo" alt="Logo">
        @endif
        <div class="brand-name">{{ $business_name }}</div>
    </td>

    {{-- Right: wordmark + invoice number in saffron --}}
    <td class="band-right" style="width:40%;">
        <div class="wordmark">INVOICE</div>
        <div class="band-number"># {{ $invoice_number }}</div>
    </td>
</tr>
</table>

{{-- 4px saffron accent line directly under the band --}}
<div class="accent"></div>

{{-- Inner wrapper: all page content sits inside 40px padding --}}
<div class="wrap">

    {{-- ==================================================================
         3. INFO ROW — Billed to | From | Details
         ================================================================== --}}
    <table class="info" cellpadding="0" cellspacing="0">
    <tr>
        {{-- Billed to --}}
        <td class="col-first">
            <div class="lbl">Billed to</div>
            @if ($hasClient)
                <div class="val-strong">{{ $client_name }}</div>
            @endif
            @if ($clientLine !== '')
                <div class="val">{{ $clientLine }}</div>
            @endif
            @if (!empty($client_address))
                <div class="val">{{ $client_address }}</div>
            @endif
        </td>

        {{-- From (business) --}}
        <td>
            <div class="lbl">From</div>
            <div class="val-strong">{{ $business_name }}</div>
            @if ($businessLine !== '')
                <div class="val">{{ $businessLine }}</div>
            @endif
            @if (!empty($business_address))
                <div class="val">{{ $business_address }}</div>
            @endif
            @if (!empty($business_tax))
                <div class="val">Tax / VAT: {{ $business_tax }}</div>
            @endif
        </td>

        {{-- Details: dates + status badge --}}
        <td class="col-last">
            <div class="lbl">Details</div>
            @if (!empty($invoice_date))
                <div class="val">Invoice date: <strong>{{ $invoice_date }}</strong></div>
            @endif
            @if (!empty($due_date))
                <div class="val">Due date: <strong>{{ $due_date }}</strong></div>
            @endif
            @if ($badgeLabel !== '')
                <div class="val" style="margin-top:4px;">
                    <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </div>
            @endif
        </td>
    </tr>
    </table>

    <div class="gap"></div>

    {{-- ==================================================================
         5. ITEMS TABLE — navy header, zebra rows, right-aligned numbers
         ================================================================== --}}
    <table class="items" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="width:48%;">Description</th>
            <th class="num" style="width:12%;">Qty</th>
            <th class="num" style="width:20%;">Price</th>
            <th class="num" style="width:20%;">Amount</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($items as $it)
            {{-- Zebra striping via loop index; rows avoid page breaks --}}
            <tr class="{{ $loop->index % 2 === 1 ? 'alt' : '' }}">
                <td>{{ $it['description'] }}</td>
                <td class="num">{{ $it['quantity'] }}</td>
                <td class="num">{{ $money($it['price']) }}</td>
                <td class="num">{{ $money($it['line_total']) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="items-empty">No items on this invoice.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="gap"></div>

    {{-- ==================================================================
         6. TOTALS — right-aligned ~45% box; saffron total row
         ================================================================== --}}
    <table class="totals-outer" cellpadding="0" cellspacing="0">
    <tr>
        <td align="right">
            <table class="totals" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="t-label">Subtotal</td>
                    <td class="t-value">{{ $money($totals['subtotal']) }}</td>
                </tr>
                @if ($taxPct > 0 || (float) $totals['tax'] > 0)
                    <tr>
                        <td class="t-label">{{ $taxLabel }}</td>
                        <td class="t-value">{{ $money($totals['tax']) }}</td>
                    </tr>
                @endif
                @if ((float) $totals['discount'] > 0)
                    <tr>
                        <td class="t-label">Discount</td>
                        <td class="t-value">- {{ $money($totals['discount']) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td class="t-label">Total</td>
                    <td class="t-value">{{ $money($totals['total']) }}</td>
                </tr>
            </table>
        </td>
    </tr>
    </table>

    {{-- ==================================================================
         7. NOTES — cream box, only when notes exist
         ================================================================== --}}
    @if (!empty(trim((string) $notes)))
        <div class="gap"></div>
        <table class="notes" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="lbl">Notes</div>
                <div class="notes-text">{{ $notes }}</div>
            </td>
        </tr>
        </table>
    @endif

    {{-- ==================================================================
         8. FOOTER — thin line + centered grey thanks line (static flow)
         ================================================================== --}}
    <div class="footer">
        <div class="thanks">Thank you for your business</div>
        @if ($businessLine !== '')
            <div>{{ $businessLine }}</div>
        @endif
    </div>

</div>{{-- /.wrap --}}
</div>{{-- /.sheet --}}
</body>
</html>
