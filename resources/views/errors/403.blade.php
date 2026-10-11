@extends('layouts.app')

@section('title', 'Access denied — InvoiceFlow')
@section('meta_description', 'You do not have permission to view this page.')

@section('content')
<section class="error-page">
    <div class="container text-center">
        <div class="error-code error-code--overdue">403</div>
        <h1>You don't have access to this</h1>
        <p class="mx-auto" style="max-width: 480px;">
            Your account doesn't have permission to view this page. If you
            think this is a mistake, please contact us.
        </p>
        <div class="error-actions">
            <a href="{{ route('home') }}" class="btn btn-saffron">
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
