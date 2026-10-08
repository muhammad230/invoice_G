@extends('layouts.dashboard')

@section('title', 'New service — InvoiceFlow')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                    <div>
                        <span class="section-label mb-2">
                            <i class="bi bi-bag-plus me-1"></i>
                            New service
                        </span>
                        <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">Create a service</h1>
                        <p class="ink-soft mb-0 mt-1">
                            Save it once, then drop it into any invoice in one click.
                        </p>
                    </div>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-ink">
                        <i class="bi bi-arrow-left"></i>
                        Back to services
                    </a>
                </div>

                @include('partials.alerts')

                <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-md);">
                    <div class="card-body p-4 p-lg-5">
                        <form method="POST" action="{{ route('products.store') }}" novalidate>
                            @csrf

                            <div class="row g-3 g-lg-4 mb-4">
                                <div class="col-12">
                                    <label for="name" class="form-label">
                                        Name <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="text" id="name" name="name"
                                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                                           placeholder="e.g. Website design, Monthly retainer, Coaching session"
                                           value="{{ old('name') }}" required maxlength="255">
                                    @error('name')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="price" class="form-label">
                                        Default price (USD) <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="price" name="price"
                                               class="form-control @error('price') is-invalid @enderror"
                                               min="0" step="0.01"
                                               placeholder="0.00"
                                               value="{{ old('price', '0.00') }}" required>
                                    </div>
                                    <div class="form-text mt-1">
                                        You can still override this price when creating an invoice.
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Default description</label>
                                    <textarea id="description" name="description" rows="4"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Optional. Fills in the invoice item description automatically."
                                              maxlength="1000">{{ old('description') }}</textarea>
                                    <div class="form-text mt-1">
                                        Good descriptions help your client understand what they're paying for.
                                    </div>
                                    @error('description')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-ink">Cancel</a>
                                <button type="submit" class="btn btn-saffron btn-lg">
                                    <i class="bi bi-check2 me-1"></i>
                                    Save service
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
