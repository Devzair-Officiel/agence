/* admin.js — Devzair Administration (Phase 9D)
 * Minimal vanilla JS. No frameworks, no inline handlers (CSP: script-src 'self').
 * Single responsibility: graceful fallback for broken media thumbnails.
 */
(function () {
    'use strict';

    document.addEventListener('error', function (e) {
        var img = e.target;
        if (img.tagName !== 'IMG' || !img.classList.contains('admin-media-card__image')) {
            return;
        }
        img.classList.add('admin-media-card__image--broken');
        img.removeAttribute('src');
    }, true);
}());
