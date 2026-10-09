{{--
    ==========================================================================
    Invoice PDF template (DomPDF)
    Redesigned to match the requested modern invoice layout with web colors.

    DomPDF stability guarantees:
      - Page margin 0 with strict inner padding (.wrap) so text NEVER clips off-canvas.
      - No CSS floats (align="right" tables used instead).
      - Self-contained base64 PNG icons so rendering is 100% reliable.
      - white-space: nowrap on amounts so currency and numbers never break across lines.
    ==========================================================================
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice {{ $invoice_number }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 0;
    }

    * {
        box-sizing: border-box;
    }

    html, body {
        margin: 0;
        padding: 0;
        background-color: #FFFFFF;
    }

    body {
        color: #12202E;
        font-family: "DejaVu Sans", Helvetica, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.35;
    }

    .wrap {
        padding: 28px 36px 20px 36px;
        width: 100%;
    }

    table {
        border-collapse: collapse;
    }

    /* Status badge */
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

    .card-box {
        background-color: #FAF6EE;
        border: 1px solid #EAE3D2;
        border-radius: 6px;
    }
</style>
</head>
<body>

@php
    // Money formatter: symbol + 2 decimals with non-breaking space
    $money = fn ($v) => $currency_symbol . '&nbsp;' . number_format((float) $v, 2);

    // Logo check
    $logoOk = !empty($logo_data) && is_string($logo_data)
              && str_starts_with(trim($logo_data), 'data:image/');

    // Status badge
    $statusKey = strtolower(trim((string) ($status ?? '')));
    [$badgeClass, $badgeLabel] = match ($statusKey) {
        'paid'    => ['badge-paid', 'Paid'],
        'pending' => ['badge-pending', 'Pending'],
        'overdue' => ['badge-overdue', 'Overdue'],
        'draft'   => ['badge-draft', 'Draft'],
        default   => ['badge-draft', $statusKey === '' ? '' : ucfirst($statusKey)],
    };

    // Tax formatting
    $taxPct   = (float) ($totals['tax_pct'] ?? 0);
    $taxText  = rtrim(rtrim(number_format($taxPct, 2), '0'), '.');
    $taxLabel = $taxPct > 0 ? "Tax ({$taxText}%)" : 'Tax';

    // Client guard
    $hasClient = !empty($client_name) && trim($client_name) !== '—';

    // Payment details presence
    $hasPayment = collect([
        $payment_method ?? null, $bank_name ?? null, $account_title ?? null,
        $account_number ?? null, $iban ?? null,
    ])->contains(fn ($v) => filled($v));

    // Signature presence
    $hasSignature = filled($signature_name ?? null);

    // Notes lines
    $noteLines = collect(preg_split('/\r\n|\r|\n/', (string) $notes))
        ->map(fn ($l) => trim($l))
        ->filter()
        ->values();

    // High compatibility base64 PNG icons (11x11, #4A5866)
    $iconLocation = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAQ0lEQVQokWNgoDdgxCXhFZH2H8betmIWhjqsGpE14dKMoRGbJmyamXApIgTor5HswKGujei2YotHsm3EC/DFKdk2AgCKNRYZvXhr7wAAAABJRU5ErkJggg==" width="10" height="10" style="vertical-align: -1px; margin-right: 4px;" alt="">';
    $iconEmail    = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAJklEQVQokWNgGDKAkYGBgcErIu0/KZq2rZjFyESujaMaB5VG+gMAcJAEEuPhJwkAAAAASUVORK5CYII=" width="10" height="10" style="vertical-align: -1px; margin-right: 4px;" alt="">';
    $iconGlobe    = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAUklEQVQokWNgIBMw4pLwikj7D2NvWzELQx2GALIGdIBsAIpGfJrQNcM1EqMJWTMTsYrRASOptsEA2TZS5lQGBnoHDim2YsQjMZpxphxcBmBLqwBo3SIZ0HXk2wAAAABJRU5ErkJggg==" width="10" height="10" style="vertical-align: -1px; margin-right: 4px;" alt="">';
    $iconPhone    = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAJElEQVQokWNgIBMwInO8ItL+41O8bcUsuHomcm0c1Tiqkc4aAUREBBrzPRNyAAAAAElFTkSuQmCC" width="10" height="10" style="vertical-align: -1px; margin-right: 4px;" alt="">';
    $iconUser     = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAATUlEQVQokWNgIBMw4pLwikj7D2NvWzELQx1WjciacGnG0IhNEzbNTLgUEQJka6SeH7FpxhaqtAH4Qpjs6EDRiE8Tuma4RmI0IWsmOx4BhLYiFsjsozgAAAAASUVORK5CYII=" width="10" height="10" style="vertical-align: -1px; margin-right: 4px;" alt="">';
    $iconNotes    = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAI0lEQVQokWNgIBMwwhheEWn/idW0bcUsRiZybRzVOKqRzhoBPJgEGnwd5+AAAAAASUVORK5CYII=" width="12" height="12" style="vertical-align: -1px; margin-right: 4px;" alt="">';
@endphp

<div class="wrap">

{{-- ====================================================================
     1. TOP HEADER: Logo & Branding (Left) / Company Contact (Right)
     ==================================================================== --}}
<table cellpadding="0" cellspacing="0" style="width: 100%;">
<tr>
    {{-- Left: Logo / Brand Name + Tagline --}}
    <td style="width: 50%; vertical-align: top;">
        @if ($logoOk)
            <img src="{{ $logo_data }}" style="max-height: 48px; max-width: 180px; display: block; margin-bottom: 4px;" alt="Logo">
        @else
            <table cellpadding="0" cellspacing="0">
            <tr>
                <td style="vertical-align: middle; padding-right: 8px;">
                    <div style="width: 28px; height: 28px; background-color: #12202E; border-radius: 6px; text-align: center; line-height: 28px; color: #FFFFFF; font-weight: bold; font-size: 15px;">
                        {{ mb_strtoupper(mb_substr($business_name ?? 'I', 0, 1)) }}
                    </div>
                </td>
                <td style="vertical-align: middle;">
                    <div style="font-size: 18px; font-weight: bold; color: #12202E; line-height: 1.1;">
                        {{ $business_name }}
                    </div>
                    @if (filled($business_tagline ?? null))
                        <div style="font-size: 9.5px; color: #4A5866; margin-top: 2px;">
                            {{ $business_tagline }}
                        </div>
                    @endif
                </td>
            </tr>
            </table>
        @endif
    </td>

    {{-- Right: Business Contact Details with icons --}}
    <td style="width: 50%; vertical-align: top; text-align: right;">
        <div style="font-size: 12px; font-weight: bold; color: #12202E; margin-bottom: 3px;">
            {{ $business_name }}
        </div>
        @if (!empty($business_address))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconLocation !!}{{ $business_address }}
            </div>
        @endif
        @if (!empty($business_email))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconEmail !!}{{ $business_email }}
            </div>
        @endif
        @if (filled($business_website ?? null))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconGlobe !!}{{ $business_website }}
            </div>
        @endif
        @if (!empty($business_phone))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconPhone !!}{{ $business_phone }}
            </div>
        @endif
    </td>
