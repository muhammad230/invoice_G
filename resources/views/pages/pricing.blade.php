@extends('layouts.app')

@section('title', 'Pricing — InvoiceFlow')
@section('meta_description', 'Simple pricing for InvoiceFlow. Start free, then choose Pro ($12/month) or Business ($29/month). Yearly billing gets you 2 months free.')

@section('content')

{{-- ============================================================
     HERO
     ============================================================ --}}
<header class="page-hero">
    <div class="container">
        <span class="section-label">
            <i class="bi bi-tags me-1"></i>
            Pricing
        </span>
        <h1>Simple, honest pricing</h1>
        <p>
            Start free and upgrade when you're ready. Switch or cancel anytime.
        </p>

        {{-- Monthly / yearly toggle. Yearly = 2 months free. --}}
        <div class="mt-4">
            <div class="pricing-toggle" role="group" aria-label="Billing period">
                <button type="button" id="toggleMonthly" class="active" aria-pressed="true">
                    Monthly
                </button>
                <button type="button" id="toggleYearly" aria-pressed="false">
                    Yearly
                    <span class="save-badge">2 months free</span>
                </button>
            </div>
        </div>
    </div>
</header>

{{-- ============================================================
     PLANS
     ============================================================ --}}
<section class="pt-0">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            {{-- Free --}}
            <div class="col-12 col-md-4">
                <div class="pricing-card">
                    <div class="plan-name">Free</div>
                    <div class="plan-price">
                        <span class="plan-amount" data-monthly="0" data-yearly="0">$0</span>
                        <span class="plan-period" data-monthly="/ month" data-yearly="/ month">/ month</span>
                    </div>
                    <div class="plan-description">For freelancers just getting started.</div>
                    <ul class="plan-features">
                        <li><i class="bi bi-check-circle-fill"></i> 5 invoices per month</li>
                        <li><i class="bi bi-check-circle-fill"></i> 1 invoice template</li>
                        <li><i class="bi bi-check-circle-fill"></i> Basic reports</li>
                        <li><i class="bi bi-check-circle-fill"></i> Unlimited clients</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-ink w-100 justify-content-center">
                        Get started free
                    </a>
                </div>
            </div>

            {{-- Pro — most popular --}}
            <div class="col-12 col-md-4">
                <div class="pricing-card popular">
                    <div class="pricing-badge">Most popular</div>
                    <div class="plan-name">Pro</div>
                    <div class="plan-price">
                        <span class="plan-amount" data-monthly="12" data-yearly="10">$12</span>
                        <span class="plan-period" data-monthly="/ month" data-yearly="/ month, billed yearly">/ month</span>
                    </div>
                    <div class="plan-description">Everything a growing business needs.</div>
                    <ul class="plan-features">
                        <li><i class="bi bi-check-circle-fill"></i> Unlimited invoices</li>
                        <li><i class="bi bi-check-circle-fill"></i> Unlimited clients</li>
                        <li><i class="bi bi-check-circle-fill"></i> PDF export &amp; downloads</li>
                        <li><i class="bi bi-check-circle-fill"></i> Custom branding &amp; logos</li>
                        <li><i class="bi bi-check-circle-fill"></i> Full reports</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-saffron w-100 justify-content-center">
                        Start Pro
                    </a>
                </div>
            </div>

            {{-- Business --}}
            <div class="col-12 col-md-4">
                <div class="pricing-card">
                    <div class="plan-name">Business</div>
                    <div class="plan-price">
                        <span class="plan-amount" data-monthly="29" data-yearly="24">$29</span>
                        <span class="plan-period" data-monthly="/ month" data-yearly="/ month, billed yearly">/ month</span>
                    </div>
                    <div class="plan-description">For teams and agencies with bigger needs.</div>
                    <ul class="plan-features">
                        <li><i class="bi bi-check-circle-fill"></i> Everything in Pro</li>
                        <li><i class="bi bi-check-circle-fill"></i> Multiple users</li>
                        <li><i class="bi bi-check-circle-fill"></i> Advanced reports</li>
                        <li><i class="bi bi-check-circle-fill"></i> Priority support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-ink w-100 justify-content-center">
                        Get Business
                    </a>
                </div>
            </div>
        </div>

        {{-- Small honesty note --}}
        <p class="text-center ink-soft mt-4 mb-0" style="font-size: 0.9rem;">
            <i class="bi bi-info-circle me-1"></i>
            Paid plans are coming soon.
        </p>
    </div>
</section>

{{-- ============================================================
     COMPARISON TABLE
     ============================================================ --}}
