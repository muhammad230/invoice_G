@extends('layouts.app')

@section('title', 'Contact us — InvoiceFlow')
@section('meta_description', 'Get in touch with the InvoiceFlow team. Send us a message and we will reply by email as soon as we can.')

@section('content')

{{-- ============================================================
     HERO
     ============================================================ --}}
<header class="page-hero">
    <div class="container">
        <span class="section-label">
            <i class="bi bi-chat-dots me-1"></i>
            Contact
        </span>
        <h1>Get in touch</h1>
        <p>
            Questions, feedback or a bug to report? Send us a message and we'll
            reply by email.
        </p>
    </div>
</header>

<section class="pt-0">
    <div class="container">
        <div class="row g-5 justify-content-center">
            {{-- Form --}}
            <div class="col-12 col-lg-7">
                <div class="contact-card">
                    {{-- Success + validation errors --}}
                    @include('partials.alerts')

                    <h2 class="h4 mb-4">Send us a message</h2>

                    <form method="POST" action="{{ route('contact.send') }}" novalidate>
                        @csrf

                        {{-- Honeypot: hidden from people, catches simple bots. --}}
                        <div class="hp-field" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Name <span style="color: var(--overdue);">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   maxlength="120"
                                   required
                                   autocomplete="name"
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email <span style="color: var(--overdue);">*</span>
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   maxlength="255"
                                   required
                                   autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div class="mb-4">
                            <label for="message" class="form-label">
                                Message <span style="color: var(--overdue);">*</span>
                            </label>
                            <textarea id="message"
                                      name="message"
                                      class="form-control @error('message') is-invalid @enderror"
                                      maxlength="5000"
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-saffron btn-lg">
                            <i class="bi bi-send me-1"></i> Send message
                        </button>
                    </form>
                </div>
            </div>

            {{-- Side info --}}
            <div class="col-12 col-lg-4">
                <div class="contact-info-item mb-4">
                    <div class="feature-icon saffron">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="h6 mb-1">Email</h3>
                        <p class="mb-0">hello@invoiceflow.app</p>
                    </div>
                </div>

                <div class="contact-info-item mb-4">
                    <div class="feature-icon sage">
                        <i class="bi bi-clock-history" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="h6 mb-1">Response time</h3>
                        <p class="mb-0">Usually within 1–2 business days.</p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="feature-icon ink">
                        <i class="bi bi-life-preserver" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="h6 mb-1">Looking for answers?</h3>
                        <p class="mb-0">
                            Many questions are answered in the
                            <a href="{{ route('help') }}">Help center</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
