{{--
    Shared light/dark theme toggle button.
    Included by the public navbar and the dashboard topbar.
    Behaviour lives in public/js/theme.js (delegated click).
--}}
<button type="button"
        class="btn btn-ghost theme-toggle {{ $btnClass ?? '' }}"
        data-theme-toggle
        aria-label="Toggle light and dark theme"
        aria-pressed="false"
        title="Toggle light / dark theme">
    <i class="bi bi-moon-stars theme-toggle__icon theme-toggle__icon--dark" aria-hidden="true"></i>
    <i class="bi bi-sun theme-toggle__icon theme-toggle__icon--light" aria-hidden="true"></i>
</button>
