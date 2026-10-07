@extends('layouts.app')

@section('title', 'Create your account — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Register — InvoiceFlow
     Guest-only: name / email / password / confirm.
     Bootstrap field-level invalid-feedback for every error.
     ========================================================== -->
<section class="auth-section py-5 py-lg-6">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">

                <!-- Header / logo ----------------------------------------- -->
                <div class="text-center mb-4 mb-lg-5">
                    <a href="{{ route('home') }}" class="auth-brand mb-3" aria-label="InvoiceFlow">
                        <span class="logo-icon" aria-hidden="true" style="width: 52px; height: 52px; font-size: 1.5rem;">
                            <i class="bi bi-check2"></i>
                        </span>
                    </a>
                    <h1 class="auth-title mb-2">Create your account</h1>
                    <p class="auth-subtitle mb-0">
                        Start for free. No credit card needed. Upgrade when you need more.
                    </p>
                </div>

                <!-- Card / form -------------------------------------------- -->
                <div class="card auth-card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <form method="POST" action="{{ route('register') }}" novalidate>
                            @csrf

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Full name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text" aria-hidden="true">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input id="name"
                                           type="text"
                                           name="name"
                                           value="{{ old('name') }}"
                                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                                           placeholder="Ahmed Khan"
                                           required
                                           autocomplete="name"
                                           autofocus>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
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
                                           autocomplete="email">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text" aria-hidden="true">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input id="password"
                                           type="password"
                                           name="password"
                                           class="form-control form-control-lg @error('password') is-invalid @enderror"
                                           placeholder="Minimum 8 characters"
                                           required
                                           autocomplete="new-password">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @else
                                    <div class="form-text mt-1">
                                        Use 8+ characters for a strong password.
                                    </div>
                                @enderror
                            </div>

                            <!-- Confirm password -->
                            <div class="mb-4">
                                <label for="password-confirm" class="form-label">
                                    Confirm password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text" aria-hidden="true">
                                        <i class="bi bi-shield-check"></i>
                                    </span>
                                    <input id="password-confirm"
                                           type="password"
                                           name="password_confirmation"
                                           class="form-control form-control-lg"
                                           placeholder="Repeat the password"
                                           required
                                           autocomplete="new-password">
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-person-plus me-1"></i>
                                    Create account
                                </button>
                            </div>

                            <!-- Login link -->
                            <p class="text-center ink-soft mb-0">
                                Already have an account?
                                <a href="{{ route('login') }}" class="auth-link">
                                    Sign in
                                </a>
                            </p>
                        </form>
                    </div>
                </div>

                <p class="text-center ink-soft mt-4" style="font-size: 0.875rem;">
                    By signing up you agree to our Terms and Privacy Policy.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
