@extends('layouts.app')

@section('title', 'Privacy Policy — InvoiceFlow')
@section('meta_description', 'How InvoiceFlow handles your data: what we store, why we store it, and how to delete your account and data.')

@section('content')

<header class="page-hero">
    <div class="container">
        <span class="section-label">
            <i class="bi bi-shield-lock me-1"></i>
            Privacy
        </span>
        <h1>Privacy Policy</h1>
        <p>Plain-language notes on what InvoiceFlow stores and why.</p>
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

            <h2>1. Who we are</h2>
            <p>
                InvoiceFlow is an invoicing tool for freelancers and small businesses.
                You use it to create invoices, manage clients, and keep track of payments.
                This policy explains what happens to your data when you do.
            </p>

            <h2>2. What we store</h2>
            <p>When you use InvoiceFlow, we store the information you give us:</p>
            <ul>
                <li><strong>Account details</strong> — your name and email address, and a
                    securely hashed version of your password (we never store your password
                    in plain text).</li>
                <li><strong>Business profile</strong> — the business name, address, contact
                    details, logo and bank details you enter for your invoices.</li>
                <li><strong>Clients</strong> — the names and contact details of the people
                    and companies you invoice.</li>
                <li><strong>Invoices and payments</strong> — the invoices you create, their
                    line items, totals, taxes, discounts, currencies, and any payments you
                    record against them.</li>
                <li><strong>Products and services</strong> — the saved items you reuse on
                    invoices.</li>
                <li><strong>Messages</strong> — if you use the contact form, we store the
                    name, email, message and IP address you submit, so we can reply.</li>
            </ul>

            <h2>3. Why we store it</h2>
            <p>We use your data only to provide the service: to keep you signed in, to
                generate your invoices and PDFs, to show your reports, and to let you
                manage your account. We do not sell your data.</p>

            <h2>4. How we protect it</h2>
            <p>
                Passwords are stored as one-way hashes. Access to your records is scoped
                to your account — other users cannot see or edit your clients, invoices,
                or payments. You should still use a strong, unique password.
            </p>

            <h2>5. Cookies and sessions</h2>
            <p>
                We use a session cookie to keep you signed in and to protect forms against
                cross-site request forgery. These are required for the app to work.
            </p>

            <h2>6. Deleting your data</h2>
            <p>
                You can delete your account at any time from
                <strong>Account → Danger zone</strong>. This permanently removes your
                account and all of your data — business profile, clients, invoices,
                payments, products and any uploaded logo. This action cannot be undone.
            </p>

            <h2>7. Changes to this policy</h2>
            <p>
                If we make meaningful changes, we'll update the date at the top of this
                page. Continued use of InvoiceFlow means you accept the updated policy.
            </p>

            <h2>8. Contact</h2>
            <p>
                Questions about this policy? Use our
                <a href="{{ route('contact') }}">contact form</a>.
            </p>
        </div>
    </div>
</section>

@endsection
