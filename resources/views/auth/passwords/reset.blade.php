@extends('layouts.app')

@section('title', 'Set a new password — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Password Reset — InvoiceFlow
     Step 2: choose a new password using the reset token from email.
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
                    <h1 class="auth-title mb-2">Set a new password</h1>
                    <p class="auth-subtitle mb-0">
                        Pick a strong password and you'll be back on track.
                    </p>
                </div>

                <div class="card auth-card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <form method="POST" action="{{ route('password.update') }}" novalidate>
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <!-- Email (pre-filled from reset link) -->
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
                                           value="{{ $email ?? old('email') }}"
                                           class="form-control form-control-lg @error('email') is-invalid @enderror"
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

                            <!-- New password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    New password <span class="text-danger">*</span>
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
                                @enderror
                            </div>

                            <!-- Confirm -->
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

                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-unlock me-1"></i>
                                    Reset password
                                </button>
                            </div>

                            <p class="text-center ink-soft mb-0">
                                Done?
                                <a href="{{ route('login') }}" class="auth-link">Sign in</a>
                            </p>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
