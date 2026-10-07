@extends('layouts.app')

@section('title', 'InvoiceFlow — Get paid on time. Every time.')

@section('content')

{{-- ============================================================
     2. HERO SECTION
     Left: copy + CTA buttons
     Right: tilted invoice card with PAID stamp + floating pending card
     ============================================================ --}}
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            {{-- LEFT: Hero Copy --}}
            <div class="col-lg-6 hero-text">
                <span class="section-label">
                    <i class="bi bi-briefcase me-1"></i>
                    Invoicing for freelancers
                </span>
                <h1>Get paid on time.<br>Every time.</h1>
                <p class="lead mt-4">
                    Create an invoice in minutes, send it in one click, and see exactly who has paid.
                </p>
                <div class="hero-cta">
                    <a href="{{ route('invoice.create') }}" class="btn btn-saffron">
                        Create invoice
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#how" class="btn btn-outline-ink">
                        See how it works
                    </a>
                </div>
                <p class="hero-note">
                    <i class="bi bi-shield-check"></i>
                    Free plan. No credit card needed.
                </p>
            </div>

            {{-- RIGHT: Invoice Visual --}}
            <div class="col-lg-6">
                <div class="invoice-visual">
                    {{-- Floating Pending Card --}}
                    <div class="pending-card" role="img" aria-label="Pending invoices: $1,200">
                        <div class="pending-label">Pending</div>
                        <div class="pending-amount">$1,200</div>
                    </div>

                    {{-- Main Invoice Card --}}
                    <div class="invoice-card">
                        {{-- Invoice Header --}}
                        <div class="invoice-header">
                            <div class="invoice-brand">
                                <span class="invoice-brand-name">InvoiceFlow</span>
                                <span class="invoice-brand-sub">hello@invoiceflow.app</span>
                            </div>
                            <div>
                                <div class="invoice-number">Invoice #INV-001</div>
                                <div class="invoice-date">Oct 7, {{ date('Y') }}</div>
                            </div>
                        </div>

                        {{-- Bill To --}}
                        <div class="invoice-bill-to">
                            <div class="invoice-label">Bill to</div>
                            <div class="invoice-client-name">Ahmed Khan</div>
                            <div class="invoice-client-email">ahmed@khanstudios.com</div>
                        </div>

                        {{-- Items --}}
                        <div class="invoice-items">
                            <div class="invoice-item">
                                <span class="invoice-item-name">Website design</span>
                                <span class="invoice-item-price">$400</span>
                            </div>
                            <div class="invoice-item">
                                <span class="invoice-item-name">Hosting setup</span>
                                <span class="invoice-item-price">$100</span>
                            </div>
                        </div>

                        {{-- Total --}}
                        <div class="invoice-total">
                            <span class="invoice-total-label">Total</span>
                            <span class="invoice-total-amount">$500</span>
                        </div>

                        {{-- PAID Stamp --}}
                        <div class="paid-stamp" aria-label="Marked as paid">
                            PAID
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     3. STATS STRIP
     3 social-proof numbers on a white card
     ============================================================ --}}
<section class="pt-0">
    <div class="container">
        <div class="stats-strip">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Invoices created</div>
                    </div>
                </div>
                <div class="col-12 col-md-4 border-start-0 border-md-start border-end-0 border-md-end" style="border-color: rgba(18,32,46,0.08);">
                    <div class="stat-item">
                        <div class="stat-number">2K+</div>
                        <div class="stat-label">Freelancers & teams</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Uptime guarantee</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     4. FEATURES (id="features")
     4 feature cards with Bootstrap Icons
     ============================================================ --}}
