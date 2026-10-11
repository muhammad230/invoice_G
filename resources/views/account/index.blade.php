@extends('layouts.dashboard')

@section('title', 'Account — InvoiceFlow')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        {{-- Page header --------------------------------------------------------- --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-gear me-1"></i>
                    Settings
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">
                    Account
                </h1>
                <p class="ink-soft mb-0 mt-1">
                    Manage your personal details, password and account data.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-ink">
                <i class="bi bi-arrow-left me-1"></i> Back to dashboard
            </a>
        </div>

        <div class="row g-4 g-lg-5">
            {{-- Left: Settings navigation (sub-menu) --------------------------- --}}
            <div class="col-lg-3">
                @include('settings.partials.nav', ['active' => 'account'])
            </div>

            {{-- Right: Account cards ------------------------------------------- --}}
            <div class="col-lg-9">
                {{-- a) Profile -------------------------------------------------- --}}
                <div class="card mb-4"
                     style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="mb-1" style="font-size: 1.25rem;">
                            <i class="bi bi-person-lines-fill me-2" style="color: var(--saffron);"></i>
                            Profile
                        </h2>
                        <p class="ink-soft mb-4" style="font-size: 0.9rem;">
                            Your name and sign-in email address.
                        </p>

                        <form method="POST"
                              action="{{ route('account.profile.update') }}"
                              id="profileForm"
                              data-original-email="{{ $user->email }}"
                              novalidate>
                            @csrf
                            @method('PUT')

                            <div class="row g-3 g-lg-4">
                                <div class="col-12">
                                    <label for="name" class="form-label">
                                        Name <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="text" id="name" name="name"
                                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}"
                                           maxlength="100" required autocomplete="name">
                                    @error('name')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label">
                                        Email <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="email" id="email" name="email"
                                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}"
                                           maxlength="255" required autocomplete="email">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- Revealed only when the email is changed. --}}
                                <div class="col-12 d-none" id="currentPasswordWrap">
                                    <label for="profile_current_password" class="form-label">
                                        Current password <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="password" id="profile_current_password"
                                           name="current_password"
                                           class="form-control form-control-lg @error('current_password') is-invalid @enderror"
                                           autocomplete="current-password">
                                    <div class="form-text mt-1">
                                        Enter your current password to confirm the email change.
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-check2 me-1"></i> Save profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- b) Change password ------------------------------------------ --}}
                <div class="card mb-4"
                     style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="mb-1" style="font-size: 1.25rem;">
                            <i class="bi bi-shield-lock me-2" style="color: var(--sage);"></i>
                            Change password
                        </h2>
                        <p class="ink-soft mb-4" style="font-size: 0.9rem;">
                            Choose a new password of at least 8 characters.
                        </p>

                        <form method="POST" action="{{ route('account.password.update') }}" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="row g-3 g-lg-4">
                                <div class="col-12">
                                    <label for="password_current" class="form-label">
                                        Current password <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="password" id="password_current" name="current_password"
                                           class="form-control form-control-lg @error('current_password') is-invalid @enderror"
                                           autocomplete="current-password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_new" class="form-label">
                                        New password <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="password" id="password_new" name="password"
                                           class="form-control form-control-lg @error('password') is-invalid @enderror"
                                           placeholder="Minimum 8 characters"
                                           autocomplete="new-password" required>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_new_confirmation" class="form-label">
                                        Confirm new password <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="password" id="password_new_confirmation"
                                           name="password_confirmation"
                                           class="form-control form-control-lg"
                                           autocomplete="new-password" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-key me-1"></i> Change password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- c) Danger zone ---------------------------------------------- --}}
                <div class="card mb-4"
                     style="border-radius: var(--radius); border: 1px solid rgba(179, 55, 47, 0.28); box-shadow: var(--shadow-sm);">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="mb-1" style="font-size: 1.25rem; color: var(--overdue);">
                            <i class="bi bi-exclamation-octagon me-2"></i>
                            Danger zone
                        </h2>
                        <p class="ink-soft mb-4" style="font-size: 0.9rem;">
                            Deleting your account permanently removes your profile,
                            clients, invoices, payments and services. This cannot be undone.
                        </p>

                        <button type="button"
                                class="btn btn-outline-danger btn-lg"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteAccountModal">
                            <i class="bi bi-trash3 me-1"></i> Delete account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Delete-account confirmation modal -------------------------------------- --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1"
     aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius); border: none;">
            <form method="POST" action="{{ route('account.destroy') }}" novalidate>
                @csrf
                @method('DELETE')

                <div class="modal-header" style="border-bottom: 1px solid rgba(179, 55, 47, 0.2);">
                    <h2 class="modal-title h5" id="deleteAccountModalLabel"
                        style="color: var(--overdue);">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Delete your account?
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="ink-soft" style="font-size: 0.9rem;">
                        This will permanently delete your account and all of your data.
                        This action <strong>cannot be undone</strong>.
                    </p>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label">
                            Password <span style="color: var(--overdue);">*</span>
                        </label>
                        <input type="password" id="delete_password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        @error('password')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input class="form-check-input @error('confirm') is-invalid @enderror"
                               type="checkbox" id="delete_confirm" name="confirm" value="1"
                               {{ old('confirm') ? 'checked' : '' }} required>
                        <label class="form-check-label" for="delete_confirm" style="font-size: 0.9rem;">
                            I understand this is permanent and want to delete my account.
                        </label>
                        @error('confirm')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i> Permanently delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    // Show the "current password" field only when the email actually changes.
    var form  = document.getElementById('profileForm');
    var email = document.getElementById('email');
    var wrap  = document.getElementById('currentPasswordWrap');
    var input = document.getElementById('profile_current_password');

    if (!form || !email || !wrap || !input) return;

    var original = (form.getAttribute('data-original-email') || '').trim().toLowerCase();

    function sync() {
        var changed = email.value.trim().toLowerCase() !== original;
        wrap.classList.toggle('d-none', !changed);
        input.required = changed;
        if (!changed) {
            input.value = '';
        }
    }

    email.addEventListener('input', sync);
    sync();

    // Re-open the delete modal automatically if its validation failed.
    @if ($errors->has('password') && !$errors->has('name') && !$errors->has('email') && !$errors->has('current_password'))
        var modalEl = document.getElementById('deleteAccountModal');
        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    @endif
})();
</script>
@endsection