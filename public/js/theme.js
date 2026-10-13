/* ============================================
   InvoiceFlow - Light / Dark theme controller
   - Persists the choice in localStorage
   - Falls back to the OS `prefers-color-scheme`
   - Sets <html data-theme="..."> before first paint (no flash)
   ============================================ */
(function () {
    'use strict';

    var STORAGE_KEY = 'invoiceflow-theme';
    var root = document.documentElement;

    function readStored() {
        try {
            return window.localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function store(theme) {
        try {
            window.localStorage.setItem(STORAGE_KEY, theme);
        } catch (e) {
            /* private mode / storage disabled — ignore */
        }
    }

    function systemPrefersDark() {
        return window.matchMedia
            && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function current() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function apply(theme) {
        root.setAttribute('data-theme', theme);
    }

    // Keep Chart.js text/grid readable in both themes. Charts are created later
    // from blade @section('scripts'); these globals are read at creation time.
    function applyChartDefaults() {
        if (typeof window.Chart === 'undefined') {
            return;
        }
        var dark = current() === 'dark';
        window.Chart.defaults.color = dark ? '#9AA9B8' : '#4A5866';
        window.Chart.defaults.borderColor = dark
            ? 'rgba(255, 255, 255, 0.08)'
            : 'rgba(18, 32, 46, 0.08)';
    }

    function refreshCharts() {
        applyChartDefaults();
        if (typeof window.Chart === 'undefined' || !window.Chart.getChart) {
            return;
        }
        // Canvases that have no chart yet simply return null from getChart().
        Array.prototype.forEach.call(document.querySelectorAll('canvas'), function (canvas) {
            var chart = window.Chart.getChart(canvas);
            if (chart) {
                chart.update();
            }
        });
    }

    // Apply immediately so the first paint already uses the right theme.
    var stored = readStored();
    if (stored === 'dark' || stored === 'light') {
        apply(stored);
    } else {
        apply(systemPrefersDark() ? 'dark' : 'light');
    }
    applyChartDefaults();

    // Delegate clicks so toggles injected anywhere (navbar, topbar, …) work.
    document.addEventListener('click', function (event) {
        var btn = event.target.closest ? event.target.closest('[data-theme-toggle]') : null;
        if (!btn) {
            return;
        }
        var next = current() === 'dark' ? 'light' : 'dark';
        apply(next);
        store(next);
        btn.setAttribute('aria-pressed', next === 'dark' ? 'true' : 'false');
        refreshCharts();
    });

    // Re-sync chart defaults once the DOM (and chart scripts) are ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyChartDefaults);
    } else {
        applyChartDefaults();
    }

    // Keep in sync with OS changes only while the user hasn't chosen manually.
    if (window.matchMedia) {
        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        var onChange = function (e) {
            if (!readStored()) {
                apply(e.matches ? 'dark' : 'light');
            }
        };
        if (mq.addEventListener) {
            mq.addEventListener('change', onChange);
        } else if (mq.addListener) {
            mq.addListener(onChange);
        }
    }
})();
