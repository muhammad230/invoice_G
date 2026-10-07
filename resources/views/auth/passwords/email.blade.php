@extends('layouts.app')

@section('title', 'Forgot password — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Forgot password — InvoiceFlow
     Step 1: enter your email and receive a reset link.
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
                    <h1 class="auth-title mb-2">Reset your password</h1>
                    <p class="auth-subtitle mb-0">
                        Enter your email and we'll send you a link to create a new password.
                    </p>
                </div>

                <div class="card auth-card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        @if (session('status'))
                            <div class="alert alert-success mb-4" role="alert">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" novalidate>
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text" aria-hidden="true">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input id="email"
                                           type="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           placeholder="you@freelance.com"
                                           required
                                           autocomplete="email"
                                           autofocus>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-send me-1"></i>
                                    Send password reset link
                                </button>
                            </div>

                            <p class="text-center ink-soft mb-0">
                                Remembered it?
                                <a href="{{ route('login') }}" class="auth-link">Back to sign in</a>
                            </p>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
