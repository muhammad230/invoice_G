@extends('layouts.dashboard')

@section('title', 'Settings — Business Profile · InvoiceFlow')

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
                    Business Profile
                </h1>
                <p class="ink-soft mb-0 mt-1">
                    This information is used to prefill the &ldquo;Your business&rdquo;
                    section on every new invoice.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-ink">
                <i class="bi bi-arrow-left me-1"></i> Back to dashboard
            </a>
        </div>

        @include('partials.alerts')

        <div class="row g-4 g-lg-5">
            {{-- Left: Settings navigation (sub-menu) --------------------------- --}}
            <div class="col-lg-3">
                <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('settings.business-profile.edit') }}"
                               class="list-group-item list-group-item-action d-flex align-items-center active"
                               style="border: none; border-radius: 0;">
                                <i class="bi bi-building me-2"></i>
                                Business Profile
                            </a>
                            <span class="list-group-item d-flex align-items-center ink-soft"
                                  style="border: none; background: transparent; cursor: not-allowed; opacity: 0.55;">
                                <i class="bi bi-person-gear me-2"></i>
                                Account
                                <span class="ms-auto badge bg-light ink-soft">Soon</span>
                            </span>
                            <span class="list-group-item d-flex align-items-center ink-soft"
                                  style="border: none; background: transparent; cursor: not-allowed; opacity: 0.55;">
                                <i class="bi bi-receipt me-2"></i>
                                Invoice defaults
                                <span class="ms-auto badge bg-light ink-soft">Soon</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Business Profile form ------------------------------------ --}}
            <div class="col-lg-9">
                <form method="POST"
                      action="{{ route('settings.business-profile.update') }}"
                      enctype="multipart/form-data"
                      class="needs-validation"
                      novalidate>
                    @csrf
                    @method('PUT')

                    <div class="card mb-4"
                         style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="mb-4" style="font-size: 1.25rem;">
                                <i class="bi bi-building me-2" style="color: var(--saffron);"></i>
                                Business information
                            </h2>

                            <div class="row g-3 g-lg-4">
                                <div class="col-12">
                                    <label for="business_name" class="form-label">
                                        Business name <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="text" id="business_name" name="business_name"
                                           class="form-control form-control-lg @error('business_name') is-invalid @enderror"
                                           placeholder="Your Studio Inc."
                                           value="{{ old('business_name', $profile->business_name ?? '') }}"
                                           required
                                           maxlength="255">
                                    @error('business_name')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           placeholder="hello@yourstudio.com"
                                           value="{{ old('email', $profile->email ?? '') }}"
                                           maxlength="255">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" id="phone" name="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           placeholder="+1 (555) 000-0000"
                                           value="{{ old('phone', $profile->phone ?? '') }}"
                                           maxlength="50">
                                    @error('phone')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="text" id="website" name="website"
                                           class="form-control @error('website') is-invalid @enderror"
                                           placeholder="https://yourstudio.com"
                                           value="{{ old('website', $profile->website ?? '') }}"
                                           maxlength="255">
                                    @error('website')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="tax_number" class="form-label">Tax / VAT number</label>
                                    <input type="text" id="tax_number" name="tax_number"
                                           class="form-control @error('tax_number') is-invalid @enderror"
                                           placeholder="e.g. US12345678"
                                           value="{{ old('tax_number', $profile->tax_number ?? '') }}"
                                           maxlength="100">
                                    @error('tax_number')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea id="address" name="address"
                                              rows="3"
                                              class="form-control @error('address') is-invalid @enderror"
                                              placeholder="123 Main Street, City, Country"
                                              maxlength="500">{{ old('address', $profile->address ?? '') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Logo upload ----------------------------------------------------- --}}
                    <div class="card mb-4"
                         style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="mb-4" style="font-size: 1.25rem;">
                                <i class="bi bi-image me-2" style="color: var(--sage);"></i>
                                Logo
                            </h2>

                            <div class="row g-4 align-items-center">
                                <div class="col-md-5 col-lg-4 text-center">
                                    <div id="logoPreviewBox"
                                         class="mx-auto d-flex align-items-center justify-content-center"
                                         style="
                                            width: 100%;
                                            max-width: 240px;
                                            height: 120px;
                                            border-radius: var(--radius);
                                            border: 2px dashed rgba(18,32,46,0.15);
                                            background: #fff;
                                            overflow: hidden;
                                         ">
                                        @if (!empty($profile->logo_path) && !empty($profile->logo_url))
                                            <img id="logoPreviewImg"
                                                 src="{{ $profile->logo_url }}"
                                                 alt="Current logo"
                                                 style="max-width: 100%; max-height: 100%; display: block;">
                                        @else
                                            <span id="logoEmptyHint" class="ink-soft" style="font-size: 0.875rem;">
                                                <i class="bi bi-file-earmark-image me-1"></i> No logo yet
                                            </span>
                                            <img id="logoPreviewImg" src="" alt="" style="display: none; max-width: 100%; max-height: 100%;">
                                        @endif
                                    </div>

                                    @if (!empty($profile->logo_path))
                                        <div class="mt-3">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input" type="checkbox"
                                                       id="remove_logo" name="remove_logo" value="1"
                                                       {{ old('remove_logo') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="remove_logo"
                                                       style="color: var(--overdue);">
                                                    <i class="bi bi-trash3 me-1"></i> Remove logo
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-7 col-lg-8">
                                    <label for="logo" class="form-label">Upload logo</label>
                                    <input type="file" id="logo" name="logo"
                                           class="form-control form-control-lg @error('logo') is-invalid @enderror"
                                           accept="image/png,image/jpeg,image/jpg">
                                    <div class="form-text mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        PNG or JPG only, max 2 MB. Will appear on every saved invoice and PDF.
                                    </div>
                                    @error('logo')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --------------------------------------------------------- --}}
                    <div class="d-flex justify-content-end gap-3 flex-wrap">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-ink btn-lg">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-saffron btn-lg">
                            <i class="bi bi-check2 me-1"></i> Save profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    var fileInput = document.getElementById('logo');
    var previewBox = document.getElementById('logoPreviewBox');
    var previewImg = document.getElementById('logoPreviewImg');
    var emptyHint  = document.getElementById('logoEmptyHint');
    var removeCb   = document.getElementById('remove_logo');

    function showImage(src) {
        if (previewImg) {
            previewImg.src = src;
            previewImg.style.display = 'block';
        }
        if (emptyHint) {
            emptyHint.style.display = 'none';
        }
    }

    function hideImage() {
        if (previewImg) {
            previewImg.removeAttribute('src');
            previewImg.style.display = 'none';
        }
        if (emptyHint) {
            emptyHint.style.display = '';
        }
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0];
            if (!file) return;
            if (!/^image\/(png|jpe?g)$/i.test(file.type)) return;
            var reader = new FileReader();
            reader.onload = function () {
                showImage(reader.result);
                if (removeCb) removeCb.checked = false;
            };
            reader.readAsDataURL(file);
        });
    }

    if (removeCb) {
        removeCb.addEventListener('change', function () {
            if (removeCb.checked) {
                if (fileInput) fileInput.value = '';
            }
        });
    }
})();
</script>
@endsection
