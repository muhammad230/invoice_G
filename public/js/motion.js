/* ============================================
   InvoiceFlow - Motion controller
   - Scroll-reveal: elements fade / slide in as they enter the viewport
   - Page transitions: content fades out before navigating away
   Disabled automatically when the OS asks for reduced motion.
   The <html class="js-motion"> flag is added pre-paint by an inline
   head script (see the layouts) so nothing flashes on first load.
   ============================================ */
(function () {
    'use strict';

    var root = document.documentElement;
    var reduce = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Tell the head-script failsafe that motion booted successfully.
    window.__ifMotionReady = true;
    if (reduce) {
        root.classList.remove('js-motion');
        return;
    }

    /* --------------------------------------------------------------
       The selector list below MUST mirror the reveal rules in
       public/css/style.css (search for "js-motion").
       -------------------------------------------------------------- */
    var REVEAL_SELECTOR = [
        '[data-reveal]',
        'section',
        '.page-hero',
        '.feature-card',
        '.pricing-card',
        '.stat-card',
        '.dash-stat',
        '.dash-date-card',
        '.invoice-card',
        '.pending-card',
        '.contact-card',
        '.auth-card',
        '.panel-card',
        '.comparison-table',
        '.cta-section',
        '.faq-accordion .accordion-item'
    ].join(',');

    function revealTargets() {
        var scope = document.querySelector('main')
            || document.querySelector('.app-content')
            || document.body;

        var nodes = Array.prototype.slice.call(scope.querySelectorAll(REVEAL_SELECTOR));

        // Never touch navigation chrome or anything inside an overlay that
        // may be hidden (a hidden element would never intersect and would
        // therefore stay invisible for good).
        return nodes.filter(function (el) {
            if (el.closest('.modal, .offcanvas, .dropdown-menu, nav, header, footer, .app-sidebar')) {
                return false;
            }
            return true;
        });
    }

    function initReveal() {
        var nodes = revealTargets();
        if (!nodes.length || !('IntersectionObserver' in window)) {
            root.classList.remove('js-motion');
            return;
        }

        var observer = new IntersectionObserver(function (entries, obs) {
            var shownInBatch = 0;
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                var el = entry.target;
                var base = parseInt(el.getAttribute('data-reveal-delay') || '0', 10) || 0;
                // Small cascade for items entering together.
                var delay = base + Math.min(shownInBatch, 5) * 60;
                el.style.animationDelay = delay + 'ms';
                el.classList.add('is-revealed');

                // Once the entrance finishes, drop the animation and hold the
                // visible state with a plain class — this clears the animated
                // `translate`, so it can never linger and interfere with
                // sticky/positioned descendants.
                var settle = function () {
                    el.removeEventListener('animationend', settle);
                    el.classList.remove('is-revealed');
                    el.classList.add('is-shown');
                    el.style.animationDelay = '';
                };
                el.addEventListener('animationend', settle);
                window.setTimeout(settle, delay + 900);

                shownInBatch++;
                obs.unobserve(el);
            });
        }, { threshold: 0.01 });

        nodes.forEach(function (el) { observer.observe(el); });
    }

    /* --------------------------------------------------------------
       Page transitions — fade the page out before an internal
       navigation so moving between pages feels smooth.
       -------------------------------------------------------------- */
    function isInternalNavigation(link) {
        if (!link || link.target === '_blank' || link.hasAttribute('download')) {
            return false;
        }
        if (link.hasAttribute('data-no-motion') || link.closest('[data-bs-toggle]')) {
            return false;
        }
        var href = link.getAttribute('href');
        if (!href || href.charAt(0) === '#') {
            return false;
        }
        if (/^(mailto:|tel:|javascript:|blob:|data:)/i.test(href)) {
            return false;
        }
        return link.origin === window.location.origin;
    }

    function initPageTransitions() {
        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.button !== 0
                || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }
            var link = event.target.closest ? event.target.closest('a') : null;
            if (!isInternalNavigation(link)) {
                return;
            }
            if (document.body.classList.contains('if-leaving')) {
                event.preventDefault();
                return;
            }

            var url = new URL(link.href, window.location.href);
            // Same page, pure in-page anchor → let the browser handle it.
            if (url.pathname === window.location.pathname
                && url.search === window.location.search
                && url.hash) {
                return;
            }

            event.preventDefault();
            document.body.classList.add('if-leaving');
            window.setTimeout(function () {
                window.location.assign(url.href);
            }, 200);
        });

        // Restore state when the page is served from the back/forward cache.
        window.addEventListener('pageshow', function () {
            document.body.classList.remove('if-leaving');
        });
    }

    function boot() {
        initReveal();
        initPageTransitions();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
