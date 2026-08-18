/* Bubbles Pets Rescue front-end helpers */
(function () {
    'use strict';

    // Back to top: appears after scrolling down, smooth-scrolls home.
    var backToTop = document.getElementById('bprBackToTop');
    if (backToTop) {
        var toggle = function () {
            backToTop.classList.toggle('is-visible', window.scrollY > 400);
        };
        window.addEventListener('scroll', toggle, { passive: true });
        toggle();

        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
})();
