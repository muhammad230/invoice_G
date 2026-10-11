@extends('layouts.app')

@section('title', 'Help center — InvoiceFlow')
@section('meta_description', 'Answers to common InvoiceFlow questions: creating invoices, adding clients, downloading PDFs, marking invoices paid, currencies, resetting your password, and deleting your account.')

@section('content')

{{-- ============================================================
     HERO + SEARCH
     ============================================================ --}}
<header class="page-hero">
    <div class="container">
        <span class="section-label">
            <i class="bi bi-life-preserver me-1"></i>
            Help center
        </span>
        <h1>How can we help?</h1>
        <p>
            Search the questions below, or
            <a href="{{ route('contact') }}">contact us</a> if you're still stuck.
        </p>
    </div>
</header>

<section class="pt-0">
    <div class="container">
        {{-- Search box: filters the questions on this page with plain JS. --}}
        <div class="help-search">
            <label for="helpSearch" class="visually-hidden">Search help topics</label>
            <input type="search"
                   id="helpSearch"
                   class="form-control"
                   placeholder="Search help topics…"
                   autocomplete="off">
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div id="helpGroups">

                    {{-- ================= Getting started ================= --}}
                    <div class="help-group" data-help-group>
                        <h2 class="h4 mb-3 mt-4">
                            <i class="bi bi-rocket-takeoff me-2" style="color: var(--saffron);"></i>
                            Getting started
                        </h2>
                        <div class="accordion faq-accordion" id="helpGroup1">
                            {{-- Q1 --}}
                            <div class="accordion-item" data-search="how do i create an invoice new dashboard invoices">
                                <h3 class="accordion-header" id="hg1q1h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg1q1" aria-expanded="false" aria-controls="hg1q1">
                                        How do I create an invoice?
                                    </button>
                                </h3>
                                <div id="hg1q1" class="accordion-collapse collapse" aria-labelledby="hg1q1h" data-bs-parent="#helpGroup1">
                                    <div class="accordion-body">
                                        Open <strong>Invoices</strong> in the navbar and choose
                                        <strong>New invoice</strong>, or click <strong>New invoice</strong>.
                                        Pick a client, add line items, and set your tax rate and discount.
                                        The totals update as you type. Save it, and it's ready to send.
                                    </div>
                                </div>
                            </div>
                            {{-- Q2 --}}
                            <div class="accordion-item" data-search="how do i add a client people add client form">
                                <h3 class="accordion-header" id="hg1q2h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg1q2" aria-expanded="false" aria-controls="hg1q2">
                                        How do I add a client?
                                    </button>
                                </h3>
                                <div id="hg1q2" class="accordion-collapse collapse" aria-labelledby="hg1q2h" data-bs-parent="#helpGroup1">
                                    <div class="accordion-body">
                                        Go to <strong>Clients</strong> and click <strong>Add client</strong>.
                                        Only the name is required — email, phone and address are optional.
                                        You can also add a client on the fly while building an invoice.
                                    </div>
                                </div>
                            </div>
                            {{-- Q3 --}}
                            <div class="accordion-item" data-search="save a product or service saved services price box">
                                <h3 class="accordion-header" id="hg1q3h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg1q3" aria-expanded="false" aria-controls="hg1q3">
                                        How do I save a product or service?
                                    </button>
                                </h3>
                                <div id="hg1q3" class="accordion-collapse collapse" aria-labelledby="hg1q3h" data-bs-parent="#helpGroup1">
                                    <div class="accordion-body">
                                        Open <strong>Services</strong> and click <strong>Add service</strong>.
                                        Give it a name and a price, and it becomes available to drop into any
                                        invoice line item in one click.
                                    </div>
                                </div>
                            </div>
                            {{-- Q4 --}}
                            <div class="accordion-item" data-search="download invoice pdf export file">
                                <h3 class="accordion-header" id="hg1q4h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg1q4" aria-expanded="false" aria-controls="hg1q4">
                                        How do I download my invoice as a PDF?
                                    </button>
                                </h3>
                                <div id="hg1q4" class="accordion-collapse collapse" aria-labelledby="hg1q4h" data-bs-parent="#helpGroup1">
                                    <div class="accordion-body">
                                        Open the invoice and click <strong>Download PDF</strong>. InvoiceFlow
                                        generates a clean, print-ready PDF you can save or email to your client.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= Invoices and PDF ================= --}}
                    <div class="help-group" data-help-group>
                        <h2 class="h4 mb-3 mt-5">
                            <i class="bi bi-receipt me-2" style="color: var(--saffron);"></i>
                            Invoices and PDF
                        </h2>
                        <div class="accordion faq-accordion" id="helpGroup2">
                            {{-- Q5 --}}
                            <div class="accordion-item" data-search="mark an invoice as paid payment status cash">
                                <h3 class="accordion-header" id="hg2q1h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg2q1" aria-expanded="false" aria-controls="hg2q1">
                                        How do I mark an invoice as paid?
                                    </button>
                                </h3>
                                <div id="hg2q1" class="accordion-collapse collapse" aria-labelledby="hg2q1h" data-bs-parent="#helpGroup2">
                                    <div class="accordion-body">
                                        Open a pending invoice and click <strong>Mark as paid</strong>. Choose
                                        the payment date and method — Cash, Bank Transfer, JazzCash, Easypaisa,
                                        Card or Other. The invoice status flips to <em>Paid</em> and the payment
                                        is logged automatically.
                                    </div>
                                </div>
                            </div>
                            {{-- Q6 --}}
                            <div class="accordion-item" data-search="undo a payment delete mistake reopen">
                                <h3 class="accordion-header" id="hg2q2h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg2q2" aria-expanded="false" aria-controls="hg2q2">
                                        How do I undo a payment?
                                    </button>
                                </h3>
                                <div id="hg2q2" class="accordion-collapse collapse" aria-labelledby="hg2q2h" data-bs-parent="#helpGroup2">
                                    <div class="accordion-body">
                                        Open the paid invoice and click <strong>Undo this payment</strong>, or
                                        find it on the <strong>Payments</strong> page and click <strong>Undo</strong>.
                                        The payment is removed and the invoice returns to <em>Pending</em>.
                                    </div>
                                </div>
                            </div>
                            {{-- Q7 --}}
                            <div class="accordion-item" data-search="which currencies are supported usd eur gbp pkr money">
                                <h3 class="accordion-header" id="hg2q3h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg2q3" aria-expanded="false" aria-controls="hg2q3">
                                        Which currencies are supported?
                                    </button>
                                </h3>
                                <div id="hg2q3" class="accordion-collapse collapse" aria-labelledby="hg2q3h" data-bs-parent="#helpGroup2">
                                    <div class="accordion-body">
                                        InvoiceFlow supports <strong>USD ($)</strong>, <strong>EUR (€)</strong>,
                                        <strong>GBP (£)</strong> and <strong>PKR (₨)</strong>. You choose the
                                        currency per invoice, and the correct symbol is used everywhere — including
                                        the PDF and reports.
                                    </div>
                                </div>
                            </div>
                            {{-- Q8 --}}
                            <div class="accordion-item" data-search="change business details logo address bank profile settings">
                                <h3 class="accordion-header" id="hg2q4h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg2q4" aria-expanded="false" aria-controls="hg2q4">
                                        How do I change my business details?
                                    </button>
                                </h3>
                                <div id="hg2q4" class="accordion-collapse collapse" aria-labelledby="hg2q4h" data-bs-parent="#helpGroup2">
                                    <div class="accordion-body">
                                        Go to <strong>Settings → Business Profile</strong>. Here you can set
                                        your business name, logo, address, tax number and bank details. These
                                        appear on your invoices and PDFs.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= Account and payments ================= --}}
                    <div class="help-group" data-help-group>
                        <h2 class="h4 mb-3 mt-5">
                            <i class="bi bi-person-gear me-2" style="color: var(--saffron);"></i>
                            Account and payments
                        </h2>
                        <div class="accordion faq-accordion" id="helpGroup3">
                            {{-- Q9 --}}
                            <div class="accordion-item" data-search="reset my password forgot login change">
                                <h3 class="accordion-header" id="hg3q1h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg3q1" aria-expanded="false" aria-controls="hg3q1">
                                        How do I reset my password?
                                    </button>
                                </h3>
                                <div id="hg3q1" class="accordion-collapse collapse" aria-labelledby="hg3q1h" data-bs-parent="#helpGroup3">
                                    <div class="accordion-body">
                                        On the sign-in page click <strong>Forgot password?</strong> and enter
                                        your email. We'll send you a link to set a new password. If you're already
                                        signed in, go to <strong>Account</strong> and use the
                                        <strong>Change password</strong> card instead.
                                    </div>
                                </div>
                            </div>
                            {{-- Q10 --}}
                            <div class="accordion-item" data-search="change my email or name account profile">
                                <h3 class="accordion-header" id="hg3q2h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg3q2" aria-expanded="false" aria-controls="hg3q2">
                                        How do I change my email or name?
                                    </button>
                                </h3>
                                <div id="hg3q2" class="accordion-collapse collapse" aria-labelledby="hg3q2h" data-bs-parent="#helpGroup3">
                                    <div class="accordion-body">
                                        Open your user menu and choose <strong>Account</strong>, then update
                                        your details on the <strong>Profile</strong> card. For your security,
                                        changing your email address asks for your current password.
                                    </div>
                                </div>
                            </div>
                            {{-- Q11 --}}
                            <div class="accordion-item" data-search="how do i delete my account danger zone remove data">
                                <h3 class="accordion-header" id="hg3q3h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg3q3" aria-expanded="false" aria-controls="hg3q3">
                                        How do I delete my account?
                                    </button>
                                </h3>
                                <div id="hg3q3" class="accordion-collapse collapse" aria-labelledby="hg3q3h" data-bs-parent="#helpGroup3">
                                    <div class="accordion-body">
                                        Go to <strong>Account</strong> and scroll to the <strong>Danger zone</strong>.
                                        Click <strong>Delete account</strong>, confirm with your password and
                                        accept the warning. This permanently deletes your account and all of your
                                        clients, invoices, payments and services. It cannot be undone.
                                    </div>
                                </div>
                            </div>
                            {{-- Q12 --}}
                            <div class="accordion-item" data-search="contact support message help get in touch">
                                <h3 class="accordion-header" id="hg3q4h">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hg3q4" aria-expanded="false" aria-controls="hg3q4">
                                        How do I contact support?
                                    </button>
                                </h3>
                                <div id="hg3q4" class="accordion-collapse collapse" aria-labelledby="hg3q4h" data-bs-parent="#helpGroup3">
                                    <div class="accordion-body">
                                        Use the <a href="{{ route('contact') }}">contact form</a> and we'll get
                                        back to you by email. Include as much detail as you can so we can help faster.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /#helpGroups --}}

                {{-- Shown when the search finds nothing. --}}
                <p class="help-no-results" id="helpNoResults">
                    <i class="bi bi-search me-1"></i>
                    No matching questions. Try a different word, or
                    <a href="{{ route('contact') }}">contact us</a>.
                </p>

                {{-- Still need help? --}}
                <div class="text-center mt-5">
                    <a href="{{ route('contact') }}" class="btn btn-saffron">
                        Still need help? Contact us
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
    (function () {
        'use strict';

        var input   = document.getElementById('helpSearch');
        var groups  = document.querySelectorAll('[data-help-group]');
        var noHits  = document.getElementById('helpNoResults');
        if (!input) return;

        function filter() {
            var term = input.value.trim().toLowerCase();
            var totalVisible = 0;

            groups.forEach(function (group) {
                var items = group.querySelectorAll('.accordion-item');
                var groupVisible = 0;

                items.forEach(function (item) {
                    var haystack = (item.getAttribute('data-search') || '') + ' ' + item.textContent.toLowerCase();
                    var match = term === '' || haystack.indexOf(term) !== -1;
                    item.classList.toggle('d-none', !match);
                    if (match) groupVisible++;
                });

                // Hide the whole group (heading included) when none of its questions match.
                group.classList.toggle('d-none', groupVisible === 0);
                totalVisible += groupVisible;
            });

            if (noHits) noHits.style.display = totalVisible === 0 ? 'block' : 'none';
        }

        input.addEventListener('input', filter);
    })();
</script>
@endsection
