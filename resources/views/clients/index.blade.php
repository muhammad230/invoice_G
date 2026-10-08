@extends('layouts.dashboard')

@section('title', 'Clients — InvoiceFlow')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <span class="section-label mb-2">
                    <i class="bi bi-people me-1"></i>
                    Clients
                </span>
                <h1 class="mb-0" style="font-size: clamp(1.75rem, 3vw, 2.25rem);">Your clients</h1>
            </div>
            <a href="{{ route('clients.create') }}" class="btn btn-saffron btn-lg">
                <i class="bi bi-person-plus me-1"></i>
                New client
            </a>
        </div>

        @if ($clients->isEmpty())
            <div class="card dashboard-empty">
                <div class="card-body text-center py-5">
                    <div class="floating-hero-icon mb-4" style="width: 96px; height: 96px; font-size: 2rem;">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <h2 class="h3 mb-2">No clients yet</h2>
                    <p class="ink-soft mb-4 mx-auto" style="max-width: 460px;">
                        Add your first client so you can start sending them invoices.
                        You can edit and remove them anytime.
                    </p>
                    <a href="{{ route('clients.create') }}" class="btn btn-saffron btn-lg">
                        <i class="bi bi-plus-lg me-1"></i>
                        Add your first client
                    </a>
                </div>
            </div>
        @else
            <div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 list-table">
                        <thead>
                            <tr style="font-size: 0.8125rem; color: var(--ink-soft); background: rgba(18,32,46,0.02);">
                                <th scope="col">Client</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col" class="text-center">Invoices</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-ring" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                                {{ strtoupper(mb_substr($client->name, 0, 1) ?? 'C') }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold ink">{{ $client->name }}</div>
                                                @if ($client->address)
                                                    <div class="ink-soft" style="font-size: 0.78rem;">
                                                        {{ Illuminate\Support\Str::limit($client->address, 48) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($client->email)
                                            <a href="mailto:{{ $client->email }}" class="auth-link">
                                                {{ $client->email }}
                                            </a>
                                        @else
                                            <span class="text-muted" style="color: var(--ink-soft);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $client->phone ?: '—' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill status-pending">
                                            {{ $client->invoices_count }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('clients.edit', $client) }}"
                                               class="btn btn-sm btn-outline-ink">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('clients.destroy', $client) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Delete client &ldquo;{{ $client->name }}&rdquo;?\nAll of their invoices and invoice items will also be deleted.');">
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
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