</tr>
</table>

<div style="height: 16px;"></div>

{{-- ====================================================================
     2. INVOICE TITLE, META DETAILS (Left) & TOTAL AMOUNT CARD (Right)
     ==================================================================== --}}
<table cellpadding="0" cellspacing="0" style="width: 100%;">
<tr>
    {{-- Left: INVOICE title + Number, Date, Due Date --}}
    <td style="width: 55%; vertical-align: top;">
        <div style="font-size: 24px; font-weight: bold; color: #12202E; letter-spacing: 0.5px; line-height: 1; margin-bottom: 8px;">
            INVOICE
        </div>
        <table cellpadding="0" cellspacing="0" style="font-size: 11px;">
        <tr>
            <td style="color: #4A5866; padding: 2px 12px 2px 0;">Invoice No:</td>
            <td style="font-weight: bold; color: #12202E; padding: 2px 0;">{{ $invoice_number }}</td>
        </tr>
        <tr>
            <td style="color: #4A5866; padding: 2px 12px 2px 0;">Issue Date:</td>
            <td style="color: #12202E; padding: 2px 0;">{{ $invoice_date }}</td>
        </tr>
        @if (!empty($due_date))
        <tr>
            <td style="color: #4A5866; padding: 2px 12px 2px 0;">Due Date:</td>
            <td style="color: #12202E; padding: 2px 0;">{{ $due_date }}</td>
        </tr>
        @endif
        @if ($badgeLabel !== '')
        <tr>
            <td style="color: #4A5866; padding: 3px 12px 2px 0;">Status:</td>
            <td style="padding: 3px 0;">
                <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
            </td>
        </tr>
        @endif
        </table>
    </td>

    {{-- Right: Total Amount Card box (web paper styling) --}}
    <td style="width: 45%; vertical-align: top; text-align: right;">
        <table align="right" cellpadding="0" cellspacing="0" class="card-box" style="border-collapse: separate; min-width: 200px;">
        <tr>
            <td style="padding: 10px 16px; text-align: left;">
                <div style="font-size: 10px; font-weight: 600; color: #4A5866; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">
                    Total Amount
                </div>
                <div style="font-size: 21px; font-weight: bold; color: #12202E; line-height: 1.1; white-space: nowrap;">
                    {!! $money($totals['total']) !!}
                </div>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

