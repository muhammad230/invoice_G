<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="InvoiceFlow - Dashboard. Track invoices, clients, and revenue in one place.">

    <title>@yield('title', 'Dashboard · InvoiceFlow')</title>

    <!-- Google Fonts: Fraunces (headings) + Inter (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js (from CDN) — used by dashboard, included globally for simplicity -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- InvoiceFlow Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="app-shell">
    @php
        // Determine active nav item from route name.
        $__route = request()->route() ? request()->route()->getName() : '';
        $navActive = function ($patterns) use ($__route) {
            foreach ((array) $patterns as $p) {
                if ($p === $__route) return true;
                if (str_ends_with($p, '*') && str_starts_with($__route, rtrim($p, '*'))) return true;
            }
            return false;
        };

        /** @var \App\Models\User|null $__user */
        $__user = auth()->user();
        $__initials = '';
        if ($__user) {
            $name = trim((string) $__user->name);
            if ($name !== '') {
                $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
                $__initials = strtoupper(mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
            }
            if ($__initials === '') $__initials = 'U';
        }
    @endphp

    {{-- =====================================================================
         OFFCANVAS (mobile / <992px) sidebar — identical to the fixed sidebar.
         Bootstrap 5 offcanvas: .offcanvas.offcanvas-start
         ===================================================================== --}}
    <div class="offcanvas offcanvas-start app-offcanvas"
         tabindex="-1"
         id="sidebarOffcanvas"
         aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header border-0">
            <a class="sidebar-brand" href="{{ route('dashboard') }}" aria-label="InvoiceFlow dashboard">
                <span class="sidebar-brand__icon" aria-hidden="true">
                    <i class="bi bi-check2"></i>
                </span>
                <span class="sidebar-brand__text">InvoiceFlow</span>
            </a>
            <button type="button"
                    class="btn-close app-offcanvas__close"
                    data-bs-dismiss="offcanvas"
                    aria-label="Close menu"></button>
        </div>
        <div class="offcanvas-body px-0 pt-0">
            @include('partials.sidebar-nav', ['active' => $navActive])
        </div>
    </div>

    {{-- =====================================================================
         FIXED SIDEBAR (≥992px)
         ===================================================================== --}}
    <aside class="app-sidebar" aria-label="Main navigation">
        <a class="sidebar-brand sidebar-brand--fixed"
           href="{{ route('dashboard') }}"
           aria-label="InvoiceFlow dashboard">
            <span class="sidebar-brand__icon" aria-hidden="true">
                <i class="bi bi-check2"></i>
            </span>
            <span class="sidebar-brand__text">InvoiceFlow</span>
        </a>

        <div class="app-sidebar__nav-spacer">
            @include('partials.sidebar-nav', ['active' => $navActive])
        </div>
    </aside>

    {{-- =====================================================================
         MAIN WRAPPER — right of sidebar, contains topbar + page content.
         ===================================================================== --}}
    <div class="app-main">

        {{-- =====================================================================
             TOP BAR
             - Hamburger (opens offcanvas on <992px)
             - Search form (GET /invoices with q)
             - Notification bell
             - User avatar + name/role + dropdown (Profile, Logout)
             ===================================================================== --}}
        <header class="app-topbar">
            <div class="container-fluid">
                <div class="d-flex align-items-center gap-3 gap-lg-4">

                    {{-- Hamburger (mobile/tablet only) --}}
                    <button class="btn btn-ghost app-topbar__burger"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#sidebarOffcanvas"
                            aria-controls="sidebarOffcanvas"
                            aria-label="Open main menu">
                        <i class="bi bi-list fs-4"></i>
                    </button>

                    {{-- Title (mobile only) --}}
                    <a href="{{ route('dashboard') }}"
                       class="d-inline-flex d-lg-none align-items-center gap-2 text-decoration-none">
                        <span class="sidebar-brand__icon sidebar-brand__icon--sm" aria-hidden="true">
                            <i class="bi bi-check2"></i>
                        </span>
                        <span class="fw-bold" style="color: var(--ink); font-family: 'Fraunces', Georgia, serif;">InvoiceFlow</span>
                    </a>

                    {{-- Search box (flex-grow for center placement) --}}
                    <form method="GET"
                          action="{{ route('invoices.index') }}"
                          role="search"
                          class="app-topbar__search flex-grow-1">
                        <label for="topbarSearch" class="visually-hidden">Search invoices</label>
                        <div class="input-group input-group-search">
                            <span class="input-group-text" id="topbarSearchIcon" aria-hidden="true">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="search"
                                   id="topbarSearch"
                                   name="q"
                                   class="form-control"
                                   placeholder="Search invoice # or client name…"
                                   aria-describedby="topbarSearchIcon"
                                   value="{{ request('q', '') }}">
                            <button class="btn btn-ink btn-search-submit" type="submit">
                                Search
                            </button>
                        </div>
                    </form>

                    {{-- Right cluster: bell + avatar + menu --}}
                    <div class="d-flex align-items-center gap-2 gap-md-3 ms-auto">

                        <button type="button"
                                class="btn btn-ghost app-topbar__iconbtn"
                                aria-label="Notifications"
                                title="Notifications (coming soon)">
                            <i class="bi bi-bell fs-5"></i>
                        </button>

                        {{-- Avatar + dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-ghost app-topbar__avatarbtn d-flex align-items-center gap-2 gap-md-3"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                    aria-label="Open account menu">
                                <span class="avatar avatar--sm" aria-hidden="true">{{ $__initials }}</span>
                                <span class="d-none d-md-flex flex-column align-items-start text-start">
                                    <span class="fw-semibold" style="color: var(--ink); line-height: 1.2;">
                                        {{ $__user ? e($__user->name) : 'Guest' }}
                                    </span>
                                    <span style="color: var(--ink-soft); font-size: 0.75rem; line-height: 1.2;">
                                        Business Owner
                                    </span>
                                </span>
                                <i class="bi bi-chevron-down d-none d-md-inline" style="color: var(--ink-soft); font-size: 0.875rem;"></i>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end app-dropdown-menu shadow-sm">
                                <li class="d-md-none px-3 pt-3 pb-2">
                                    <div class="fw-semibold mb-0" style="color: var(--ink);">
                                        {{ $__user ? e($__user->name) : 'Guest' }}
                                    </div>
                                    <div style="color: var(--ink-soft); font-size: 0.8rem;">
                                        Business Owner
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider d-md-none"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('settings.business-profile.edit') }}">
                                        <i class="bi bi-person-gear me-2" style="color: var(--ink-soft);"></i>
                                        Profile &amp; Settings
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}"
                                          class="m-0"
                                          onsubmit="event.preventDefault(); const f = event.target || this; f.submit();">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>
                                            Log out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        {{-- =====================================================================
             PAGE CONTENT
             ===================================================================== --}}
        <div class="app-content">
            <div class="container-fluid">
                @include('partials.alerts')

                @yield('content')
            </div>
        </div>

        <footer class="app-footer">
            <div class="container-fluid">
                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center py-3">
                    <span style="color: var(--ink-soft); font-size: 0.8125rem;">
                        &copy; {{ date('Y') }} InvoiceFlow — Paper-ledger invoicing for freelancers.
                    </span>
                    <span style="color: var(--ink-soft); font-size: 0.8125rem;">
                        Made with <i class="bi bi-suit-heart-fill" style="color: var(--overdue);"></i> for small business owners
                    </span>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS Bundle (includes Popper) from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Page-specific scripts (yielded from child views) -->
    @yield('scripts')
</body>
</html>
