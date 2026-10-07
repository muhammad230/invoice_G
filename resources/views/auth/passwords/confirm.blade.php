@extends('layouts.app')

@section('title', 'Confirm password — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Confirm password — InvoiceFlow
     Shown before sensitive actions (e.g. changing email) to
     ensure the current user is the one at the keyboard.
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
                    <h1 class="auth-title mb-2">Confirm your password</h1>
                    <p class="auth-subtitle mb-0">
                        Please confirm your password before continuing.
                    </p>
                </div>

                <div class="card auth-card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <form method="POST" action="{{ route('password.confirm') }}" novalidate>
                            @csrf

                            <div class="mb-4">
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
                                           placeholder="••••••••"
                                           required
                                           autocomplete="current-password"
                                           autofocus>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Confirm password
                                </button>
                            </div>

                            @if (Route::has('password.request'))
                                <p class="text-center ink-soft mb-0">
                                    Can't remember?
                                    <a href="{{ route('password.request') }}" class="auth-link">
                                        Reset password
                                    </a>
                                </p>
                            @endif
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