<div style="height: 16px;"></div>

{{-- ====================================================================
     3. BILL TO & FROM COLUMNS
     ==================================================================== --}}
<table cellpadding="0" cellspacing="0" style="width: 100%; page-break-inside: avoid;">
<tr>
    {{-- Bill To --}}
    <td style="width: 50%; vertical-align: top; padding-right: 12px;">
        <div style="font-size: 10px; font-weight: 600; color: #4A5866; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
            Bill To
        </div>
        <div style="font-size: 13.5px; font-weight: bold; color: #12202E; margin-bottom: 5px;">
            {{ $hasClient ? $client_name : '—' }}
        </div>
        @if (!empty($client_email))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconEmail !!}{{ $client_email }}
            </div>
        @endif
        @if (!empty($client_phone))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconPhone !!}{{ $client_phone }}
            </div>
        @endif
        @if (!empty($client_address))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconLocation !!}{{ $client_address }}
            </div>
        @endif
    </td>

    {{-- From --}}
    <td style="width: 50%; vertical-align: top; padding-left: 12px;">
        <div style="font-size: 10px; font-weight: 600; color: #4A5866; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
            From
        </div>
        <div style="font-size: 13.5px; font-weight: bold; color: #12202E; margin-bottom: 5px;">
            {{ $business_name }}
        </div>
        @if (!empty($business_address))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconLocation !!}{{ $business_address }}
            </div>
        @endif
        @if (!empty($business_email))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconEmail !!}{{ $business_email }}
            </div>
        @endif
        @if (filled($business_website ?? null))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconGlobe !!}{{ $business_website }}
            </div>
        @endif
        @if (!empty($business_phone))
            <div style="font-size: 10px; color: #4A5866; margin-bottom: 2px;">
                {!! $iconPhone !!}{{ $business_phone }}
            </div>
        @endif
    </td>
</tr>
</table>

<div style="height: 14px;"></div>

{{-- ====================================================================
     4. ITEMS TABLE (Navy Dark Header, Clean Content Rows)
     ==================================================================== --}}
