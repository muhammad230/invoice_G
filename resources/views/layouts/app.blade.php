<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Per-page meta description (each view sets @section('meta_description')) --}}
    <meta name="description" content="@yield('meta_description', 'InvoiceFlow — simple, beautiful invoicing for freelancers and small businesses. Create invoices, track payments, and get paid on time.')">

    <title>@yield('title', 'InvoiceFlow')</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    {{-- Open Graph (social sharing) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="InvoiceFlow">
    <meta property="og:title" content="@yield('title', 'InvoiceFlow')">
    <meta property="og:description" content="@yield('meta_description', 'InvoiceFlow — simple, beautiful invoicing for freelancers and small businesses.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">

    <!-- Google Fonts: Fraunces (headings) + Inter (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- InvoiceFlow Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <!-- Navbar -->
    @include('partials.navbar')

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')

    <!-- Bootstrap 5 JS Bundle (includes Popper) from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Page-specific scripts (yielded from child views) -->
    @yield('scripts')
</body>
</html>
