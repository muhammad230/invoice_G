@extends('layouts.app')

@section('title', 'Session expired — InvoiceFlow')
@section('meta_description', 'Your session expired. Please refresh the page and try again.')

@section('content')
<section class="error-page">
    <div class="container text-center">
        <div class="error-code error-code--overdue">419</div>
        <h1>Your session expired</h1>
        <p class="mx-auto" style="max-width: 480px;">
            Your session expired. Please refresh and try again.
            For your security, long-idle forms are reset automatically.
        </p>
        <div class="error-actions">
            <a href="{{ url()->previous() }}" class="btn btn-saffron">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh &amp; try again
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-ink">
                <i class="bi bi-house-door me-1"></i> Back home
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline-ink">
                    <i class="bi bi-speedometer2 me-1"></i> Go to dashboard
                </a>
            @endauth
        </div>
    </div>
</section>
@endsection
