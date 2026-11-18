/* =====================================================================
   PHOTO GALLERY (resources/views/components/photo-gallery.blade.php)
   Swiping already works with CSS scroll-snap. This file only adds:
   - the "1 / 3" counter, updated while you swipe
   - previous / next buttons (handy with a mouse)
   ===================================================================== */
(function () {
    'use strict';

    document.querySelectorAll('[data-gallery]').forEach(function (gallery) {
        var track = gallery.querySelector('[data-gallery-track]');
        var current = gallery.querySelector('[data-gallery-current]');
        var prev = gallery.querySelector('[data-gallery-prev]');
        var next = gallery.querySelector('[data-gallery-next]');
        var total = track.children.length;

        if (total < 2) {
            return;
        }

        // Which photo is showing: how far we've scrolled, in photo widths
        function index() {
            return Math.round(track.scrollLeft / track.clientWidth);
        }

        function update() {
            var i = index();
            current.textContent = i + 1;
            prev.disabled = i === 0;
            next.disabled = i === total - 1;
        }

        function goTo(i) {
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            track.scrollTo({
                left: i * track.clientWidth,
                behavior: reduceMotion ? 'auto' : 'smooth'
            });
        }

        prev.addEventListener('click', function () { goTo(index() - 1); });
        next.addEventListener('click', function () { goTo(index() + 1); });

        // Update the counter while scrolling, at most once per screen refresh
        var waiting = false;
        track.addEventListener('scroll', function () {
            if (waiting) {
                return;
            }
            waiting = true;
            window.requestAnimationFrame(function () {
                update();
                waiting = false;
            });
        }, { passive: true });

        update();
    });
})();
