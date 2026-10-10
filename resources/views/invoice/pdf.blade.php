{{--
    Invoice PDF template (DomPDF)
    DomPDF compatibility rules:
      - @page sets page margins; NO wrapper div with padding + width:100%
      - Width-bearing <td>s have ZERO padding/margin/border
      - Padding lives ONLY on inner (non-width-bearing) wrappers
      - Every <table> with columns has explicit % widths summing to exactly 100%
      - No box-sizing, no width:100% on anything with padding/border/margin
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice {{ $invoice_number }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 36px 40px;
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
        line-height: 1.35;
    }

    td, th, p, div, li {
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        color: #12202E;
        font-size: 11px;
        line-height: 1.35;
    }

    /* Top-level structural tables: fixed layout, 100% width */
    .tbl {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }

    /* Inner / utility tables do not have a fixed 100% width */
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

    .text-cell {
        word-wrap: break-word;
    }

    .money {
        text-align: right;
        white-space: nowrap;
    }

    .badge {
        display: inline-block;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 2px 7px;
        border-radius: 3px;
    }
    .badge-paid    { color: #3F6B50; background: #E6F2EC; border: 1px solid #CBE3D6; }
    .badge-pending { color: #C97F14; background: #FDF4E6; border: 1px solid #F5DFB3; }
    .badge-overdue { color: #B3372F; background: #FDE8E7; border: 1px solid #F6D0CE; }
    .badge-draft   { color: #4A5866; background: #F3F1EC; border: 1px solid #E5DFD4; }

    .lbl {
        color: #4A5866;
        font-size: 9.5px;
        font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    }

    /* Card visual: applied to an inner <td> (no width set on its parent) */
    .card-td {
        background-color: #FAF6EE;
        border: 1px solid #EAE3D2;
        border-radius: 6px;
    }
</style>
</head>
<body>

@php
    $money = fn ($v) => $currency_symbol . '&nbsp;' . number_format((float) $v, 2);

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

{{-- ====================================================================
     SPACER helper (a blank 1-row table — no width issue)
     ==================================================================== --}}
@php
    $sp = function ($px = 8) {
        echo '<table class="tbl-inner"><tr><td style="padding:' . $px . 'px 0 0 0;"></td></tr></table>';
    };
@endphp

{{-- ====================================================================
     1. TOP HEADER: Logo & Branding (Left) 50% / Company Contact (Right) 50%
     Outer <td> widths: 50% each, ZERO padding. Inner nested-table <td> has padding.
     ==================================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0">
<tr>
    {{-- Left column — width 50%, no padding --}}
    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 12px 0 0;" class="text-cell">
                @if ($logoOk)
                    <img src="{{ $logo_data }}" style="max-height: 48px; max-width: 180px; display: block; margin-bottom: 4px;" alt="Logo">
                @else
                    <table class="tbl-inner" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="vertical-align: middle; padding: 0 8px 0 0;">
                            <div style="width: 28px; height: 28px; background-color: #12202E; border-radius: 6px; text-align: center; line-height: 28px; color: #FFFFFF; font-weight: bold; font-size: 15px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                {{ mb_strtoupper(mb_substr($business_name ?? 'I', 0, 1)) }}
                            </div>
                        </td>
                        <td style="vertical-align: middle; padding: 0;">
                            <div style="font-size: 18px; font-weight: bold; color: #12202E; line-height: 1.1; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                {{ $business_name }}
                            </div>
                            @if (filled($business_tagline ?? null))
                                <div style="font-size: 9.5px; color: #4A5866; margin-top: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
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

    {{-- Right column — width 50%, no padding --}}
    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 0 0 12px; text-align: right;" class="text-cell">
                <div style="font-size: 12px; font-weight: bold; color: #12202E; margin-bottom: 3px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                    {{ $business_name }}
                </div>
                @if (!empty($business_address))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Address:&nbsp;</span>{{ $business_address }}
                    </div>
                @endif
                @if (!empty($business_email))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Email:&nbsp;</span>{{ $business_email }}
                    </div>
                @endif
                @if (filled($business_website ?? null))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Web:&nbsp;</span>{{ $business_website }}
                    </div>
                @endif
                @if (!empty($business_phone))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Phone:&nbsp;</span>{{ $business_phone }}
                    </div>
                @endif
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

<?php $sp(10); ?>

{{-- ====================================================================
     2. INVOICE TITLE + META (Left) 55% / TOTAL AMOUNT CARD (Right) 45%
     ==================================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0">
<tr>
    <td style="width: 55%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 12px 0 0;" class="text-cell">
                <div style="font-size: 24px; font-weight: bold; color: #12202E; letter-spacing: 0.5px; line-height: 1; margin-bottom: 8px; font-family: 'DejaVu Serif', Georgia, serif;">
                    INVOICE
                </div>
                <table class="tbl-inner" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="color: #4A5866; padding: 2px 12px 2px 0; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Invoice No:</td>
                    <td style="font-weight: bold; color: #12202E; padding: 2px 0; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $invoice_number }}</td>
                </tr>
                <tr>
                    <td style="color: #4A5866; padding: 2px 12px 2px 0; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Issue Date:</td>
                    <td style="color: #12202E; padding: 2px 0; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $invoice_date }}</td>
                </tr>
                @if (!empty($due_date))
                <tr>
                    <td style="color: #4A5866; padding: 2px 12px 2px 0; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Due Date:</td>
                    <td style="color: #12202E; padding: 2px 0; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $due_date }}</td>
                </tr>
                @endif
                @if ($badgeLabel !== '')
                <tr>
                    <td style="color: #4A5866; padding: 3px 12px 2px 0; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Status:</td>
                    <td style="padding: 3px 0;">
                        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                    </td>
                </tr>
                @endif
                </table>
            </td>
        </tr>
        </table>
    </td>

    <td style="width: 45%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 0 0 12px;">
                <table class="tbl-inner" cellpadding="0" cellspacing="0" align="right">
                <tr>
                    <td class="card-td" style="padding: 10px 16px;">
                        <div style="font-size: 10px; font-weight: 600; color: #4A5866; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                            Total Amount
                        </div>
                        <div style="font-size: 21px; font-weight: bold; color: #12202E; line-height: 1.1; white-space: nowrap; font-family: 'DejaVu Serif', Georgia, serif;">
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

<?php $sp(10); ?>

{{-- ====================================================================
     3. BILL TO 50% / FROM 50%
     ==================================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 12px 0 0;" class="text-cell">
                <div style="font-size: 10px; font-weight: 600; color: #4A5866; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                    Bill To
                </div>
                <div style="font-size: 13.5px; font-weight: bold; color: #12202E; margin-bottom: 5px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                    {{ $hasClient ? $client_name : '—' }}
                </div>
                @if (!empty($client_email))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Email:&nbsp;</span>{{ $client_email }}
                    </div>
                @endif
                @if (!empty($client_phone))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Phone:&nbsp;</span>{{ $client_phone }}
                    </div>
                @endif
                @if (!empty($client_address))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Address:&nbsp;</span>{{ $client_address }}
                    </div>
                @endif
            </td>
        </tr>
        </table>
    </td>

    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 0 0 12px;" class="text-cell">
                <div style="font-size: 10px; font-weight: 600; color: #4A5866; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                    From
                </div>
                <div style="font-size: 13.5px; font-weight: bold; color: #12202E; margin-bottom: 5px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                    {{ $business_name }}
                </div>
                @if (!empty($business_address))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Address:&nbsp;</span>{{ $business_address }}
                    </div>
                @endif
                @if (!empty($business_email))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Email:&nbsp;</span>{{ $business_email }}
                    </div>
                @endif
                @if (filled($business_website ?? null))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Web:&nbsp;</span>{{ $business_website }}
                    </div>
                @endif
                @if (!empty($business_phone))
                    <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        <span class="lbl">Phone:&nbsp;</span>{{ $business_phone }}
                    </div>
                @endif
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

<?php $sp(10); ?>

{{-- ====================================================================
     4. ITEMS TABLE
     Column widths EXACTLY 100%: # 6%, Description 44%, Qty 12%, Unit Price 19%, Total 19%
     Padding is on <th>/<td> directly — they DON'T carry widths; parent <col> or
     first-row <th> widths do. Actually <th> here sets the width AND has padding.
     But since table-layout:fixed is used on the parent table, widths come from
     the first row of cells only once. To be safe we include padding within each
     cell's own box under the fixed-layout regime.
     ==================================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0">
    <colgroup>
        <col style="width: 6%;">
        <col style="width: 44%;">
        <col style="width: 12%;">
        <col style="width: 19%;">
        <col style="width: 19%;">
    </colgroup>
    <thead>
    <tr style="background-color: #12202E; color: #FFFFFF;">
        <th style="text-align: center; padding: 8px 4px; font-size: 10px; font-weight: 600; color: #FFFFFF; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">#</th>
        <th style="text-align: left;   padding: 8px 8px; font-size: 10px; font-weight: 600; color: #FFFFFF; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Item Description</th>
        <th style="text-align: center; padding: 8px 4px; font-size: 10px; font-weight: 600; color: #FFFFFF; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Quantity</th>
        <th style="padding: 8px 8px; font-size: 10px; font-weight: 600; color: #FFFFFF; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="money">Unit Price</th>
        <th style="padding: 8px 8px; font-size: 10px; font-weight: 600; color: #FFFFFF; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="money">Total</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($items as $it)
        <tr style="border-bottom: 1px solid #E8EAF0; page-break-inside: avoid;">
            <td style="text-align: center; padding: 8px 4px; font-weight: bold; color: #12202E; vertical-align: top; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                {{ $loop->iteration }}
            </td>
            <td style="text-align: left; padding: 8px 8px; vertical-align: top;" class="text-cell">
                <div style="font-weight: bold; color: #12202E; font-size: 11px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                    {{ $it['description'] }}
                </div>
                @if (filled($it['details'] ?? null))
                    <div style="color: #4A5866; font-size: 9.5px; margin-top: 2px; line-height: 1.3; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="text-cell">
                        {{ $it['details'] }}
                    </div>
                @endif
            </td>
            <td style="text-align: center; padding: 8px 4px; color: #12202E; vertical-align: top; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                {{ $it['quantity'] }}
            </td>
            <td style="padding: 8px 8px; color: #12202E; vertical-align: top; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="money">
                {!! $money($it['price']) !!}
            </td>
            <td style="padding: 8px 8px; font-weight: bold; color: #12202E; vertical-align: top; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="money">
                {!! $money($it['line_total']) !!}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" style="text-align: center; padding: 14px; color: #4A5866; font-style: italic; border-bottom: 1px solid #E8EAF0; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                No items on this invoice.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

<?php $sp(10); ?>

{{-- ====================================================================
     5. PAYMENT DETAILS 50% (Left) / TOTALS CALCULATION 50% (Right)
     ==================================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 12px 0 0;" class="text-cell">
                @if ($hasPayment)
                    <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="card-td" style="padding: 10px 12px;">
                            <div style="font-size: 11.5px; font-weight: bold; color: #12202E; margin-bottom: 6px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                Payment Details
                            </div>
                            <table class="tbl-full-inner" cellpadding="0" cellspacing="0" style="font-size: 10px;">
                                <colgroup>
                                    <col style="width: 44%;">
                                    <col style="width: 56%;">
                                </colgroup>
                                @if (filled($bank_name ?? null))
                                <tr>
                                    <td style="vertical-align: top;"><div style="padding: 2px 10px 2px 0; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Bank Name:</div></td>
                                    <td style="vertical-align: top;"><div style="padding: 2px 0; font-weight: 600; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="text-cell">{{ $bank_name }}</div></td>
                                </tr>
                                @endif
                                @if (filled($account_title ?? null))
                                <tr>
                                    <td style="vertical-align: top;"><div style="padding: 2px 10px 2px 0; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Account Title:</div></td>
                                    <td style="vertical-align: top;"><div style="padding: 2px 0; font-weight: 600; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="text-cell">{{ $account_title }}</div></td>
                                </tr>
                                @endif
                                @if (filled($account_number ?? null))
                                <tr>
                                    <td style="vertical-align: top;"><div style="padding: 2px 10px 2px 0; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Account Number:</div></td>
                                    <td style="vertical-align: top;"><div style="padding: 2px 0; font-weight: 600; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="text-cell">{{ $account_number }}</div></td>
                                </tr>
                                @endif
                                @if (filled($iban ?? null))
                                <tr>
                                    <td style="vertical-align: top;"><div style="padding: 2px 10px 2px 0; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">IBAN:</div></td>
                                    <td style="vertical-align: top;"><div style="padding: 2px 0; font-weight: 600; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="text-cell">{{ $iban }}</div></td>
                                </tr>
                                @endif
                                @if (filled($payment_method ?? null))
                                <tr>
                                    <td style="vertical-align: top;"><div style="padding: 2px 10px 2px 0; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Payment Method:</div></td>
                                    <td style="vertical-align: top;"><div style="padding: 2px 0; font-weight: 600; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="text-cell">{{ $payment_method }}</div></td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                    </table>
                @endif
            </td>
        </tr>
        </table>
    </td>

    <td style="width: 50%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 0 0 12px;">
                <table class="tbl-full-inner" cellpadding="0" cellspacing="0" style="font-size: 10.5px;">
                    <colgroup>
                        <col style="width: 50%;">
                        <col style="width: 50%;">
                    </colgroup>
                    <tr>
                        <td style="vertical-align: top;"><div style="padding: 3px 8px 3px 0; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Subtotal</div></td>
                        <td style="vertical-align: top;"><div style="padding: 3px 0 3px 8px; font-weight: bold; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="money">{!! $money($totals['subtotal']) !!}</div></td>
                    </tr>
                    @if ($taxPct > 0 || (float) $totals['tax'] > 0)
                    <tr>
                        <td style="vertical-align: top;"><div style="padding: 3px 8px 3px 0; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">{{ $taxLabel }}</div></td>
                        <td style="vertical-align: top;"><div style="padding: 3px 0 3px 8px; font-weight: bold; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="money">{!! $money($totals['tax']) !!}</div></td>
                    </tr>
                    @endif
                    @if ((float) $totals['discount'] > 0)
                    <tr>
                        <td style="vertical-align: top;"><div style="padding: 3px 8px 3px 0; color: #4A5866; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">Discount</div></td>
                        <td style="vertical-align: top;"><div style="padding: 3px 0 3px 8px; font-weight: bold; color: #12202E; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="money">- {!! $money($totals['discount']) !!}</div></td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="2" style="padding: 6px 0 0 0;">
                            <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
                                <colgroup>
                                    <col style="width: 50%;">
                                    <col style="width: 50%;">
                                </colgroup>
                                <tr>
                                    <td class="card-td" style="padding: 8px 12px; font-size: 12px; font-weight: bold; color: #12202E; border-right: none; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                        Total
                                    </td>
                                    <td class="card-td money" style="padding: 8px 12px; font-size: 15px; font-weight: bold; color: #12202E; border-left: none; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                        {!! $money($totals['total']) !!}
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

<?php $sp(10); ?>

{{-- ====================================================================
     6. NOTES 60% / SIGNATURE 40%
     ==================================================================== --}}
@if ($noteLines->isNotEmpty() || $hasSignature)
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td style="width: 60%; vertical-align: top;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 0 12px 0 0;" class="text-cell">
                @if ($noteLines->isNotEmpty())
                    <div style="font-size: 11px; font-weight: bold; color: #12202E; margin-bottom: 4px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        Notes
                    </div>
                    <ul style="margin: 0; padding-left: 16px; color: #4A5866; font-size: 10px; line-height: 1.45; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                        @foreach ($noteLines as $line)
                            <li style="margin-bottom: 2px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;" class="text-cell">{{ $line }}</li>
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
                    <table class="tbl-inner" cellpadding="0" cellspacing="0" align="right">
                    <tr>
                        <td style="text-align: right;">
                            <div style="border-top: 1px solid #94A3B8; width: 180px;"></div>
                            <div style="font-weight: bold; font-size: 10.5px; color: #12202E; margin-top: 5px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                                {{ $signature_name }}
                            </div>
                            @if (filled($signature_title ?? null))
                                <div style="font-size: 9.5px; color: #4A5866; margin-top: 1px; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
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

<?php $sp(12); ?>

{{-- ====================================================================
     7. FOOTER: Thank You Message — no QR code, no icons
     ==================================================================== --}}
<table class="tbl" cellpadding="0" cellspacing="0" style="page-break-inside: avoid;">
<tr>
    <td style="width: 100%;">
        <table class="tbl-full-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 10px 0 0 0; border-top: 1px solid #EAE3D2; font-size: 10px; color: #4A5866; vertical-align: middle; font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;">
                Thank you for choosing {{ filled($business_name ?? null) && $business_name !== 'Your business' ? $business_name : 'InvoiceFlow' }}!
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

</body>
</html>
