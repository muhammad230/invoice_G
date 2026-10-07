@extends('layouts.app')

@section('title', 'Dashboard — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Dashboard — InvoiceFlow
     Greeting + 4 stat cards + Create invoice CTA
     ========================================================== -->
<section class="dashboard-section py-5 py-lg-6">
    <div class="container">
        <!-- Header / Greeting ---------------------------------------- -->
        <div class="row align-items-center g-4 mb-5">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="avatar-ring" aria-hidden="true">
                        <span class="avatar-initials">{{ strtoupper(mb_substr($user->name, 0, 1) ?? 'U') }}</span>
                    </div>
                    <div>
                        <h1 class="h1 mb-0">
                            {{ $greeting }}, <span class="accent-text">{{ $user->name }}</span>
                        </h1>
                        <p class="ink-soft mb-0" style="font-size: 1rem; letter-spacing: 0;">
                            {{ $user->email }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('invoice.create') }}" class="btn btn-saffron btn-lg">
                    <i class="bi bi-plus-lg me-1"></i>
                    Create invoice
                </a>
            </div>
        </div>

        <!-- Stats grid ------------------------------------------------- -->
        <div class="row g-4 mb-5">
            <!-- Total invoices -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon stat-icon--total" aria-hidden="true">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <span class="badge rounded-pill bg-light ink-soft border">All</span>
                    </div>
                    <p class="stat-label mb-1">Total invoices</p>
                    <p class="stat-value mb-0">{{ number_format($stats['total']) }}</p>
                </div>
            </div>

            <!-- Paid -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon stat-icon--paid" aria-hidden="true">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <span class="badge rounded-pill status-paid">Paid</span>
                    </div>
                    <p class="stat-label mb-1">Paid</p>
                    <p class="stat-value mb-0">{{ number_format($stats['paid']) }}</p>
                </div>
            </div>

            <!-- Pending -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon stat-icon--pending" aria-hidden="true">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <span class="badge rounded-pill status-pending">Pending</span>
                    </div>
                    <p class="stat-label mb-1">Pending</p>
                    <p class="stat-value mb-0">{{ number_format($stats['pending']) }}</p>
                </div>
            </div>

            <!-- Overdue -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon stat-icon--overdue" aria-hidden="true">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <span class="badge rounded-pill status-overdue">Overdue</span>
                    </div>
                    <p class="stat-label mb-1">Overdue</p>
                    <p class="stat-value mb-0">{{ number_format($stats['overdue']) }}</p>
                </div>
            </div>
        </div>

        <!-- Empty-state welcome panel --------------------------------- -->
        <div class="card dashboard-empty">
            <div class="card-body">
                <div class="row align-items-center g-4">
                    <div class="col-md-8">
                        <h2 class="h3 mb-2">Welcome to InvoiceFlow</h2>
                        <p class="ink-soft mb-3 mb-md-4" style="max-width: 560px;">
                            You're all set up. Ready to send your first invoice? Build it in under a minute,
                            send it to your client, and track the payment status right here.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('invoice.create') }}" class="btn btn-saffron">
                                <i class="bi bi-file-earmark-plus me-1"></i>
                                Create your first invoice
                            </a>
                            <a href="{{ route('home') }}#how" class="btn btn-outline-ink">
                                <i class="bi bi-play-circle me-1"></i>
                                See how it works
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="floating-hero-icon" aria-hidden="true">
                            <i class="bi bi-send-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