<section id="features">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">
                <i class="bi bi-stars me-1"></i>
                Everything you need
            </span>
            <h2 class="mb-3">Simple tools, powerful results</h2>
            <p class="mb-0" style="max-width: 540px; margin: 0 auto;">
                Built for the way you work — no clutter, no learning curve.
            </p>
        </div>

        <div class="row g-4">
            {{-- Feature 1 — Custom invoices --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon saffron">
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                    </div>
                    <h3>Custom invoices</h3>
                    <p>Design branded invoices with your logo, colors, and payment terms.</p>
                </div>
            </div>

            {{-- Feature 2 — Send in one click --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon ink">
                        <i class="bi bi-send-check" aria-hidden="true"></i>
                    </div>
                    <h3>Send in one click</h3>
                    <p>Email your invoice directly or share a secure payment link instantly.</p>
                </div>
            </div>

            {{-- Feature 3 — Track payments --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon sage">
                        <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>
                    </div>
                    <h3>Track payments</h3>
                    <p>See who's paid, who's late, and send gentle reminders automatically.</p>
                </div>
            </div>

            {{-- Feature 4 — Client list --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon overdue">
                        <i class="bi bi-people" aria-hidden="true"></i>
                    </div>
                    <h3>Client list</h3>
                    <p>Keep every client, contact, and billing history in one organized place.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     5. HOW IT WORKS (id="how")
     3 numbered steps
     ============================================================ --}}
<section id="how" style="background-color: rgba(18, 32, 46, 0.02);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">
                <i class="bi bi-123 me-1"></i>
                How it works
            </span>
            <h2 class="mb-3">Three steps. Zero headaches.</h2>
            <p class="mb-0" style="max-width: 540px; margin: 0 auto;">
                From sending to getting paid — the whole flow in under a minute.
            </p>
        </div>

        <div class="row g-4">
            {{-- Step 1 --}}
            <div class="col-12 col-lg-4">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-connector" aria-hidden="true"></div>
                    <h3>Add your client</h3>
                    <p>Save contact info once and reuse it on every future invoice.</p>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="col-12 col-lg-4">
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-connector" aria-hidden="true"></div>
                    <h3>Create the invoice</h3>
                    <p>Add line items, set due dates, and apply taxes in seconds.</p>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="col-12 col-lg-4">
                <div class="step-item">
                    <div class="step-number">3</div>
                    <h3>Get paid and mark it paid</h3>
                    <p>Accept payments online, record cash, and update status with one tap.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     6. PRICING PREVIEW (id="pricing")
     3 plans — Free, Pro (Most popular), Business
     ============================================================ --}}
<section id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">
                <i class="bi bi-tags me-1"></i>
                Pricing
            </span>
            <h2 class="mb-3">Choose the plan that fits</h2>
            <p class="mb-0" style="max-width: 540px; margin: 0 auto;">
                Start free and upgrade when you're ready. Cancel anytime.
            </p>
        </div>

        <div class="row g-4 align-items-stretch">
            {{-- Free Plan --}}
            <div class="col-12 col-md-4">
                <div class="pricing-card">
                    <div class="plan-name">Free</div>
                    <div class="plan-price">
                        <span class="plan-amount">$0</span>
                    </div>
                    <div class="plan-description">For freelancers just getting started.</div>
                    <ul class="plan-features">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            5 invoices per month
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Unlimited clients
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Email & share links
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Payment tracking
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-ink w-100 justify-content-center">
                        Get started
                    </a>
                </div>
            </div>

            {{-- Pro Plan — "Most popular" --}}
            <div class="col-12 col-md-4">
                <div class="pricing-card popular">
                    <div class="pricing-badge">Most popular</div>
                    <div class="plan-name">Pro</div>
                    <div class="plan-price">
                        <span class="plan-amount">$12</span>
                        <span class="plan-period">/ month</span>
                    </div>
                    <div class="plan-description">Everything growing businesses need.</div>
                    <ul class="plan-features">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Unlimited invoices
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            PDF export & downloads
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Custom branding & logos
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Automatic reminders
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Priority support
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-saffron w-100 justify-content-center">
                        Start Pro trial
                    </a>
                </div>
            </div>

            {{-- Business Plan --}}
            <div class="col-12 col-md-4">
                <div class="pricing-card">
                    <div class="plan-name">Business</div>
                    <div class="plan-price">
                        <span class="plan-amount">$29</span>
                        <span class="plan-period">/ month</span>
                    </div>
                    <div class="plan-description">For teams and agencies with big needs.</div>
                    <ul class="plan-features">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Everything in Pro
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Multiple team users
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Advanced reports & analytics
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Role-based permissions
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Dedicated account manager
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-ink w-100 justify-content-center">
                        Contact sales
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     7. FINAL CALL TO ACTION
     Dark ink background + saffron button
     ============================================================ --}}
<section>
    <div class="container">
        <div class="cta-section">
            <h2>Send your first invoice today</h2>
            <p>Join thousands of freelancers and small businesses already getting paid faster with InvoiceFlow.</p>
            <a href="{{ route('invoice.create') }}" class="btn btn-saffron btn-lg">
                Get started free
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

@endsection
