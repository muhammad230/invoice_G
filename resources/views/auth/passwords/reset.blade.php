@extends('layouts.app')

@section('title', 'Set a new password — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Password Reset — InvoiceFlow
     Step 2: choose a new password using the reset token from email.
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
                            Almost there — <span>pick a new password.</span>
                        </h2>
                        <p class="lf-panel__text">
                            Choose something strong and unique. You'll be back on track in no time.
                        </p>

                        <ul class="lf-panel__list">
                            <li><i class="bi bi-check-circle-fill"></i> At least 8 characters</li>
                            <li><i class="bi bi-check-circle-fill"></i> Stored fully encrypted</li>
                            <li><i class="bi bi-check-circle-fill"></i> Signs you straight back in</li>
                        </ul>
                    </div>

                    <div class="lf-panel__foot">
                        <i class="bi bi-shield-lock-fill"></i>
                        Protected by 256-bit HTTPS encryption
                    </div>
                </aside>

                <!-- Right / form panel -------------------------------- -->
                <div class="lf-form">
                    <p class="lf-form__eyebrow">Password help</p>
                    <h1 class="lf-form__title">Set a new password</h1>
                    <p class="lf-form__subtitle">
                        Pick a strong password and you'll be back on track.
                    </p>

                    <form method="POST" action="{{ route('password.update') }}" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <!-- Email (pre-filled from reset link) -->
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
                                       value="{{ $email ?? old('email') }}"
                                       class="lf-field__input"
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

                        <!-- New password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                New password <span class="text-danger">*</span>
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
                            @enderror
                        </div>

                        <!-- Confirm -->
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
                            <i class="bi bi-unlock"></i>
                            Reset password
                        </button>
                    </form>

                    <div class="lf-divider">or</div>

                    <p class="lf-register">
                        Done?
                        <a href="{{ route('login') }}">Sign in</a>
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