<section class="pt-0">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3">Compare plans</h2>
            <p class="mb-0">A quick look at what's included in each plan.</p>
        </div>

        <div class="comparison-table table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Feature</th>
                        <th scope="col" class="text-center">Free</th>
                        <th scope="col" class="text-center">Pro</th>
                        <th scope="col" class="text-center">Business</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">Invoices per month</th>
                        <td class="text-center">5</td>
                        <td class="text-center">Unlimited</td>
                        <td class="text-center">Unlimited</td>
                    </tr>
                    <tr>
                        <th scope="row">Clients</th>
                        <td class="text-center">Unlimited</td>
                        <td class="text-center">Unlimited</td>
                        <td class="text-center">Unlimited</td>
                    </tr>
                    <tr>
                        <th scope="row">PDF export</th>
                        <td class="text-center"><i class="bi bi-x-lg text-secondary" aria-label="Not included"></i></td>
                        <td class="text-center"><i class="bi bi-check-lg" style="color: var(--sage);" aria-label="Included"></i></td>
                        <td class="text-center"><i class="bi bi-check-lg" style="color: var(--sage);" aria-label="Included"></i></td>
                    </tr>
                    <tr>
                        <th scope="row">Custom branding</th>
                        <td class="text-center"><i class="bi bi-x-lg text-secondary" aria-label="Not included"></i></td>
                        <td class="text-center"><i class="bi bi-check-lg" style="color: var(--sage);" aria-label="Included"></i></td>
                        <td class="text-center"><i class="bi bi-check-lg" style="color: var(--sage);" aria-label="Included"></i></td>
                    </tr>
                    <tr>
                        <th scope="row">Reports</th>
                        <td class="text-center">Basic</td>
                        <td class="text-center">Full</td>
                        <td class="text-center">Advanced</td>
                    </tr>
                    <tr>
                        <th scope="row">Multiple users</th>
                        <td class="text-center"><i class="bi bi-x-lg text-secondary" aria-label="Not included"></i></td>
                        <td class="text-center"><i class="bi bi-x-lg text-secondary" aria-label="Not included"></i></td>
                        <td class="text-center"><i class="bi bi-check-lg" style="color: var(--sage);" aria-label="Included"></i></td>
                    </tr>
                    <tr>
                        <th scope="row">Support</th>
                        <td class="text-center">Community</td>
                        <td class="text-center">Priority</td>
                        <td class="text-center">Dedicated</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ============================================================
     PRICING FAQS
     ============================================================ --}}
<section class="pt-0">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3">Pricing questions</h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion faq-accordion" id="pricingFaq">
                    {{-- FAQ 1 --}}
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="pf1h">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pf1" aria-expanded="true" aria-controls="pf1">
                                Is there a free plan?
                            </button>
                        </h3>
                        <div id="pf1" class="accordion-collapse collapse show" aria-labelledby="pf1h" data-bs-parent="#pricingFaq">
                            <div class="accordion-body">
                                Yes. The Free plan lets you create up to 5 invoices per month with
                                unlimited clients, and it never expires. No credit card required.
                            </div>
                        </div>
                    </div>

                    {{-- FAQ 2 --}}
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="pf2h">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf2" aria-expanded="false" aria-controls="pf2">
                                How does yearly billing work?
                            </button>
                        </h3>
                        <div id="pf2" class="accordion-collapse collapse" aria-labelledby="pf2h" data-bs-parent="#pricingFaq">
                            <div class="accordion-body">
                                Pay for a year up front and get 2 months free — that's 12 months for
                                the price of 10. The monthly equivalent shown on the yearly toggle
                                reflects the discount.
                            </div>
                        </div>
                    </div>

                    {{-- FAQ 3 --}}
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="pf3h">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf3" aria-expanded="false" aria-controls="pf3">
                                When can I actually pay for a plan?
                            </button>
                        </h3>
                        <div id="pf3" class="accordion-collapse collapse" aria-labelledby="pf3h" data-bs-parent="#pricingFaq">
                            <div class="accordion-body">
                                Paid plans are coming soon. For now every account runs on the Free
                                plan, and the buttons above simply create your account.
                            </div>
                        </div>
                    </div>

                    {{-- FAQ 4 --}}
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="pf4h">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf4" aria-expanded="false" aria-controls="pf4">
                                Can I change plans later?
                            </button>
                        </h3>
                        <div id="pf4" class="accordion-collapse collapse" aria-labelledby="pf4h" data-bs-parent="#pricingFaq">
                            <div class="accordion-body">
                                Yes. You'll be able to move between Free, Pro and Business at any time
                                once paid plans launch. Your data stays exactly where it is.
                            </div>
                        </div>
                    </div>
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

        var monthly = document.getElementById('toggleMonthly');
        var yearly  = document.getElementById('toggleYearly');
        if (!monthly || !yearly) return;

        // Swap each price/period between its monthly and yearly values.
        function apply(mode) {
            var amounts = document.querySelectorAll('.plan-amount');
            var periods = document.querySelectorAll('.plan-period');

            amounts.forEach(function (el) {
                var value = el.getAttribute('data-' + mode);
                if (value !== null) el.textContent = '$' + value;
            });
            periods.forEach(function (el) {
                var value = el.getAttribute('data-' + mode);
                if (value !== null) el.textContent = value;
            });
        }

        function select(mode) {
            var isMonthly = mode === 'monthly';
            monthly.classList.toggle('active', isMonthly);
            yearly.classList.toggle('active', !isMonthly);
            monthly.setAttribute('aria-pressed', isMonthly ? 'true' : 'false');
            yearly.setAttribute('aria-pressed', isMonthly ? 'false' : 'true');
            apply(mode);
        }

        monthly.addEventListener('click', function () { select('monthly'); });
        yearly.addEventListener('click', function () { select('yearly'); });
    })();
</script>
@endsection
