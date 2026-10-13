{{--
    Shared light/dark theme toggle button.
    Included by the public navbar, the dashboard topbar and the sidebar.
    Behaviour lives in public/js/theme.js (delegated click).

    $btnClass  string  extra classes on the button
    $withLabel bool    also render a text label (used by the sidebar)
--}}
<button type="button"
        class="btn btn-ghost theme-toggle {{ $btnClass ?? '' }}"
        data-theme-toggle
        aria-label="Toggle light and dark theme"
        aria-pressed="false"
        title="Toggle light / dark theme">
    <i class="bi bi-moon-stars theme-toggle__icon theme-toggle__icon--dark" aria-hidden="true"></i>
    <i class="bi bi-sun theme-toggle__icon theme-toggle__icon--light" aria-hidden="true"></i>
    @if (!empty($withLabel))
        <span class="theme-toggle__label theme-toggle__label--dark">Dark mode</span>
        <span class="theme-toggle__label theme-toggle__label--light">Light mode</span>
    @endif
</button>
