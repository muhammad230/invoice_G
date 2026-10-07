@extends('layouts.app')

@section('title', 'Verify your email — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Email Verification — InvoiceFlow
     Shown right after registration when MustVerifyEmail is active.
     ========================================================== -->
<section class="auth-section py-5 py-lg-6">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">

                <div class="text-center mb-4 mb-lg-5">
                    <a href="{{ route('home') }}" class="auth-brand mb-3" aria-label="InvoiceFlow">
                        <span class="logo-icon" aria-hidden="true" style="width: 52px; height: 52px; font-size: 1.5rem;">
                            <i class="bi bi-check2"></i>
                        </span>
                    </a>
                    <h1 class="auth-title mb-2">Verify your email</h1>
                    <p class="auth-subtitle mb-0">
                        Before you continue, please confirm your email address.
                    </p>
                </div>

                <div class="card auth-card shadow-sm">
                    <div class="card-body p-4 p-lg-5 text-center">
                        <div class="mx-auto mb-4" style="width: 72px; height: 72px; border-radius: 18px; background: color-mix(in srgb, var(--saffron) 18%, #fff); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-envelope-check ink" aria-hidden="true" style="font-size: 2rem;"></i>
                        </div>

                        @if (session('resent'))
                            <div class="alert alert-success mb-4" role="alert">
                                <i class="bi bi-check-circle me-1"></i>
                                A fresh verification link has been sent to your email address.
                            </div>
                        @endif

                        <p class="ink-soft mb-4">
                            If you did not receive the email at
                            <strong>{{ Auth::user()->email }}</strong>,
                            click the button below to request another.
                        </p>

                        <form method="POST" action="{{ route('verification.resend') }}" class="d-inline">
                            @csrf
                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-send me-1"></i>
                                    Resend verification email
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-ink">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
