@extends('layouts.app')

@section('title', 'Login — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Login — InvoiceFlow
     Unique split-screen layout: brand panel + focused form.
     Scoped under .lf-* so no other page is affected.
     ========================================================== -->
@include('auth.partials.split-styles')

<section class="lf-auth">
    <div class="container">
        <div class="lf-auth__shell">
            <div class="lf-auth__card">

                <!-- Left / brand panel --------------------------------- -->
                <aside class="lf-panel">
                    <div>
                        <a href="{{ route('home') }}" class="lf-brand" aria-label="InvoiceFlow">
                            <span class="lf-brand__icon" aria-hidden="true">
                                <i class="bi bi-check2"></i>
                            </span>
                            InvoiceFlow
                        </a>

                        <h2 class="lf-panel__headline">
                            Get paid faster, <span>every time.</span>
                        </h2>
                        <p class="lf-panel__text">
                            The calm, paper-inspired workspace for invoices, clients, and payments.
                        </p>

                        <ul class="lf-panel__list">
                            <li><i class="bi bi-check-circle-fill"></i> Track invoices &amp; payments</li>
                            <li><i class="bi bi-check-circle-fill"></i> Send polished estimates</li>
                            <li><i class="bi bi-check-circle-fill"></i> Built for freelancers</li>
                        </ul>
                    </div>

                    <div class="lf-panel__foot">
                        <i class="bi bi-shield-lock-fill"></i>
                        Protected by 256-bit HTTPS encryption
                    </div>
                </aside>

                <!-- Right / form panel -------------------------------- -->
                <div class="lf-form">
                    <p class="lf-form__eyebrow">Welcome back</p>
                    <h1 class="lf-form__title">Sign in to your account</h1>
                    <p class="lf-form__subtitle">
                        Enter your details to continue to your dashboard.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success py-2 px-3 mb-3" role="alert">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ session('status') }}
                        </div>
                    @endif

                    @error('login')
                        <div class="alert alert-danger py-2 px-3 mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            {{ $message }}
                        </div>
                    @enderror

                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <div class="lf-field @error('email') is-invalid @enderror">
                                <span class="lf-field__icon" aria-hidden="true">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="lf-field__input"
                                       placeholder="you@freelance.com"
                                       required
                                       autocomplete="email"
                                       autofocus>
                            </div>
                            @error('email')
                                <div class="lf-error">
                                    <i class="bi bi-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">
                                    Password <span class="text-danger">*</span>
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="lf-forgot mb-1">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <div class="lf-field @error('password') is-invalid @enderror">
                                <span class="lf-field__icon" aria-hidden="true">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input id="password"
                                       type="password"
                                       name="password"
                                       class="lf-field__input"
                                       placeholder="Enter your password"
                                       required
                                       autocomplete="current-password">
                                <button type="button" class="lf-field__toggle" data-toggle-password="password" aria-label="Show password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="lf-error">
                                    <i class="bi bi-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Remember me -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check lf-remember">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="remember"
                                       id="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-saffron lf-submit">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Sign in
                        </button>
                    </form>

                    <div class="lf-divider">or</div>

                    <p class="lf-register">
                        Don't have an account?
                        <a href="{{ route('register') }}">Create a free account</a>
                    </p>

                    <p class="lf-trust">
                        <i class="bi bi-shield-check"></i>
                        No credit card required to start
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-toggle-password'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            var icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-eye', !show);
                icon.classList.toggle('bi-eye-slash', show);
            }
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });
</script>
@endsection
