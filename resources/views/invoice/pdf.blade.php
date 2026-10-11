{{--
    Invoice PDF template (DomPDF) - Paper Ledger style.

    DomPDF rules we obey:
      - No flexbox / grid / sticky. Tables + floats only.
      - Explicit % column widths that sum to 100%.
      - @page sets page margins.
      - DejaVu Sans only (no external fonts, no icon glyphs).

    Data contract (from InvoiceController@download, unchanged):
      business_name, business_email, business_phone, business_address,
      business_website, business_tagline, business_tax,
      bank_name, account_title, account_number, iban, payment_method,
      signature_name, signature_title,
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

    /* NOTE: do NOT reset `html { margin: 0 }` — DomPDF then ignores the
       @page margins above and content bleeds to the sheet edge. */
    body {
        margin: 0;
        padding: 0;
        background-color: #FFFFFF;
        color: #12202E;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-size: 12px;
        line-height: 1.45;
    }

    td, th, p, div, li, span {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        color: #12202E;
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
        table-layout: fixed;
        width: 100%;
    }

    .cell-wrap { word-wrap: break-word; }
    .money    { text-align: right; white-space: nowrap; }

    .brand-muted { color: #4A5866; }

    /* ---------- Typography ---------- */
    .brand-name {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-weight: bold;
        font-size: 24px;
        color: #12202E;
        letter-spacing: -0.3px;
        line-height: 1.1;
    }

    .tagline {
        color: #4A5866;
        font-size: 10px;
        letter-spacing: 0.2px;
        line-height: 1.3;
    }

    .invoice-title {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-weight: bold;
        color: #12202E;
        letter-spacing: -0.5px;
        line-height: 1;
        font-size: 30px;
    }

    .section-label {
        color: #12202E;
        font-weight: bold;
        font-size: 12px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* Small grey caps labels (Email / Phone / Web / Address ...) */
    .mini-label {
        color: #8A94A0;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .client-name {
        color: #12202E;
        font-weight: bold;
        font-size: 12px;
        line-height: 1.35;
    }

    .client-line {
        color: #4A5866;
        font-size: 11px;
        line-height: 1.45;
    }

    /* ---------- Invoice meta (right upper) ---------- */
    .meta-label {
        color: #4A5866;
        font-size: 10.5px;
        text-align: right;
    }
    .meta-value {
        color: #12202E;
        font-weight: bold;
        font-size: 11px;
        text-align: right;
        white-space: nowrap;
    }

    /* ---------- Status badge ---------- */
    .status-badge {
        display: inline-block;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 2px 8px;
        border-radius: 10px;
        border: 1px solid #D6DCE2;
        background-color: #ECEFF2;
        color: #4A5866;
    }

    /* ---------- Brand logo (saffron square + 3 lines) ---------- */
    .brand-logo {
        width: 44px;
        height: 44px;
        background-color: #F2A33A;
        border-radius: 8px;
        text-align: center;
        vertical-align: middle;
    }
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
        border: 1px solid #E6E9EC;
    }
    .items thead th {
        background-color: #F1F3F5;
        color: #12202E;
        font-weight: bold;
        font-size: 11px;
        letter-spacing: 0.1px;
        text-align: left;
        border-bottom: 1px solid #E6E9EC;
        padding: 9px 10px;
    }
    .items thead th.center { text-align: center; }
    .items thead th.money  { text-align: right;  }

    .items tbody td {
        padding: 10px;
        border-bottom: 1px solid #EEF0F2;
        color: #12202E;
        font-size: 12px;
        vertical-align: top;
    }
    .items tbody td.center { text-align: center; }
    .items tbody td.money  { text-align: right; white-space: nowrap; }
    .items tbody tr:last-child td { border-bottom: none; }

    .item-name {
        color: #12202E;
        font-weight: bold;
        font-size: 12px;
        line-height: 1.35;
    }
    .item-details {
        color: #4A5866;
        font-size: 10px;
        line-height: 1.35;
        margin-top: 2px;
    }

    /* ---------- Payment details card (cream) ---------- */
    .pay-card {
        background-color: #FAF6EE;
        border: 1px solid #EAE3D2;
        border-radius: 6px;
        padding: 12px 14px;
    }
    .pay-title {
        color: #12202E;
        font-weight: bold;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }
    .pay-row {
        font-size: 11px;
        color: #12202E;
        line-height: 1.5;
    }

    /* ---------- Totals ---------- */
    .totals-tbl {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }
    .totals-tbl td {
        padding: 6px 10px;
        vertical-align: middle;
    }
    .totals-tbl td.lbl {
        color: #4A5866;
        font-size: 12px;
        text-align: left;
    }
    .totals-tbl td.val {
        color: #12202E;
        font-size: 12px;
        font-weight: bold;
        text-align: right;
        white-space: nowrap;
    }
    .totals-tbl tr.total td {
        background-color: #F2A33A;
        color: #12202E;
        font-weight: bold;
        font-size: 16px;
        padding: 8px 10px;
    }
    .totals-tbl tr.total td.lbl { color: #12202E; }
    .totals-tbl tr.total td.val { color: #12202E; font-size: 16px; }

    /* ---------- Notes ---------- */
    .notes-title {
        color: #12202E;
        font-weight: bold;
        font-size: 12px;
    }
    .notes-body {
        color: #4A5866;
        font-size: 12px;
        line-height: 1.5;
    }

    /* ---------- Footer divider ---------- */
    .soft-divider {
        height: 1px;
        background-color: #F2A33A;
        line-height: 1px;
    }

    /* ---------- Signature (plain sans, no script/italic) ---------- */
    .signature-line {
        border-top: 1.5px solid #12202E;
        width: 190px;
        margin: 0 0 6px auto;
    }
    .signature-name {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-weight: bold;
        color: #12202E;
        font-size: 13px;
        line-height: 1.2;
        text-align: right;
    }
    .signature-title {
        color: #4A5866;
        font-size: 10px;
        letter-spacing: 0.2px;
        text-align: right;
    }

    .sp { line-height: 1px; height: 1px; }
</style>
</head>
<body>

@php
    $money = fn ($v) => $currency_symbol . '&nbsp;' . number_format((float) $v, 2);

    $statusKey = strtolower(trim((string) ($status ?? '')));

    $statusMeta = [
        'paid'    => ['label' => 'Paid',    'bg' => '#E3F0E7', 'fg' => '#2F6B47', 'bd' => '#BFDDC9'],
        'pending' => ['label' => 'Pending', 'bg' => '#FCEBCF', 'fg' => '#B26A00', 'bd' => '#F2D6A8'],
        'overdue' => ['label' => 'Overdue', 'bg' => '#FBE4E2', 'fg' => '#B3372F', 'bd' => '#EEC3BF'],
        'draft'   => ['label' => 'Draft',   'bg' => '#ECEFF2', 'fg' => '#4A5866', 'bd' => '#D6DCE2'],
    ];
    $badge = $statusMeta[$statusKey] ?? null;

    $taxPct   = (float) ($totals['tax_pct'] ?? 0);
    $taxText  = rtrim(rtrim(number_format($taxPct, 2), '0'), '.');
    $taxLabel = $taxPct > 0 ? "Tax ({$taxText}%)" : 'Tax';

    $hasClient = !empty($client_name) && trim($client_name) !== '—';
    $hasSignature = filled($signature_name ?? null);

    $noteLines = collect(preg_split('/\r\n|\r|\n/', (string) $notes))
        ->map(fn ($l) => trim($l))
        ->filter()
        ->values();

    $hasBank = filled($bank_name ?? null)
        || filled($account_title ?? null)
        || filled($account_number ?? null)
        || filled($iban ?? null)
        || filled($payment_method ?? null);

    $sp = function ($px = 12) {
        echo '<table class="tbl-inner"><tr><td style="padding:' . $px . 'px 0 0 0;"></td></tr></table>';
    };
@endphp

{{-- ===========================================================
     TOP HEADER: Brand LEFT / "INVOICE" RIGHT
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="table-layout: auto;">
<tr>
    <td style="width: 8%; vertical-align: middle;">
        <div class="brand-logo">
            <div class="brand-logo-lines">
                <div></div>
                <div style="width: 18px; margin-left: auto; margin-right: auto;"></div>
                <div style="width: 14px; margin-left: auto; margin-right: auto;"></div>
            </div>
        </div>
    </td>
    <td style="width: 52%; vertical-align: middle; padding: 0 0 0 12px;" class="cell-wrap">
        <div class="brand-name">{{ $business_name }}</div>
        @if (filled($business_tagline ?? null))
            <div class="tagline" style="margin-top: 4px;">{{ $business_tagline }}</div>
        @endif
    </td>
    <td style="width: 40%; vertical-align: middle; text-align: right;">
        <div class="invoice-title">INVOICE</div>
    </td>
</tr>
</table>

{{$sp(24);}}

{{-- ===========================================================
     PARTIES: [ From | Bill To ] LEFT / Invoice meta RIGHT
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<colgroup>
    <col style="width: 60%;">
    <col style="width: 40%;">
</colgroup>
<tr>
    {{-- LEFT: From + Bill To side by side --}}
    <td style="vertical-align: top; padding-right: 16px;">
        <table class="tbl-inner" cellpadding="0" cellspacing="0">
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 50%;">
        </colgroup>
        <tr>
            {{-- From --}}
            <td style="vertical-align: top; padding-right: 12px;" class="cell-wrap">
                <div class="section-label" style="margin-bottom: 8px;">From</div>
                <div class="client-name">{{ $business_name }}</div>
                @if (filled($business_address ?? null))
                    <div class="client-line">
                        <span class="mini-label">Address</span>&nbsp; {{ $business_address }}
                    </div>
                @endif
                @if (filled($business_email ?? null))
                    <div class="client-line">
                        <span class="mini-label">Email</span>&nbsp; {{ $business_email }}
                    </div>
                @endif
                @if (filled($business_phone ?? null))
                    <div class="client-line">
                        <span class="mini-label">Phone</span>&nbsp; {{ $business_phone }}
                    </div>
                @endif
                @if (filled($business_website ?? null))
                    <div class="client-line">
                        <span class="mini-label">Web</span>&nbsp; {{ $business_website }}
                    </div>
                @endif
            </td>

            {{-- Bill To --}}
            <td style="vertical-align: top;" class="cell-wrap">
                <div class="section-label" style="margin-bottom: 8px;">Bill To</div>
                <div class="client-name">{{ $hasClient ? $client_name : '—' }}</div>
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

    {{-- RIGHT: Invoice details + status badge --}}
    <td style="vertical-align: top;">
        <table cellpadding="0" cellspacing="0" align="right" style="border-collapse: collapse;">
        <tr>
            <td class="meta-label" style="padding: 4px 10px 4px 0;">Invoice No:</td>
            <td class="meta-value" style="padding: 4px 0 4px 0;">
                {{ $invoice_number }}
                @if ($badge)
                    &nbsp;<span class="status-badge" style="background-color: {{ $badge['bg'] }}; color: {{ $badge['fg'] }}; border-color: {{ $badge['bd'] }};">{{ $badge['label'] }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="meta-label" style="padding: 4px 10px 4px 0;">Issue Date:</td>
            <td class="meta-value" style="padding: 4px 0 4px 0;">{{ $invoice_date }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding: 4px 10px 4px 0;">Due Date:</td>
            <td class="meta-value" style="padding: 4px 0 4px 0;">
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
     # 6% | Description 46% | Quantity 12% | Rate 18% | Amount 18%
     =========================================================== --}}
<table class="items" cellpadding="0" cellspacing="0">
<colgroup>
    <col style="width: 6%;">
    <col style="width: 46%;">
    <col style="width: 12%;">
    <col style="width: 18%;">
    <col style="width: 18%;">
</colgroup>
<thead>
<tr>
    <th class="center" style="width: 6%;">#</th>
    <th style="width: 46%;">Description</th>
    <th class="center" style="width: 12%;">Quantity</th>
    <th class="money" style="width: 18%;">Rate</th>
    <th class="money" style="width: 18%;">Amount</th>
</tr>
</thead>
<tbody>
@forelse ($items as $it)
    <tr style="page-break-inside: avoid;">
        <td class="center" style="font-weight: bold;">{{ $loop->iteration }}</td>
        <td class="cell-wrap">
            <div class="item-name">{{ $it['description'] }}</div>
            @if (filled($it['details'] ?? null))
                <div class="item-details">{{ $it['details'] }}</div>
            @endif
        </td>
        <td class="center">{{ $it['quantity'] }}</td>
        <td class="money">{!! $money($it['price']) !!}</td>
        <td class="money">{!! $money($it['line_total']) !!}</td>
    </tr>
@empty
    <tr>
        <td colspan="5" style="text-align: center; padding: 22px 10px; color: #4A5866; font-size: 12px;">
            No items on this invoice.
        </td>
    </tr>
@endforelse
</tbody>
</table>

{{$sp(18);}}

{{-- ===========================================================
     PAYMENT CARD (LEFT) + TOTALS (RIGHT, 45% aligned to Amount)
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<colgroup>
    <col style="width: 55%;">
    <col style="width: 45%;">
</colgroup>
<tr>
    {{-- LEFT: payment details card --}}
    <td style="vertical-align: top; padding-right: 20px;">
        @if ($hasBank)
        <table class="tbl-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="pay-card">
                    <div class="pay-title">Payment Details</div>
                    @if (filled($bank_name ?? null))
                        <div class="pay-row"><span class="mini-label">Bank</span>&nbsp; {{ $bank_name }}</div>
                    @endif
                    @if (filled($account_title ?? null))
                        <div class="pay-row"><span class="mini-label">Account Title</span>&nbsp; {{ $account_title }}</div>
                    @endif
                    @if (filled($account_number ?? null))
                        <div class="pay-row"><span class="mini-label">Account No</span>&nbsp; {{ $account_number }}</div>
                    @endif
                    @if (filled($iban ?? null))
                        <div class="pay-row"><span class="mini-label">IBAN</span>&nbsp; {{ $iban }}</div>
                    @endif
                    @if (filled($payment_method ?? null))
                        <div class="pay-row"><span class="mini-label">Method</span>&nbsp; {{ $payment_method }}</div>
                    @endif
                </div>
            </td>
        </tr>
        </table>
        @endif
    </td>

    {{-- RIGHT: totals block (fills the 45% column; right edge = Amount column) --}}
    <td style="vertical-align: top;">
        <table class="totals-tbl" cellpadding="0" cellspacing="0">
        <colgroup>
            <col style="width: 60%;">
            <col style="width: 40%;">
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
        <tr>
            <td class="lbl">
                @php
                    $subtotalCents = round((float) $totals['subtotal'] * 100);
                    $discCents = round((float) $totals['discount'] * 100);
                    $discPct = 0;
                    $discPctText = '';
                    if ($subtotalCents > 0) {
                        $discPct = round(($discCents / $subtotalCents) * 100, 2);
                        $discPctText = rtrim(rtrim(number_format($discPct, 2), '0'), '.');
                    }
                @endphp
                @if ($discPctText !== '' && $discPct > 0)
                    Discount ({{ $discPctText }}%)
                @else
                    Discount
                @endif
            </td>
            <td class="val">- {!! $money($totals['discount']) !!}</td>
        </tr>
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
            <div class="notes-body" style="{{ !$loop->first ? 'margin-top: 1px;' : '' }}">{{ $line }}</div>
        @endforeach
    </td>
</tr>
</table>
@endif

{{$sp(28);}}

{{-- Soft saffron divider before footer --}}
<table class="tbl-inner" cellpadding="0" cellspacing="0">
<tr><td><div class="soft-divider"></div></td></tr>
</table>

{{$sp(14);}}

{{-- ===========================================================
     FOOTER: thank-you LEFT / signature RIGHT
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<colgroup>
    <col style="width: 60%;">
    <col style="width: 40%;">
</colgroup>
<tr>
    <td style="vertical-align: bottom;">
        <p class="client-line" style="margin: 0;">Thank you for your business.</p>
    </td>

    <td style="vertical-align: bottom;">
        @if ($hasSignature)
            <table class="tbl-inner" cellpadding="0" cellspacing="0" align="right">
            <tr>
                <td style="text-align: right;">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $signature_name }}</div>
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
