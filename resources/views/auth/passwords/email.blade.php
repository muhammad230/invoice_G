@extends('layouts.app')

@section('title', 'Forgot password — InvoiceFlow')

@section('content')
<!-- ==========================================================
     Forgot password — InvoiceFlow
     Step 1: enter your email and receive a reset link.
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
                            Forgot it? <span>We've got you.</span>
                        </h2>
                        <p class="lf-panel__text">
                            We'll email you a secure link to choose a brand-new password.
                        </p>

                        <ul class="lf-panel__list">
                            <li><i class="bi bi-check-circle-fill"></i> Secure, time-limited link</li>
                            <li><i class="bi bi-check-circle-fill"></i> Never shared with anyone</li>
                            <li><i class="bi bi-check-circle-fill"></i> Back to work in a minute</li>
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
                    <h1 class="lf-form__title">Reset your password</h1>
                    <p class="lf-form__subtitle">
                        Enter your email and we'll send you a link to create a new password.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success py-2 px-3 mb-3" role="alert">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" novalidate>
                        @csrf

                        <!-- Email -->
                        <div class="mb-4">
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

                        <!-- Submit -->
                        <button type="submit" class="btn btn-saffron lf-submit">
                            <i class="bi bi-send"></i>
                            Send password reset link
                        </button>
                    </form>

                    <div class="lf-divider">or</div>

                    <p class="lf-register">
                        Remembered it?
                        <a href="{{ route('login') }}">Back to sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
