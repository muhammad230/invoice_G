@extends('layouts.app')

@section('title', 'Edit service · ' . $product->name . ' — InvoiceFlow')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                    <div>
                        <span class="section-label mb-2">
                            <i class="bi bi-pencil me-1"></i>
                            Edit service
                        </span>
                        <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">
                            {{ $product->name }}
                        </h1>
                        <p class="ink-soft mb-0 mt-1">
                            Update the name, description or default price.
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
                        <form method="POST" action="{{ route('products.update', $product) }}" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="row g-3 g-lg-4 mb-4">
                                <div class="col-12">
                                    <label for="name" class="form-label">
                                        Name <span style="color: var(--overdue);">*</span>
                                    </label>
                                    <input type="text" id="name" name="name"
                                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                                           value="{{ old('name', $product->name) }}" required maxlength="255">
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
                                               value="{{ old('price', $product->price_decimal) }}" required>
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
                                              maxlength="1000">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <form method="POST"
                                      action="{{ route('products.destroy', $product) }}"
                                      class="d-inline m-0"
                                      onsubmit="return confirm('Delete service &ldquo;{{ $product->name }}&rdquo;?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn"
                                            style="background: color-mix(in srgb, var(--overdue) 12%, #fff); color: var(--overdue); border: 1px solid color-mix(in srgb, var(--overdue) 30%, #fff);">
                                        <i class="bi bi-trash3 me-1"></i> Delete
                                    </button>
                                </form>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-ink">Cancel</a>
                                    <button type="submit" class="btn btn-saffron btn-lg">
                                        <i class="bi bi-check2 me-1"></i>
                                        Save changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
