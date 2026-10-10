{{--
    Invoice PDF template (DomPDF) - Paper Ledger theme, redesigned.
    DomPDF compatibility rules:
      - @page sets page margins; NO wrapper div with padding + width:100%
      - Width-bearing <td>s have ZERO padding/margin/border
      - Padding lives ONLY on inner (non-width-bearing) wrappers
      - Every <table> with columns has explicit % widths summing to exactly 100%
      - No box-sizing, no width:100% on anything with padding/border/margin

    Design tokens (from web style.css):
      --paper #FAF6EE   --ink #12202E   --ink-soft #4A5866
      --saffron #F2A33A --saffron-dark #C97F14   --sage #3F6B50
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice {{ $invoice_number }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 30px 38px;
    }

    html, body {
        margin: 0;
        padding: 0;
        background-color: #FFFFFF;
    }

    body {
        color: #12202E;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
    }

    td, th, p, div, li {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        color: #12202E;
        font-size: 11px;
        line-height: 1.4;
    }

    /* ---------- Structural table layers ---------- */
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
    .tbl-full-inner {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }

    .text-cell { word-wrap: break-word; }
    .money     { text-align: right; white-space: nowrap; }

    /* ---------- Brand mark + theme palette ---------- */
    .brand-ink    { color: #12202E; }
    .brand-soft   { color: #4A5866; }
    .brand-saffron{ color: #C97F14; }
    .brand-sage   { color: #3F6B50; }
    .saffron-bar  { background-color: #F2A33A; }
    .ink-bar      { background-color: #12202E; }
    .paper-bg     { background-color: #FAF6EE; }

    /* Brand chip (saffron circle with ink letter — like web .logo-icon) */
    .brand-chip {
        width: 34px;
        height: 34px;
        background-color: #F2A33A;
        border-radius: 50%;
        text-align: center;
        line-height: 34px;
        color: #FFFFFF;
        font-weight: bold;
        font-size: 15px;
        font-family: 'DejaVu Serif', Georgia, serif;
    }

    /* ---------- Section labels (like web .section-label pill) ---------- */
    .section-pill {
        display: inline-block;
        background-color: rgba(242, 163, 58, 0.16);
        color: #C97F14;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Typography tiers ---------- */
    .display-title {
        font-family: 'DejaVu Serif', Georgia, serif;
        font-weight: bold;
        color: #12202E;
        letter-spacing: -0.5px;
        line-height: 1;
    }
    .display-money {
        font-family: 'DejaVu Serif', Georgia, serif;
        font-weight: bold;
        color: #12202E;
        line-height: 1.05;
        white-space: nowrap;
    }
    .subtitle {
        color: #4A5866;
        font-size: 9.5px;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-weight: bold;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .meta-label {
        color: #4A5866;
        font-size: 9.5px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .meta-value {
        color: #12202E;
        font-weight: bold;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Status badges ---------- */
    .badge {
        display: inline-block;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        padding: 3px 9px;
        border-radius: 999px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .badge-paid    { color: #FFFFFF; background: #3F6B50; }
    .badge-pending { color: #FFFFFF; background: #C97F14; }
    .badge-overdue { color: #FFFFFF; background: #B3372F; }
    .badge-draft   { color: #FFFFFF; background: #4A5866; }

    /* ---------- Items table ---------- */
    .items thead th {
        color: #FFFFFF;
        background-color: #12202E;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    /* leftmost and rightmost header corners rounded (visual only; DomPDF may ignore) */
    .items thead th:first-child { border-top-left-radius: 6px; border-bottom-left-radius: 6px; }
    .items thead th:last-child  { border-top-right-radius: 6px; border-bottom-right-radius: 6px; }

    .items tbody tr.row-odd  td { background-color: #FFFFFF; }
    .items tbody tr.row-even td { background-color: #FAF6EE; }

    .items tbody td {
        border-bottom: 1px solid #EAE3D2;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Accent card (Paper Ledger panel) ---------- */
    .ledger-card {
        background-color: #FAF6EE;
        border: 1px solid #EAE3D2;
        border-radius: 8px;
        position: relative;
    }
    /* Saffron accent stripe down the left edge of the totals card */
    .ledger-card-totals {
        border-left: 4px solid #F2A33A;
    }
    /* Navy accent stripe down the left edge of the payment details card */
    .ledger-card-payment {
        border-left: 4px solid #12202E;
    }
    /* Saffron top bar for the total-amount hero card */
    .hero-card {
        background-color: #12202E;
        color: #FFFFFF;
        border-radius: 8px;
        border: 1px solid #12202E;
    }
    .hero-card td, .hero-card div { color: #FFFFFF; }
    .hero-card .subtitle-hero {
        color: #F2A33A;
        font-size: 10px;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        font-weight: bold;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .hero-card .money-hero {
        font-family: 'DejaVu Serif', Georgia, serif;
        font-weight: bold;
        color: #FFFFFF;
        line-height: 1.05;
        white-space: nowrap;
        font-size: 22px;
    }
    .hero-card .money-hero-sym {
        color: #F2A33A;
        font-size: 16px;
        vertical-align: 4px;
        margin-right: 2px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        font-weight: bold;
    }

    /* ---------- Meta rows (invoice no / date / due / status) ---------- */
    .meta-row-label {
        background-color: rgba(242, 163, 58, 0.14);
        color: #C97F14;
        font-size: 9.5px;
        font-weight: bold;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        border-radius: 4px;
    }

    /* ---------- Totals breakdown rows ---------- */
    .totals-label {
        color: #4A5866;
        font-size: 10.5px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .totals-value {
        color: #12202E;
        font-weight: bold;
        font-size: 10.5px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .totals-final-label {
        color: #FFFFFF;
        font-size: 12px;
        font-weight: bold;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .totals-final-value {
        font-family: 'DejaVu Serif', Georgia, serif;
        color: #FFFFFF;
        font-weight: bold;
        line-height: 1.05;
        white-space: nowrap;
        font-size: 17px;
    }

    /* ---------- Dividers ---------- */
    .divider-ink {
        height: 1px;
        background-color: #12202E;
        line-height: 1px;
    }
    .divider-saffron {
        height: 2px;
        background-color: #F2A33A;
        line-height: 2px;
    }
    .divider-soft {
        height: 1px;
        background-color: #EAE3D2;
        line-height: 1px;
    }

    /* ---------- "Bill To / From" panel style ---------- */
    .party-name {
        font-family: 'DejaVu Serif', Georgia, serif;
        color: #12202E;
        font-weight: bold;
        font-size: 14px;
        line-height: 1.2;
    }
    .party-label-pill {
        display: inline-block;
        background-color: #12202E;
        color: #FFFFFF;
        padding: 2px 9px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .party-label-pill.saffron {
        background-color: #C97F14;
    }

    /* ---------- Footer ---------- */
    .footer-rule {
        border-top: 2px solid #12202E;
    }
    .footer-saffron-rule {
        border-top: 3px solid #F2A33A;
        border-radius: 2px;
    }
    .footer-text {
        color: #4A5866;
        font-size: 9.5px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .footer-brand {
        color: #C97F14;
        font-size: 9.5px;
        font-weight: bold;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Signature ---------- */
    .signature-line {
        border-top: 1.5px solid #12202E;
        width: 180px;
        margin-left: auto;
    }
    .signature-name {
        font-family: 'DejaVu Serif', Georgia, serif;
        color: #12202E;
        font-weight: bold;
        font-size: 11px;
        line-height: 1.2;
    }
    .signature-title {
        color: #4A5866;
        font-size: 9.5px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* ---------- Notes list ---------- */
    .notes-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .notes-list li {
        padding: 2px 0 2px 14px;
        position: relative;
        color: #4A5866;
        font-size: 10px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }
    .notes-list li::before {
        /* Saffron diamond bullet (Unicode, DejaVu Sans supported) */
        content: "\25B8";
        color: #C97F14;
        position: absolute;
        left: 0;
        top: 2px;
        font-size: 9px;
    }
</style>
</head>
<body>

@php
    $money = fn ($v) => $currency_symbol . '&nbsp;' . number_format((float) $v, 2);
    $moneyHero = function ($v) use ($currency_symbol) {
        $formatted = number_format((float) $v, 2);
        return '<span class="money-hero-sym">' . $currency_symbol . '</span>' . $formatted;
    };

    $canRenderImages = extension_loaded('gd') && function_exists('imagecreatefrompng');
    $logoOk = $canRenderImages && !empty($logo_data) && is_string($logo_data)
              && str_starts_with(trim($logo_data), 'data:image/');

    $statusKey = strtolower(trim((string) ($status ?? '')));
    [$badgeClass, $badgeLabel] = match ($statusKey) {
        'paid'    => ['badge-paid', 'Paid'],
        'pending' => ['badge-pending', 'Pending'],
        'overdue' => ['badge-overdue', 'Overdue'],
        'draft'   => ['badge-draft', 'Draft'],
        default   => ['badge-draft', $statusKey === '' ? '' : ucfirst($statusKey)],
    };

    $taxPct   = (float) ($totals['tax_pct'] ?? 0);
    $taxText  = rtrim(rtrim(number_format($taxPct, 2), '0'), '.');
    $taxLabel = $taxPct > 0 ? "Tax ({$taxText}%)" : 'Tax';

    $hasClient = !empty($client_name) && trim($client_name) !== '—';

    $hasPayment = collect([
        $payment_method ?? null, $bank_name ?? null, $account_title ?? null,
        $account_number ?? null, $iban ?? null,
    ])->contains(fn ($v) => filled($v));

    $hasSignature = filled($signature_name ?? null);

    $noteLines = collect(preg_split('/\r\n|\r|\n/', (string) $notes))
        ->map(fn ($l) => trim($l))
        ->filter()
        ->values();
@endphp

{{-- spacer helper --}}
@php
    $sp = function ($px = 8) {
        echo '<table class="tbl-inner"><tr><td style="padding:' . $px . 'px 0 0 0;"></td></tr></table>';
    };
@endphp

{{-- ===========================================================
     TOP BANNER: Saffron + navy accent + invoice no (unique strip)
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0">
<colgroup>
    <col style="width: 6px;">
    <col style="width: 100%;">
</colgroup>
<tr>
    <td style="width: 6px; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="saffron-bar" style="width: 6px; height: 62px; border-radius: 3px;"></div>
            </td>
        </tr>
        </table>
    </td>
    <td style="vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 0 0 10px;">

                {{-- ---------- Row 1: Header ---------- --}}
                <table class="tbl" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 52%; vertical-align: top;">
                        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding: 0 14px 0 0;" class="text-cell">
                                @if ($logoOk)
                                    <img src="{{ $logo_data }}" style="max-height: 46px; max-width: 180px; display: block;" alt="Logo">
                                @else
                                    <table class="tbl-inner" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align: middle; padding: 0 10px 0 0;">
                                            <div class="brand-chip">
                                                {{ mb_strtoupper(mb_substr($business_name ?? 'I', 0, 1)) }}
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle; padding: 0;">
                                            <div class="display-title" style="font-size: 20px;">
                                                {{ $business_name }}
                                            </div>
                                            @if (filled($business_tagline ?? null))
                                                <div style="font-size: 9.5px; color: #4A5866; margin-top: 2px; letter-spacing: 0.2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                                    {{ $business_tagline }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    </table>
                                @endif
                            </td>
                        </tr>
                        </table>
                    </td>

                    {{-- Right side: contact details (faded, right-aligned, small) --}}
                    <td style="width: 48%; vertical-align: top;">
                        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding: 0 0 0 14px; text-align: right;" class="text-cell">
                                <table class="tbl-inner" cellpadding="0" cellspacing="0" align="right">
                                <tr>
                                    <td style="text-align: right;">
                                        @if (!empty($business_address))
                                            <div style="font-size: 9.5px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                                <span class="meta-label">Address:&nbsp;</span>{{ $business_address }}
                                            </div>
                                        @endif
                                        @if (!empty($business_email))
                                            <div style="font-size: 9.5px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                                <span class="meta-label">Email:&nbsp;</span>{{ $business_email }}
                                            </div>
                                        @endif
                                        @if (filled($business_website ?? null))
                                            <div style="font-size: 9.5px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                                <span class="meta-label">Web:&nbsp;</span>{{ $business_website }}
                                            </div>
                                        @endif
                                        @if (!empty($business_phone))
                                            <div style="font-size: 9.5px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                                <span class="meta-label">Phone:&nbsp;</span>{{ $business_phone }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                </table>
                            </td>
                        </tr>
                        </table>
                    </td>
                </tr>
                </table>

                {{$sp(6);}}
                <table class="tbl-full-inner" cellpadding="0" cellspacing="0"><tr><td><div class="divider-saffron"></div></td></tr></table>
                {{$sp(10);}}

                {{-- ---------- Row 2: INVOICE title left + Navy hero card total right ---------- --}}
                <table class="tbl" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 55%; vertical-align: middle;">
                        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding: 0 14px 0 0;">
                                <table class="tbl-inner" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 0 0 6px 0;">
                                        <span class="section-pill">Invoice</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="display-title" style="font-size: 30px; color: #12202E;">
                                            INVOICE
                                        </div>
                                    </td>
                                </tr>
                                </table>

                                {{$sp(8);}}

                                <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                                <colgroup>
                                    <col style="width: 38%;">
                                    <col style="width: 62%;">
                                </colgroup>
                                <tr>
                                    <td style="vertical-align: top;">
                                        <div style="padding: 0 10px 0 0;">
                                            <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 4px 0;">
                                                    <span class="meta-row-label" style="padding: 2px 8px;">Invoice No</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 8px 4px 8px;">
                                                    <span class="meta-value" style="font-size: 11.5px;">{{ $invoice_number }}</span>
                                                </td>
                                            </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 4px 0 4px 0;">
                                                <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                                                <colgroup>
                                                    <col style="width: 50%;">
                                                    <col style="width: 50%;">
                                                </colgroup>
                                                <tr>
                                                    <td style="vertical-align: top;">
                                                        <div style="padding: 0 6px 0 0;">
                                                            <span class="meta-label">Issue Date</span><br>
                                                            <span class="meta-value" style="font-size: 11px;">{{ $invoice_date }}</span>
                                                        </div>
                                                    </td>
                                                    <td style="vertical-align: top;">
                                                        <div style="padding: 0 0 0 6px;">
                                                            <span class="meta-label">Due Date</span><br>
                                                            @if (!empty($due_date))
                                                                <span class="meta-value" style="font-size: 11px;">{{ $due_date }}</span>
                                                            @else
                                                                <span style="font-size: 11px; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">—</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        @if ($badgeLabel !== '')
                                        <tr>
                                            <td style="padding: 6px 0 0 0;">
                                                <span class="meta-label">Status:&nbsp;</span>
                                                <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                            </td>
                                        </tr>
                                        @endif
                                        </table>
                                    </td>
                                </tr>
                                </table>

                            </td>
                        </tr>
                        </table>
                    </td>

                    {{-- RIGHT: HERO CARD (navy background + saffron accent) --}}
                    <td style="width: 45%; vertical-align: top;">
                        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding: 0 0 0 14px;">
                                <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="hero-card" style="padding: 14px 16px;">
                                        <div class="subtitle-hero" style="margin-bottom: 4px;">Total Amount Due</div>
                                        <div class="money-hero">
                                            {!! $moneyHero($totals['total']) !!}
                                        </div>
                                        <table class="tbl-inner" cellpadding="0" cellspacing="0" style="margin-top: 10px;">
                                        <tr>
                                            <td style="padding: 0; vertical-align: top;">
                                                <span style="color: #F2A33A; font-size: 9.5px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; letter-spacing: 0.8px; text-transform: uppercase; font-weight: bold;">InvoiceFlow</span>
                                            </td>
                                            <td style="padding: 0 0 0 12px; vertical-align: top;">
                                                <span style="color: rgba(255,255,255,0.55); font-size: 9px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; letter-spacing: 0.3px;">Paper Ledger Invoice</span>
                                            </td>
                                        </tr>
                                        </table>
                                    </td>
                                </tr>
                                </table>
                            </td>
                        </tr>
                        </table>
                    </td>
                </tr>
                </table>

            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

{{$sp(12);}}

{{-- ===========================================================
     PARTIES: Bill To / From  (ink pill + saffron pill)
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 12px 0 0;">
                <div style="margin-bottom: 6px;">
                    <span class="party-label-pill">Bill To</span>
                </div>
                <div class="divider-soft" style="margin-bottom: 8px;"></div>
                <div class="party-name" style="margin-bottom: 5px;" class="text-cell">
                    {{ $hasClient ? $client_name : '—' }}
                </div>
                <table class="tbl-inner" cellpadding="0" cellspacing="0">
                @if (!empty($client_email))
                <tr>
                    <td style="padding: 1px 0;" class="text-cell">
                        <span class="meta-label">Email:&nbsp;</span>
                        <span style="font-size: 10px; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $client_email }}</span>
                    </td>
                </tr>
                @endif
                @if (!empty($client_phone))
                <tr>
                    <td style="padding: 1px 0;" class="text-cell">
                        <span class="meta-label">Phone:&nbsp;</span>
                        <span style="font-size: 10px; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $client_phone }}</span>
                    </td>
                </tr>
                @endif
                @if (!empty($client_address))
                <tr>
                    <td style="padding: 1px 0;" class="text-cell">
                        <span class="meta-label">Address:&nbsp;</span>
                        <span style="font-size: 10px; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $client_address }}</span>
                    </td>
                </tr>
                @endif
                </table>
            </td>
        </tr>
        </table>
    </td>

    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 0 0 12px;">
                <div style="margin-bottom: 6px;">
                    <span class="party-label-pill saffron">From</span>
                </div>
                <div class="divider-soft" style="margin-bottom: 8px;"></div>
                <div class="party-name" style="margin-bottom: 5px;" class="text-cell">
                    {{ $business_name }}
                </div>
                <table class="tbl-inner" cellpadding="0" cellspacing="0">
                @if (!empty($business_address))
                <tr>
                    <td style="padding: 1px 0;" class="text-cell">
                        <span class="meta-label">Address:&nbsp;</span>
                        <span style="font-size: 10px; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $business_address }}</span>
                    </td>
                </tr>
                @endif
                @if (!empty($business_email))
                <tr>
                    <td style="padding: 1px 0;" class="text-cell">
                        <span class="meta-label">Email:&nbsp;</span>
                        <span style="font-size: 10px; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $business_email }}</span>
                    </td>
                </tr>
                @endif
                @if (filled($business_website ?? null))
                <tr>
                    <td style="padding: 1px 0;" class="text-cell">
                        <span class="meta-label">Web:&nbsp;</span>
                        <span style="font-size: 10px; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $business_website }}</span>
                    </td>
                </tr>
                @endif
                @if (!empty($business_phone))
                <tr>
                    <td style="padding: 1px 0;" class="text-cell">
                        <span class="meta-label">Phone:&nbsp;</span>
                        <span style="font-size: 10px; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $business_phone }}</span>
                    </td>
                </tr>
                @endif
                </table>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

{{$sp(12);}}

{{-- ===========================================================
     ITEMS TABLE (Paper Ledger style with alternating rows)
     =========================================================== --}}
<table class="tbl items" cellpadding="0" cellspacing="0">
    <colgroup>
        <col style="width: 6%;">
        <col style="width: 44%;">
        <col style="width: 12%;">
        <col style="width: 19%;">
        <col style="width: 19%;">
    </colgroup>
    <thead>
    <tr>
        <th style="text-align: center; padding: 10px 4px;">#</th>
        <th style="text-align: left;   padding: 10px 10px;">Item Description</th>
        <th style="text-align: center; padding: 10px 4px;">Quantity</th>
        <th style="padding: 10px 10px;" class="money">Unit Price</th>
        <th style="padding: 10px 10px;" class="money">Total</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($items as $it)
        @php
            $rowClass = ($loop->iteration % 2 === 0) ? 'row-even' : 'row-odd';
        @endphp
        <tr class="{{ $rowClass }}" style="page-break-inside: avoid;">
            <td style="text-align: center; padding: 10px 4px; font-weight: bold; color: #12202E; vertical-align: top;">
                {{ $loop->iteration }}
            </td>
            <td style="text-align: left; padding: 10px 10px; vertical-align: top;" class="text-cell">
                <div style="font-weight: bold; color: #12202E; font-size: 11px; line-height: 1.3; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                    {{ $it['description'] }}
                </div>
                @if (filled($it['details'] ?? null))
                    <div style="color: #4A5866; font-size: 9.5px; margin-top: 3px; line-height: 1.35; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="text-cell">
                        {{ $it['details'] }}
                    </div>
                @endif
            </td>
            <td style="text-align: center; padding: 10px 4px; color: #12202E; vertical-align: top;">
                {{ $it['quantity'] }}
            </td>
            <td style="padding: 10px 10px; color: #12202E; vertical-align: top;" class="money">
                {!! $money($it['price']) !!}
            </td>
            <td style="padding: 10px 10px; font-weight: bold; color: #C97F14; vertical-align: top;" class="money">
                {!! $money($it['line_total']) !!}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" style="text-align: center; padding: 18px; color: #4A5866; font-style: italic; border-bottom: 1px solid #EAE3D2; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                No items on this invoice.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

{{$sp(12);}}

{{-- ===========================================================
     LOWER PANEL: Payment Details 50% (ink stripe) / Totals 50% (saffron stripe)
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 12px 0 0;">
                @if ($hasPayment)
                    <div style="margin-bottom: 6px;">
                        <span class="section-pill">Payment Details</span>
                    </div>
                    <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="ledger-card ledger-card-payment" style="padding: 12px 14px;">
                            <table class="tbl-full-inner" cellpadding="0" cellspacing="0" style="font-size: 10px;">
                                <colgroup>
                                    <col style="width: 44%;">
                                    <col style="width: 56%;">
                                </colgroup>
                                @if (filled($bank_name ?? null))
                                <tr>
                                    <td style="vertical-align: top; padding: 3px 8px 3px 0;"><span class="meta-label">Bank Name</span></td>
                                    <td style="vertical-align: top; padding: 3px 0 3px 8px;" class="text-cell"><span style="font-weight: bold; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $bank_name }}</span></td>
                                </tr>
                                @endif
                                @if (filled($account_title ?? null))
                                <tr>
                                    <td style="vertical-align: top; padding: 3px 8px 3px 0;"><span class="meta-label">Account Title</span></td>
                                    <td style="vertical-align: top; padding: 3px 0 3px 8px;" class="text-cell"><span style="font-weight: bold; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $account_title }}</span></td>
                                </tr>
                                @endif
                                @if (filled($account_number ?? null))
                                <tr>
                                    <td style="vertical-align: top; padding: 3px 8px 3px 0;"><span class="meta-label">Account Number</span></td>
                                    <td style="vertical-align: top; padding: 3px 0 3px 8px;" class="text-cell"><span style="font-weight: bold; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $account_number }}</span></td>
                                </tr>
                                @endif
                                @if (filled($iban ?? null))
                                <tr>
                                    <td style="vertical-align: top; padding: 3px 8px 3px 0;"><span class="meta-label">IBAN</span></td>
                                    <td style="vertical-align: top; padding: 3px 0 3px 8px;" class="text-cell"><span style="font-weight: bold; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $iban }}</span></td>
                                </tr>
                                @endif
                                @if (filled($payment_method ?? null))
                                <tr>
                                    <td style="vertical-align: top; padding: 3px 8px 3px 0;"><span class="meta-label">Payment Method</span></td>
                                    <td style="vertical-align: top; padding: 3px 0 3px 8px;" class="text-cell"><span style="font-weight: bold; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $payment_method }}</span></td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                    </table>
                @else
                    {{-- Empty: nothing rendered (so the right totals panel doesn't shift) --}}
                @endif
            </td>
        </tr>
        </table>
    </td>

    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 0 0 12px;">
                <div style="margin-bottom: 6px;">
                    <span class="section-pill">Summary</span>
                </div>
                <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="ledger-card ledger-card-totals" style="padding: 12px 14px;">
                        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                            <colgroup>
                                <col style="width: 54%;">
                                <col style="width: 46%;">
                            </colgroup>
                            <tr>
                                <td style="vertical-align: top; padding: 4px 8px 4px 0;">
                                    <span class="totals-label">Subtotal</span>
                                </td>
                                <td style="vertical-align: top; padding: 4px 0 4px 8px;">
                                    <div class="totals-value money">{!! $money($totals['subtotal']) !!}</div>
                                </td>
                            </tr>
                            @if ($taxPct > 0 || (float) $totals['tax'] > 0)
                            <tr>
                                <td style="vertical-align: top; padding: 4px 8px 4px 0;">
                                    <span class="totals-label">{{ $taxLabel }}</span>
                                </td>
                                <td style="vertical-align: top; padding: 4px 0 4px 8px;">
                                    <div class="totals-value money">{!! $money($totals['tax']) !!}</div>
                                </td>
                            </tr>
                            @endif
                            @if ((float) $totals['discount'] > 0)
                            <tr>
                                <td style="vertical-align: top; padding: 4px 8px 4px 0;">
                                    <span class="totals-label">Discount</span>
                                </td>
                                <td style="vertical-align: top; padding: 4px 0 4px 8px;">
                                    <div class="totals-value money">- {!! $money($totals['discount']) !!}</div>
                                </td>
                            </tr>
                            @endif
                        </table>

                        <table class="tbl-full-inner" cellpadding="0" cellspacing="0" style="margin-top: 10px;">
                        <tr>
                            <td style="padding: 0;">
                                <div class="divider-saffron"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="hero-card" style="padding: 10px 14px; margin-top: 10px;">
                                <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                                <colgroup>
                                    <col style="width: 46%;">
                                    <col style="width: 54%;">
                                </colgroup>
                                <tr>
                                    <td style="vertical-align: middle; padding: 0 6px 0 0;">
                                        <span class="totals-final-label">Total</span>
                                    </td>
                                    <td style="vertical-align: middle; padding: 0 0 0 6px;">
                                        <div class="totals-final-value money">
                                            {!! $money($totals['total']) !!}
                                        </div>
                                    </td>
                                </tr>
                                </table>
                            </td>
                        </tr>
                        </table>
                    </td>
                </tr>
                </table>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

{{$sp(14);}}

{{-- ===========================================================
     NOTES (60%) + SIGNATURE (40%)
     =========================================================== --}}
@if ($noteLines->isNotEmpty() || $hasSignature)
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td style="width: 60%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 12px 0 0;">
                @if ($noteLines->isNotEmpty())
                    <div style="margin-bottom: 6px;">
                        <span class="section-pill">Notes</span>
                    </div>
                    <ul class="notes-list">
                        @foreach ($noteLines as $line)
                            <li class="text-cell">{{ $line }}</li>
                        @endforeach
                    </ul>
                @endif
            </td>
        </tr>
        </table>
    </td>

    <td style="width: 40%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 0 0 12px;">
                @if ($hasSignature)
                    <div style="margin-bottom: 6px;">
                        <span class="section-pill">Authorised Signatory</span>
                    </div>
                    <table class="tbl-inner" cellpadding="0" cellspacing="0" align="right">
                    <tr>
                        <td style="text-align: right;">
                            <div class="signature-line"></div>
                            <div class="signature-name" style="margin-top: 6px;">
                                {{ $signature_name }}
                            </div>
                            @if (filled($signature_title ?? null))
                                <div class="signature-title" style="margin-top: 1px;">
                                    {{ $signature_title }}
                                </div>
                            @endif
                        </td>
                    </tr>
                    </table>
                @endif
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>
@endif

{{$sp(16);}}

{{-- ===========================================================
     FOOTER: Ink + Saffron double rule + branding
     =========================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td style="width: 100%;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0;">
                <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding: 0;">
                        <div class="footer-saffron-rule"></div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 0 0 0;">
                        <div class="footer-rule"></div>
                    </td>
                </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0 0 0;">
                <table class="tbl" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="padding: 0 10px 0 0;">
                            <div class="footer-text">
                                Thank you for choosing
                                <span style="color: #C97F14; font-weight: bold;">{{ filled($business_name ?? null) && $business_name !== 'Your business' ? $business_name : 'InvoiceFlow' }}</span>!
                            </div>
                            <div class="footer-text" style="margin-top: 2px;">
                                Generated on {{ date('M j, Y') }} &middot; Paper Ledger &middot; InvoiceFlow
                            </div>
                        </div>
                    </td>
                    <td style="width: 40%; vertical-align: middle;">
                        <div style="padding: 0 0 0 10px; text-align: right;">
                            <span class="footer-brand">InvoiceFlow</span>
                            <div style="color: #4A5866; font-size: 8.5px; margin-top: 2px; letter-spacing: 0.2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; text-align: right;">
                                Paper Ledger Theme
                            </div>
                        </div>
                    </td>
                </tr>
                </table>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

</body>
</html>