<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
    <tr style="background-color: #12202E; color: #FFFFFF;">
        <th style="width: 6%; text-align: center; padding: 8px 6px; font-size: 10px; font-weight: 600;">#</th>
        <th style="width: 48%; text-align: left; padding: 8px 10px; font-size: 10px; font-weight: 600;">Item Description</th>
        <th style="width: 12%; text-align: center; padding: 8px 6px; font-size: 10px; font-weight: 600;">Quantity</th>
        <th style="width: 17%; text-align: right; padding: 8px 10px; font-size: 10px; font-weight: 600;">Unit Price</th>
        <th style="width: 17%; text-align: right; padding: 8px 10px; font-size: 10px; font-weight: 600;">Total</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($items as $it)
        <tr style="border-bottom: 1px solid #E8EAF0; page-break-inside: avoid;">
            <td style="text-align: center; padding: 8px 6px; font-weight: bold; color: #12202E; vertical-align: top;">
                {{ $loop->iteration }}
            </td>
            <td style="text-align: left; padding: 8px 10px; vertical-align: top;">
                <div style="font-weight: bold; color: #12202E; font-size: 11px;">
                    {{ $it['description'] }}
                </div>
                @if (filled($it['details'] ?? null))
                    <div style="color: #4A5866; font-size: 9.5px; margin-top: 2px; line-height: 1.3;">
                        {{ $it['details'] }}
                    </div>
                @endif
            </td>
            <td style="text-align: center; padding: 8px 6px; color: #12202E; vertical-align: top;">
                {{ $it['quantity'] }}
            </td>
            <td style="text-align: right; padding: 8px 10px; color: #12202E; vertical-align: top; white-space: nowrap;">
                {!! $money($it['price']) !!}
            </td>
            <td style="text-align: right; padding: 8px 10px; font-weight: bold; color: #12202E; vertical-align: top; white-space: nowrap;">
                {!! $money($it['line_total']) !!}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" style="text-align: center; padding: 14px; color: #4A5866; font-style: italic; border-bottom: 1px solid #E8EAF0;">
                No items on this invoice.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

<div style="height: 14px;"></div>

{{-- ====================================================================
     5. PAYMENT DETAILS (Left) & TOTALS CALCULATION (Right)
     ==================================================================== --}}
