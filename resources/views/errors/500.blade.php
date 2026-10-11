@extends('layouts.app')

@section('title', 'Something went wrong — InvoiceFlow')
@section('meta_description', 'Something went wrong on our side. Please try again in a moment.')

@section('content')
<section class="error-page">
    <div class="container text-center">
        <div class="error-code error-code--overdue">500</div>
        <h1>Something went wrong</h1>
        <p class="mx-auto" style="max-width: 480px;">
            An unexpected error occurred on our side. Please try again in a
            moment — if it keeps happening, let us know.
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
