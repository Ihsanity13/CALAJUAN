// OPENING HOURS shortcut on the destination form:
// "Copy Monday's hours to every day" ticks every day and copies Monday's times.
// Showing/hiding the time boxes needs no JavaScript (CSS :has does it).
// Without JavaScript the button simply stays hidden.
(function () {
    'use strict';

    var fieldset = document.querySelector('[data-hours]');
    if (!fieldset) return;

    var rows = fieldset.querySelectorAll('[data-hours-row]');
    var button = fieldset.querySelector('[data-hours-copy]');
    if (!button || rows.length === 0) return;

    // The first row is Monday (the form lists Monday first)
    var monday = rows[0];

    button.hidden = false;

    button.addEventListener('click', function () {
        var mondayOpen = monday.querySelector('[data-hours-open]').checked;
        var opens = monday.querySelector('[data-hours-opens]').value;
        var closes = monday.querySelector('[data-hours-closes]').value;

        rows.forEach(function (row) {
            row.querySelector('[data-hours-open]').checked = mondayOpen;
            row.querySelector('[data-hours-opens]').value = opens;
            row.querySelector('[data-hours-closes]').value = closes;
        });

        // Tell screen reader users what happened (aria-live region)
        var status = fieldset.querySelector('[data-hours-status]');
        if (status) status.textContent = 'Monday\'s hours were copied to every day.';
    });
})();
