@extends('layouts.app')

@section('title', 'Get Started — InvoiceFlow')

@section('content')
<div class="auth-placeholder">
    <div class="auth-card">
        <div class="logo-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
            <i class="bi bi-check2"></i>
        </div>
        <h1>Create your account</h1>
        <p>Start for free. No credit card required. Upgrade whenever you need more.</p>
        <p class="mb-0 text-muted" style="font-size: 0.875rem;">
            <em>This is a placeholder page. Registration will be added soon.</em>
        </p>
        <div class="mt-4">
            <a href="{{ route('home') }}" class="btn btn-outline-ink">
                <i class="bi bi-arrow-left"></i>
                Back to home
            </a>
        </div>
    </div>
</div>
@endsection
