@extends('layouts.app')

@section('title', 'Create your account — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Register — InvoiceFlow
     Guest-only: name / email / password / confirm.
     Matches the login split-screen layout (.lf-*).
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
                            Start free, <span>get paid faster.</span>
                        </h2>
                        <p class="lf-panel__text">
                            Create your account and start sending polished invoices in minutes.
                        </p>

                        <ul class="lf-panel__list">
                            <li><i class="bi bi-check-circle-fill"></i> Unlimited clients &amp; invoices</li>
                            <li><i class="bi bi-check-circle-fill"></i> Track payments &amp; reports</li>
                            <li><i class="bi bi-check-circle-fill"></i> No credit card required</li>
                        </ul>
                    </div>

                    <div class="lf-panel__foot">
                        <i class="bi bi-shield-lock-fill"></i>
                        Protected by 256-bit HTTPS encryption
                    </div>
                </aside>

                <!-- Right / form panel -------------------------------- -->
                <div class="lf-form">
                    <p class="lf-form__eyebrow">Get started</p>
                    <h1 class="lf-form__title">Create your account</h1>
                    <p class="lf-form__subtitle">
                        Start for free. No credit card needed. Upgrade when you need more.
                    </p>

                    <form method="POST" action="{{ route('register') }}" novalidate>
                        @csrf

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Full name <span class="text-danger">*</span>
                            </label>
                            <div class="lf-field @error('name') is-invalid @enderror">
                                <span class="lf-field__icon" aria-hidden="true">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input id="name"
                                       type="text"
                                       name="name"
                                       value="{{ old('name') }}"
                                       class="lf-field__input"
                                       placeholder="Ahmed Khan"
                                       required
                                       autocomplete="name"
                                       autofocus>
                            </div>
                            @error('name')
                                <div class="lf-error">
                                    <i class="bi bi-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

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
                                       autocomplete="email">
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
                            <label for="password" class="form-label">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="lf-field @error('password') is-invalid @enderror">
                                <span class="lf-field__icon" aria-hidden="true">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input id="password"
                                       type="password"
                                       name="password"
                                       class="lf-field__input"
                                       placeholder="Minimum 8 characters"
                                       required
                                       autocomplete="new-password">
                                <button type="button" class="lf-field__toggle" data-toggle-password="password" aria-label="Show password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="lf-error">
                                    <i class="bi bi-exclamation-circle"></i>
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
                            <div class="lf-field">
                                <span class="lf-field__icon" aria-hidden="true">
                                    <i class="bi bi-shield-check"></i>
                                </span>
                                <input id="password-confirm"
                                       type="password"
                                       name="password_confirmation"
                                       class="lf-field__input"
                                       placeholder="Repeat the password"
                                       required
                                       autocomplete="new-password">
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-saffron lf-submit">
                            <i class="bi bi-person-plus"></i>
                            Create account
                        </button>
                    </form>

                    <div class="lf-divider">or</div>

                    <p class="lf-register">
                        Already have an account?
                        <a href="{{ route('login') }}">Sign in</a>
                    </p>

                    <p class="lf-trust">
                        <i class="bi bi-shield-check"></i>
                        By signing up you agree to our Terms &amp; Privacy Policy
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
