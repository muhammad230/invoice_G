{{--
    Invoice / reminder email.

    Styled to match the site: cream background (#FAF6EE), navy text (#12202E),
    saffron CTA (#F2A33A). Email clients are fussy, so this is a table-based
    layout with inline styles only (no external stylesheet, no flexbox).
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $invoiceNumber }}</title>
</head>
<body style="margin:0; padding:0; background-color:#FAF6EE; color:#12202E; font-family:Arial, Helvetica, sans-serif;">

{{-- Hidden preheader shown in the inbox preview. --}}
<div style="display:none; max-height:0; overflow:hidden; opacity:0; color:#FAF6EE;">
    {{ $isReminder ? 'A friendly reminder about invoice ' : 'Invoice ' }}{{ $invoiceNumber }} — {{ $amount }}.
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF6EE;">
    <tr>
        <td align="center" style="padding:24px 12px;">

            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; background-color:#FFFFFF; border-radius:12px; overflow:hidden;">

                {{-- Saffron accent bar --}}
                <tr>
                    <td style="height:6px; line-height:6px; font-size:0; background-color:#F2A33A;">&nbsp;</td>
                </tr>

                {{-- Business name --}}
                <tr>
                    <td style="padding:28px 32px 0;">
                        <div style="font-family:Georgia, 'Times New Roman', serif; font-size:20px; font-weight:bold; color:#12202E;">
                            {{ $businessName }}
                        </div>
                    </td>
                </tr>

                {{-- Greeting + user message --}}
                <tr>
                    <td style="padding:16px 32px 0;">
                        <p style="margin:0 0 14px; font-size:16px; line-height:1.5; color:#12202E;">
                            Hi {{ $greetingName ?: 'there' }},
                        </p>
                        <div style="margin:0 0 22px; font-size:15px; line-height:1.6; color:#4A5866;">
                            {!! nl2br(e($bodyMessage)) !!}
                        </div>
                    </td>
                </tr>

                {{-- Invoice summary box --}}
                <tr>
                    <td style="padding:0 32px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF6EE; border-radius:10px;">
                            <tr>
                                <td style="padding:18px 20px;">
                                    <div style="font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:#4A5866; margin-bottom:10px;">
                                        Invoice summary
                                    </div>
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="padding:4px 0; font-size:14px; color:#4A5866;">Invoice</td>
                                            <td style="padding:4px 0; font-size:14px; color:#12202E; text-align:right; font-weight:bold;">{{ $invoiceNumber }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:4px 0; font-size:14px; color:#4A5866;">Amount due</td>
                                            <td style="padding:4px 0; font-size:14px; color:#12202E; text-align:right; font-weight:bold;">{{ $amount }} <span style="color:#4A5866; font-weight:normal;">{{ $currency }}</span></td>
                                        </tr>
                                        @if ($dueDate)
                                            <tr>
                                                <td style="padding:4px 0; font-size:14px; color:#4A5866;">Due date</td>
                                                <td style="padding:4px 0; font-size:14px; color:#12202E; text-align:right;">{{ $dueDate }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td style="padding:4px 0; font-size:14px; color:#4A5866;">Status</td>
                                            <td style="padding:4px 0; font-size:14px; color:#12202E; text-align:right;">{{ $status }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Saffron CTA --}}
                <tr>
                    <td style="padding:24px 32px 0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" bgcolor="#F2A33A" style="border-radius:8px;">
                                    <a href="{{ $businessWebsite ?: ($businessEmail ? 'mailto:' . $businessEmail : '#') }}"
                                       style="display:inline-block; padding:12px 22px; font-size:15px; font-weight:bold; color:#12202E; text-decoration:none;">
                                        {{ $businessWebsite ? 'Visit ' . $businessName : 'Reply to this email' }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Attachment hint --}}
                <tr>
                    <td style="padding:16px 32px 28px;">
                        <p style="margin:0; font-size:13px; line-height:1.6; color:#4A5866;">
                            The invoice is attached to this email as a PDF for your records.
                        </p>
                    </td>
                </tr>

                {{-- Footer with business contact details --}}
                <tr>
                    <td style="padding:20px 32px; background-color:#12202E;">
                        <div style="font-size:14px; font-weight:bold; color:#FAF6EE;">{{ $businessName }}</div>
                        @if ($businessEmail)
                            <div style="font-size:12px; color:#C9D2DA; margin-top:4px;">{{ $businessEmail }}</div>
                        @endif
                        @if ($businessPhone)
                            <div style="font-size:12px; color:#C9D2DA; margin-top:2px;">{{ $businessPhone }}</div>
                        @endif
                    </td>
                </tr>
            </table>

            <div style="max-width:600px; margin:14px auto 0; font-size:11px; color:#8A938F; text-align:center;">
                Sent with InvoiceFlow.
            </div>

        </td>
    </tr>
</table>

</body>
</html>
