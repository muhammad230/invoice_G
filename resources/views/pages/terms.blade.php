@extends('layouts.app')

@section('title', 'Terms of Service — InvoiceFlow')
@section('meta_description', 'The terms for using InvoiceFlow: your account, your data, acceptable use, and how to close your account.')

@section('content')

<header class="page-hero">
    <div class="container">
        <span class="section-label">
            <i class="bi bi-file-earmark-text me-1"></i>
            Terms
        </span>
        <h1>Terms of Service</h1>
        <p>The ground rules for using InvoiceFlow.</p>
    </div>
</header>

<section class="pt-0">
    <div class="container">
        <div class="legal-content">
            {{-- Required draft banner --}}
            <div class="draft-notice" role="note">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Draft. Review with a professional before launch.</span>
            </div>

            <p>Last updated: {{ date('F j, Y') }}</p>

            <h2>1. Agreement</h2>
            <p>
                By creating an account or using InvoiceFlow, you agree to these terms.
                If you do not agree, please do not use the service.
            </p>

            <h2>2. Your account</h2>
            <p>
                You are responsible for the activity on your account and for keeping your
                password secure. Provide accurate information, and let us know if you
                believe your account has been compromised.
            </p>

            <h2>3. Your content and data</h2>
            <p>
                The clients, invoices, payments, products and business details you create
                belong to you. We store them so we can provide the service, as described in
                our <a href="{{ route('privacy') }}">Privacy Policy</a>. You are responsible
                for making sure you have the right to store any data you enter about your
                own clients.
            </p>

            <h2>4. Acceptable use</h2>
            <p>You agree not to:</p>
            <ul>
                <li>Use InvoiceFlow for anything illegal or fraudulent.</li>
                <li>Attempt to access another user's data or disrupt the service.</li>
                <li>Upload malicious files or abuse the contact form.</li>
            </ul>
            <p>We may suspend accounts that break these rules.</p>

            <h2>5. Plans and pricing</h2>
            <p>
                InvoiceFlow currently offers a Free plan. Additional paid plans shown on
                our <a href="{{ route('pricing') }}">Pricing page</a> are coming soon; when
                they launch, their features and prices will be listed there. Nothing on the
                Pricing page is a charge today.
            </p>

            <h2>6. Availability and disclaimers</h2>
            <p>
                We work to keep InvoiceFlow available, but the service is provided "as is"
                without warranties. We are not liable for lost profits or for decisions you
                make based on the app. Always keep your own records.
            </p>

            <h2>7. Closing your account</h2>
            <p>
                You can close your account at any time from
                <strong>Account → Danger zone</strong>. This permanently deletes your
                account and all associated data, and cannot be undone.
            </p>

            <h2>8. Changes to these terms</h2>
            <p>
                We may update these terms from time to time. We'll change the date at the
                top when we do. Continued use means you accept the new terms.
            </p>

            <h2>9. Contact</h2>
            <p>
                Questions about these terms? Use our
                <a href="{{ route('contact') }}">contact form</a>.
            </p>
        </div>
    </div>
</section>

@endsection
