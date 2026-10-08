{{--
    Shared sidebar navigation — used both by the fixed sidebar (≥992px)
    and the offcanvas sidebar (<992px).
    $active : callable(string|array $pattern):bool  — true if route matches.
--}}
@php
    $active = $active ?? function ($patterns) {
        foreach ((array) $patterns as $p) {
            if (request()->routeIs($p)) return true;
        }
        return false;
    };

    $item = function ($route, $icon, $label, $patterns = null) use ($active) {
        $patterns = $patterns ?? $route;
        $isActive = $active($patterns);
        return (object)[
            'href'  => route($route),
            'icon'  => $icon,
            'label' => $label,
            'active'=> $isActive,
        ];
    };

    $menu = [
        $item('dashboard',                   'bi-grid-1x2-fill',    'Dashboard'),
        $item('invoices.create',             'bi-file-earmark-plus', 'Create Invoice', ['invoice.create', 'invoices.create']),
        $item('invoices.index',              'bi-receipt-cutoff',   'Invoices', ['invoices.*', 'invoices.pdf', 'invoices.mark-paid', 'invoices.duplicate']),
        $item('clients.index',               'bi-people-fill',      'Clients', ['clients.*']),
        $item('products.index',              'bi-bag-check-fill',   'Products &amp; Services', ['products.*']),
        $item('settings.business-profile.edit','bi-gear-fill',     'Settings', ['settings.*']),
    ];
@endphp

<nav class="sidebar-nav" aria-label="Main menu">
    <ul class="sidebar-nav__list list-unstyled mb-0">
        @foreach ($menu as $m)
            <li class="sidebar-nav__item">
                <a href="{{ $m->href }}"
                   class="sidebar-nav__link {{ $m->active ? 'is-active' : '' }}"
                   aria-current="{{ $m->active ? 'page' : 'false' }}">
                    <i class="sidebar-nav__icon bi {{ $m->icon }}" aria-hidden="true"></i>
                    <span class="sidebar-nav__label">{!! $m->label !!}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>
