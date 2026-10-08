@extends('layouts.dashboard')

@section('title', 'Services — InvoiceFlow')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-bag me-1"></i>
                    Services
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">Your products &amp; services</h1>
                <p class="ink-soft mb-0 mt-1">
                    Save services you invoice for often. Drop them into any invoice with one click.
                </p>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-saffron btn-lg">
                <i class="bi bi-plus-lg me-1"></i>
                New service
            </a>
        </div>

        @include('partials.alerts')

        {{-- Search bar ------------------------------------------------------ --}}
        <form method="GET" action="{{ route('products.index') }}" class="mb-4">
            <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                <div class="card-body p-3 p-lg-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-10">
                            <label for="q" class="form-label">Search</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" aria-hidden="true">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="q" name="q"
                                       class="form-control form-control-lg"
                                       value="{{ $q }}"
                                       placeholder="Search services by name or description…">
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-ink btn-lg w-100 justify-content-center">
                                    <i class="bi bi-funnel me-1 d-none d-sm-inline"></i>
                                    Search
                                </button>
                                <a href="{{ route('products.index') }}"
                                   class="btn btn-outline-ink btn-lg justify-content-center"
                                   title="Clear search">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if ($products->isEmpty())
            <div class="card dashboard-empty">
                <div class="card-body text-center py-5">
                    <div class="floating-hero-icon mb-4" style="width: 96px; height: 96px; font-size: 2rem;">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                    <h2 class="h3 mb-2">No services yet</h2>
                    <p class="ink-soft mb-4 mx-auto" style="max-width: 460px;">
                        Save the services or products you offer most often. Next time you build
                        an invoice, pick one from a dropdown and its name + price fill in automatically.
                    </p>
                    <a href="{{ route('products.create') }}" class="btn btn-saffron btn-lg">
                        <i class="bi bi-plus-lg me-1"></i>
                        Add your first service
                    </a>
                </div>
            </div>
        @else
            <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="font-size: 0.8125rem; color: var(--ink-soft); background: rgba(18,32,46,0.02);">
                                <th scope="col" class="px-4 py-3">Service</th>
                                <th scope="col" class="px-4 py-3 text-end">Default price</th>
                                <th scope="col" class="px-4 py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold ink">{{ $product->name }}</div>
                                        @if ($product->description)
                                            <div class="ink-soft" style="font-size: 0.875rem;">
                                                {{ Illuminate\Support\Str::limit($product->description, 80) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end fw-semibold" style="font-variant-numeric: tabular-nums;">
                                        ${{ $product->price_decimal }}
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('products.edit', $product) }}"
                                               class="btn btn-sm btn-outline-ink">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('products.destroy', $product) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Delete service &ldquo;{{ $product->name }}&rdquo;? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm"
                                                        style="background: color-mix(in srgb, var(--overdue) 12%, #fff); color: var(--overdue); border: 1px solid color-mix(in srgb, var(--overdue) 30%, #fff);">
                                                    <i class="bi bi-trash3 me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
