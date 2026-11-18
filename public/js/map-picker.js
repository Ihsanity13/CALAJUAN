/* =====================================================================
   MAP PICKER (admin destination form)
   - Click the map to drop a pin; click again or drag the pin to move it.
   - The pin's position is copied into the hidden latitude/longitude inputs,
     which are what the form sends to Laravel.
   - "Use my location" asks the browser for the phone's GPS position.
   - After each new pin, the street address is looked up and put in the
     Address field (only if it's empty or was filled in by us before).
   - Submitting without a pin shows an error under the map instead.
   Uses Leaflet (loaded from its CDN just before this file) and
   OpenStreetMap tiles.
   ===================================================================== */
(function () {
    'use strict';

    var root = document.querySelector('[data-map-picker]');

    // Not on this page, or Leaflet didn't load (e.g. offline): do nothing
    if (!root || typeof L === 'undefined') {
        return;
    }

    var latInput = root.querySelector('[data-lat]');
    var lngInput = root.querySelector('[data-lng]');
    var mapEl = root.querySelector('[data-map]');
    var coordsEl = root.querySelector('[data-coords]');
    var locateBtn = root.querySelector('[data-locate]');
    var errorEl = root.querySelector('[data-location-error]');
    var errorText = root.querySelector('[data-location-error-text]');
    var form = root.closest('form');

    var DEFAULT_ERROR = 'Drop a pin on the map to set the location.';

    // ---- The map ----
    var defaultCenter = [
        parseFloat(mapEl.dataset.defaultLat),
        parseFloat(mapEl.dataset.defaultLng)
    ];
    var defaultZoom = parseInt(mapEl.dataset.defaultZoom, 10);

    var map = L.map(mapEl, {
        center: defaultCenter,
        zoom: defaultZoom,
        scrollWheelZoom: false   // so scrolling the page doesn't zoom the map by accident
    });

    // OpenStreetMap tiles. Their rules require this attribution text.
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Turn wheel-zoom on only after the admin clicks into the map
    map.once('focus', function () { map.scrollWheelZoom.enable(); });

    // ---- The pin: a pineapple-green marker drawn with SVG ----
    var pinIcon = L.divIcon({
        className: 'map-pin',
        html:
            '<svg width="36" height="46" viewBox="0 0 36 46" aria-hidden="true">' +
            '<path d="M18 45s15-15.3 15-27A15 15 0 0 0 3 18c0 11.7 15 27 15 27z" fill="#3f633b" stroke="#fff" stroke-width="2"/>' +
            '<circle cx="18" cy="18" r="6.5" fill="#ea9e30" stroke="#fff" stroke-width="2"/>' +
            '</svg>',
        iconSize: [36, 46],
        iconAnchor: [18, 45]     // the tip of the pin sits on the exact spot
    });

    var marker = null;

    // Put the pin at a spot (or move it there) and update everything that shows it
    function setPin(latlng, options) {
        options = options || {};

        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng, {
                icon: pinIcon,
                draggable: true,
                keyboard: false,
                title: 'Destination pin. Drag to adjust.'
            }).addTo(map);

            // Dragging the pin updates the inputs when you let go
            marker.on('dragend', function () {
                setPin(marker.getLatLng());
            });
        }

        // 7 decimals = what the database stores (about 1 cm accuracy)
        latInput.value = latlng.lat.toFixed(7);
        lngInput.value = latlng.lng.toFixed(7);
        coordsEl.textContent = 'Pin at ' + latlng.lat.toFixed(5) + ', ' + latlng.lng.toFixed(5);

        hideError();

        if (options.pan) {
            map.setView(latlng, options.zoom || Math.max(map.getZoom(), 16));
        }

        // Look up the street address for the new spot (not for the saved pin on page load)
        if (options.lookup !== false) {
            lookupAddress(latlng);
        }
    }

    /* -----------------------------------------------------------------
       ADDRESS LOOKUP ("reverse geocoding")
       Asks OpenStreetMap's free Nominatim service which address is at
       the pin. Their rules: at most 1 request per second, so we wait
       until the pin has stopped moving, and cancel any older request.
       ----------------------------------------------------------------- */
    var addressInput = form ? form.querySelector('#address') : null;
    var lookupBox = document.querySelector('[data-address-lookup]');
    var lookupText = document.querySelector('[data-address-lookup-text]');
    var useAddressBtn = document.querySelector('[data-address-use]');

    var lookupTimer = null;
    var lookupRequest = null;   // lets us cancel a request that's still running
    var foundAddress = '';
    var lastAutoFilled = null;  // the address WE put in the box (safe to replace)

    function showLookup(message, offerButton) {
        lookupText.textContent = message;
        useAddressBtn.hidden = !offerButton;
        lookupBox.hidden = false;
    }

    function hideLookup() {
        lookupBox.hidden = true;
    }

    function lookupAddress(latlng) {
        if (!addressInput || !lookupBox || !window.fetch) {
            return;
        }

        showLookup('Looking up the address…', false);
        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(function () { fetchAddress(latlng); }, 700);
    }

    function fetchAddress(latlng) {
        if (lookupRequest) {
            lookupRequest.abort();
        }
        lookupRequest = new AbortController();

        var url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&zoom=18' +
            '&accept-language=en&lat=' + latlng.lat.toFixed(7) + '&lon=' + latlng.lng.toFixed(7);

        fetch(url, { signal: lookupRequest.signal })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Lookup failed');
                }
                return response.json();
            })
            .then(function (data) {
                var address = formatAddress(data);

                if (!address) {
                    showLookup('No address found for this spot. You can type one.', false);
                    return;
                }

                foundAddress = address;
                var current = addressInput.value.trim();

                if (current === '' || current === lastAutoFilled) {
                    // Empty, or still our own earlier suggestion: fill it in
                    addressInput.value = address;
                    lastAutoFilled = address;
                    showLookup('Address filled in from the pin.', false);
                } else if (current === address) {
                    hideLookup();
                } else {
                    // The admin typed their own address: never overwrite it, just offer
                    showLookup('Address at the pin: ' + address, true);
                }
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;   // a newer pin replaced this request; ignore
                }
                showLookup('The address could not be looked up right now. You can type it.', false);
            });
    }

    // Build a short address like "Don Andres Soriano Avenue, Mabacan, Calauan, Laguna"
    // instead of the very long full name OpenStreetMap returns.
    // In the Philippines, OpenStreetMap stores the barangay as "village"
    // and smaller areas (sitio, zone, Poblacion) as "quarter"/"neighbourhood".
    function formatAddress(data) {
        var a = (data && data.address) || {};
        var street = [a.house_number, a.road].filter(Boolean).join(' ');
        var parts = [
            street,
            a.neighbourhood || a.hamlet || a.quarter,
            a.village || a.suburb,
            a.town || a.city || a.municipality,
            a.province || a.state || a.county
        ];

        // Remove empty and repeated parts
        var seen = {};
        parts = parts.filter(function (part) {
            if (!part || seen[part.toLowerCase()]) {
                return false;
            }
            seen[part.toLowerCase()] = true;
            return true;
        });

        var text = parts.length >= 2 ? parts.join(', ') : (data && data.display_name) || '';
        return text.slice(0, 255);   // the database column holds 255 characters
    }

    if (useAddressBtn) {
        useAddressBtn.addEventListener('click', function () {
            addressInput.value = foundAddress;
            lastAutoFilled = foundAddress;
            showLookup('Address updated from the pin.', false);
        });
    }

    function showError(message) {
        errorText.textContent = message || DEFAULT_ERROR;
        errorEl.hidden = false;
        mapEl.classList.add('is-invalid');
    }

    function hideError() {
        errorEl.hidden = true;
        mapEl.classList.remove('is-invalid');
    }

    // ---- Editing (or the form came back with errors): show the saved pin ----
    var savedLat = parseFloat(latInput.value);
    var savedLng = parseFloat(lngInput.value);

    if (!isNaN(savedLat) && !isNaN(savedLng)) {
        // lookup: false, so opening the edit page doesn't replace the saved address
        setPin(L.latLng(savedLat, savedLng), { pan: true, zoom: 16, lookup: false });
    }

    // ---- Click the map: drop or move the pin ----
    map.on('click', function (event) {
        setPin(event.latlng);
    });

    // ---- Keyboard: Enter or Space drops the pin in the middle of the map ----
    mapEl.addEventListener('keydown', function (event) {
        if (event.target !== mapEl) {
            return;   // don't hijack keys on the zoom buttons or links inside the map
        }
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            setPin(map.getCenter());
        }
    });

    // ---- "Use my location" (needs https or localhost; phones ask permission) ----
    if (!('geolocation' in navigator)) {
        locateBtn.hidden = true;
    } else {
        var buttonLabel = locateBtn.innerHTML;

        locateBtn.addEventListener('click', function () {
            locateBtn.disabled = true;
            locateBtn.textContent = 'Finding you…';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    setPin(
                        L.latLng(position.coords.latitude, position.coords.longitude),
                        { pan: true, zoom: 17 }
                    );
                    locateBtn.disabled = false;
                    locateBtn.innerHTML = buttonLabel;
                },
                function (error) {
                    var messages = {
                        1: 'Location permission was denied. Allow it in your browser, or click the map instead.',
                        2: 'Your location could not be found. Click the map instead.',
                        3: 'Finding your location took too long. Try again, or click the map instead.'
                    };
                    showError(messages[error.code] || messages[2]);
                    locateBtn.disabled = false;
                    locateBtn.innerHTML = buttonLabel;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    }

    // ---- Submitting without a pin: stop, and show the error under the map ----
    if (form) {
        form.addEventListener('submit', function (event) {
            if (latInput.value === '' || lngInput.value === '') {
                event.preventDefault();
                showError(DEFAULT_ERROR);
                var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                mapEl.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
                mapEl.focus({ preventScroll: true });
            }
        });
    }

    // The map sometimes measures its box before the page finishes laying out;
    // this makes it redraw at the right size.
    window.addEventListener('load', function () { map.invalidateSize(); });
})();
