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

/* Species filter on the Meet the Animals page. */
(function () {
    'use strict';

    var filter = document.getElementById('bprSpeciesFilter');
    if (!filter) { return; }

    var chips = filter.querySelectorAll('[data-species-filter]');
    var cards = document.querySelectorAll('[data-species]');
    var sections = document.querySelectorAll('[data-pet-section]');
    var emptyMessage = document.querySelector('[data-filter-empty]');

    var apply = function (species) {
        chips.forEach(function (chip) {
            chip.classList.toggle('is-active', chip.getAttribute('data-species-filter') === species);
        });
        cards.forEach(function (card) {
            card.classList.toggle('d-none', species !== 'all' && card.getAttribute('data-species') !== species);
        });
        var anyVisible = false;
        sections.forEach(function (section) {
            var visible = section.querySelectorAll('[data-species]:not(.d-none)').length > 0;
            section.classList.toggle('d-none', !visible);
            anyVisible = anyVisible || visible;
        });
        if (emptyMessage) {
            emptyMessage.classList.toggle('d-none', anyVisible);
        }
    };

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            apply(chip.getAttribute('data-species-filter'));
        });
    });

    var preset = new URLSearchParams(window.location.search).get('species');
    if (preset === 'dog' || preset === 'cat') {
        apply(preset);
    }
})();
