@extends('layouts.app')

@section('title', 'Features — InvoiceFlow')
@section('meta_description', 'Everything InvoiceFlow can do: live invoice preview, automatic tax and discounts, multi-currency invoices, PDF export, clients, saved services, payment tracking, and reports.')

@section('content')

{{-- ============================================================
     HERO
     ============================================================ --}}
<header class="page-hero">
    <div class="container">
        <span class="section-label">
            <i class="bi bi-stars me-1"></i>
            Features
        </span>
        <h1>Everything you need to invoice</h1>
        <p>
            InvoiceFlow keeps invoicing simple: create, send, and get paid —
            with the tools you actually use, and none of the clutter.
        </p>
    </div>
</header>

{{-- ============================================================
     FEATURE GRID — 8 features that exist in the app today
     ============================================================ --}}
<section class="pt-0">
    <div class="container">
        <div class="row g-4">
            {{-- 1. Live invoice preview --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon saffron">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </div>
                    <h3>Live invoice preview</h3>
                    <p>Watch your invoice update as you type — totals, taxes and layout in real time.</p>
                </div>
            </div>

            {{-- 2. Automatic tax and discount calculation --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon ink">
                        <i class="bi bi-calculator" aria-hidden="true"></i>
                    </div>
                    <h3>Automatic tax &amp; discount</h3>
                    <p>Set a tax rate and a discount, and InvoiceFlow calculates the totals for you.</p>
                </div>
            </div>

            {{-- 3. Multiple currencies --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon sage">
                        <i class="bi bi-currency-exchange" aria-hidden="true"></i>
                    </div>
                    <h3>Multiple currencies</h3>
                    <p>Bill in USD, EUR, GBP or PKR — with the correct symbol on every invoice.</p>
                </div>
            </div>

            {{-- 4. PDF invoices --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon overdue">
                        <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                    </div>
                    <h3>PDF invoices</h3>
                    <p>Download a clean, print-ready PDF for any invoice in a single click.</p>
                </div>
            </div>

            {{-- 5. Client list --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon sage">
                        <i class="bi bi-people" aria-hidden="true"></i>
                    </div>
                    <h3>Client list</h3>
                    <p>Save every client once and reuse their details on all future invoices.</p>
                </div>
            </div>

            {{-- 6. Saved products and services --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon saffron">
                        <i class="bi bi-box-seam" aria-hidden="true"></i>
                    </div>
                    <h3>Saved services</h3>
                    <p>Store your products and services with prices, then drop them into any invoice.</p>
                </div>
            </div>

            {{-- 7. Payment tracking --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon ink">
                        <i class="bi bi-cash-coin" aria-hidden="true"></i>
                    </div>
                    <h3>Payment tracking</h3>
                    <p>Record payments by Cash, Bank Transfer, JazzCash, Easypaisa or Card.</p>
                </div>
            </div>

            {{-- 8. Reports and charts --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon overdue">
                        <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                    </div>
                    <h3>Reports &amp; charts</h3>
                    <p>See revenue over time, top clients, and paid vs unpaid at a glance.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CALL TO ACTION
     ============================================================ --}}
<section class="pt-0">
    <div class="container">
        <div class="cta-section">
            <h2>Create your first invoice</h2>
            <p>Start free — no credit card required. You'll have your first invoice sent in minutes.</p>
            <a href="{{ route('register') }}" class="btn btn-saffron btn-lg">
                Get started free
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

@endsection
