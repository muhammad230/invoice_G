@extends('layouts.app')

@section('title', 'Page not found — InvoiceFlow')
@section('meta_description', 'The page you were looking for could not be found.')

@section('content')
<section class="error-page">
    <div class="container text-center">
        <div class="error-code">404</div>
        <h1>We couldn't find that page</h1>
        <p class="mx-auto" style="max-width: 480px;">
            The page may have moved, or the link might be broken.
            Let's get you back on track.
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
