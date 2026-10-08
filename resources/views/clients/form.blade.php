@extends('layouts.dashboard')

@section('title', (isset($mode) && $mode === 'edit' ? 'Edit client' : 'New client') . ' — InvoiceFlow')

@php
    $mode = isset($mode) ? $mode : (Route::currentRouteName() === 'clients.edit' ? 'edit' : 'create');
    $formAction = $mode === 'edit'
        ? route('clients.update', $client)
        : route('clients.store', request()->query());
    $submitLabel = $mode === 'edit' ? 'Save changes' : 'Create client';
    $pageTitle = $mode === 'edit' ? 'Edit client' : 'New client';
    $pageSubtitle = $mode === 'edit'
        ? 'Update contact details for this client.'
        : 'Add a client so you can start invoicing them.';
@endphp

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                    <div>
                        <span class="section-label mb-2">
                            <i class="bi bi-person-plus me-1"></i>
                            {{ $pageTitle }}
                        </span>
                        <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">{{ $pageTitle }}</h1>
                        <p class="ink-soft mb-0 mt-1">{{ $pageSubtitle }}</p>
                    </div>
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-ink">
                        <i class="bi bi-arrow-left"></i>
                        Back to clients
                    </a>
                </div>

                @include('partials.alerts')

                <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-md);">
                    <div class="card-body p-4 p-lg-5">
                        <form method="POST" action="{{ $formAction }}" novalidate>
                            @csrf
                            @if ($mode === 'edit')
                                @method('PUT')
                            @endif

                            <div class="row g-3 g-lg-4 mb-4">
                                <div class="col-12">
                                    <label for="name" class="form-label">Full name / Company <span style="color: var(--overdue);">*</span></label>
                                    <input type="text" id="name" name="name"
                                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                                           placeholder="Acme Inc. or Jane Doe"
                                           value="{{ old('name', $client->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           placeholder="client@example.com"
                                           value="{{ old('email', $client->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" id="phone" name="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           placeholder="+1 (555) 000-0000"
                                           value="{{ old('phone', $client->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea id="address" name="address" rows="3"
                                              class="form-control @error('address') is-invalid @enderror"
                                              placeholder="Street, city, country">{{ old('address', $client->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('clients.index') }}" class="btn btn-outline-ink">Cancel</a>
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-check2 me-1"></i>
                                    {{ $submitLabel }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