<table cellpadding="0" cellspacing="0" style="width: 100%; page-break-inside: avoid;">
<tr>
    {{-- Left: Payment Details Card --}}
    <td style="width: 50%; vertical-align: top; padding-right: 12px;">
        @if ($hasPayment)
            <table cellpadding="0" cellspacing="0" class="card-box" style="width: 100%;">
            <tr>
                <td style="padding: 10px 12px;">
                    <div style="font-size: 11.5px; font-weight: bold; color: #12202E; margin-bottom: 6px;">
                        Payment Details
                    </div>
                    <table cellpadding="0" cellspacing="0" style="width: 100%; font-size: 10px;">
                        @if (filled($bank_name ?? null))
                        <tr>
                            <td style="color: #4A5866; padding: 2px 0; width: 44%;">Bank Name:</td>
                            <td style="font-weight: 600; color: #12202E; padding: 2px 0;">{{ $bank_name }}</td>
                        </tr>
                        @endif
                        @if (filled($account_title ?? null))
                        <tr>
                            <td style="color: #4A5866; padding: 2px 0;">Account Title:</td>
                            <td style="font-weight: 600; color: #12202E; padding: 2px 0;">{{ $account_title }}</td>
                        </tr>
                        @endif
                        @if (filled($account_number ?? null))
                        <tr>
                            <td style="color: #4A5866; padding: 2px 0;">Account Number:</td>
                            <td style="font-weight: 600; color: #12202E; padding: 2px 0;">{{ $account_number }}</td>
                        </tr>
                        @endif
                        @if (filled($iban ?? null))
                        <tr>
                            <td style="color: #4A5866; padding: 2px 0;">IBAN:</td>
                            <td style="font-weight: 600; color: #12202E; padding: 2px 0;">{{ $iban }}</td>
                        </tr>
                        @endif
                        @if (filled($payment_method ?? null))
                        <tr>
                            <td style="color: #4A5866; padding: 2px 0;">Payment Method:</td>
                            <td style="font-weight: 600; color: #12202E; padding: 2px 0;">{{ $payment_method }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
            </table>
        @endif
    </td>

    {{-- Right: Subtotal, Tax, Discount, Total Pill/Box --}}
    <td style="width: 50%; vertical-align: top; padding-left: 12px;">
        <table cellpadding="0" cellspacing="0" style="width: 100%; font-size: 10.5px;">
            <tr>
                <td style="color: #4A5866; padding: 2px 0; text-align: left;">Subtotal</td>
                <td style="font-weight: bold; color: #12202E; padding: 2px 0; text-align: right; white-space: nowrap;">{!! $money($totals['subtotal']) !!}</td>
            </tr>
            @if ($taxPct > 0 || (float) $totals['tax'] > 0)
            <tr>
                <td style="color: #4A5866; padding: 2px 0; text-align: left;">{{ $taxLabel }}</td>
                <td style="font-weight: bold; color: #12202E; padding: 2px 0; text-align: right; white-space: nowrap;">{!! $money($totals['tax']) !!}</td>
            </tr>
            @endif
            @if ((float) $totals['discount'] > 0)
            <tr>
                <td style="color: #4A5866; padding: 2px 0; text-align: left;">Discount</td>
                <td style="font-weight: bold; color: #12202E; padding: 2px 0; text-align: right; white-space: nowrap;">- {!! $money($totals['discount']) !!}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2" style="padding-top: 6px;">
                    <table cellpadding="0" cellspacing="0" class="card-box" style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 12px; font-size: 12px; font-weight: bold; color: #12202E; text-align: left;">
                                Total
                            </td>
                            <td style="padding: 8px 12px; font-size: 15px; font-weight: bold; color: #12202E; text-align: right; white-space: nowrap;">
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

<div style="height: 14px;"></div>

{{-- ====================================================================
     6. NOTES & SIGNATURE SECTION
     ==================================================================== --}}
@if ($noteLines->isNotEmpty() || $hasSignature)
<table cellpadding="0" cellspacing="0" style="width: 100%; page-break-inside: avoid;">
<tr>
    {{-- Left: Notes with bullets --}}
    <td style="width: 60%; vertical-align: top; padding-right: 16px;">
        @if ($noteLines->isNotEmpty())
            <div style="font-size: 11px; font-weight: bold; color: #12202E; margin-bottom: 4px;">
                {!! $iconNotes !!}Notes
            </div>
            <ul style="margin: 0; padding-left: 16px; color: #4A5866; font-size: 10px; line-height: 1.45;">
                @foreach ($noteLines as $line)
                    <li style="margin-bottom: 2px;">{{ $line }}</li>
                @endforeach
            </ul>
        @endif
    </td>

    {{-- Right: Handwritten Signature Block --}}
    <td style="width: 40%; vertical-align: top; text-align: right;">
        @if ($hasSignature)
            <table align="right" cellpadding="0" cellspacing="0" style="width: 180px; text-align: center;">
            <tr>
                <td>
                    <div style="font-family: 'Brush Script MT', 'DejaVu Serif', cursive; font-style: italic; font-size: 22px; color: #12202E; line-height: 1.1; margin-bottom: 3px;">
                        {{ $signature_name }}
                    </div>
                    <div style="border-top: 1px solid #94A3B8; margin: 3px 0 5px 0;"></div>
                    <div style="font-weight: bold; font-size: 10.5px; color: #12202E;">
                        {{ $signature_name }}
                    </div>
                    @if (filled($signature_title ?? null))
                        <div style="font-size: 9.5px; color: #4A5866; margin-top: 1px;">
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
@endif

<div style="height: 16px;"></div>

{{-- ====================================================================
     7. FOOTER: Thank You Message (Left) & QR Code (Right)
     ==================================================================== --}}
<table cellpadding="0" cellspacing="0" style="width: 100%; border-top: 1px solid #EAE3D2; padding-top: 10px; page-break-inside: avoid;">
<tr>
    <td style="font-size: 10px; color: #4A5866; vertical-align: middle;">
        Thank you for choosing {{ filled($business_name ?? null) && $business_name !== 'Your business' ? $business_name : 'InvoiceFlow' }}!
    </td>
    <td style="text-align: right; vertical-align: middle;">
        <table align="right" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding-right: 6px; vertical-align: middle;">
                {{-- Clean fallback QR block --}}
                <div style="width: 28px; height: 28px; border: 2px solid #12202E; padding: 2px;">
                    <div style="width: 8px; height: 8px; background: #12202E; float: left;"></div>
                    <div style="width: 8px; height: 8px; background: #12202E; float: right;"></div>
                    <div style="width: 8px; height: 8px; background: #12202E; margin-top: 12px;"></div>
                </div>
            </td>
            <td style="font-size: 8.5px; color: #4A5866; line-height: 1.2; text-align: left; vertical-align: middle;">
                Scan to<br>view online
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

</div>{{-- /.wrap --}}

</body>
</html>
