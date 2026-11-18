/* =====================================================================
   READ-ONLY MAP (destination detail page)
   Shows where the place is, with the same pin as the admin map picker.
   Nothing can be changed here. On phones the map doesn't grab one-finger
   swipes, so scrolling the page past it still works.
   ===================================================================== */
(function () {
    'use strict';

    var el = document.querySelector('[data-map-view]');

    // Not on this page, or Leaflet didn't load (e.g. offline): the address still shows
    if (!el || typeof L === 'undefined') {
        return;
    }

    var spot = [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)];

    var map = L.map(el, {
        center: spot,
        zoom: 16,
        scrollWheelZoom: false,
        dragging: !L.Browser.mobile,   // phones: swipe scrolls the page, not the map
        tap: false,
        keyboard: false                // the map is a picture here; the address is the real info
    });

    // OpenStreetMap tiles. Their rules require this attribution text.
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Same green and gold pin as the admin map picker
    L.marker(spot, {
        keyboard: false,
        icon: L.divIcon({
            className: 'map-pin',
            html:
                '<svg width="36" height="46" viewBox="0 0 36 46" aria-hidden="true">' +
                '<path d="M18 45s15-15.3 15-27A15 15 0 0 0 3 18c0 11.7 15 27 15 27z" fill="#3f633b" stroke="#fff" stroke-width="2"/>' +
                '<circle cx="18" cy="18" r="6.5" fill="#ea9e30" stroke="#fff" stroke-width="2"/>' +
                '</svg>',
            iconSize: [36, 46],
            iconAnchor: [18, 45]
        })
    }).addTo(map);

    window.addEventListener('load', function () { map.invalidateSize(); });
})();
