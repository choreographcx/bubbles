'use strict';

/**
 * Bridges the legacy SBI_Elementor_Widget (registered as `sbi-widget`) to the
 * Elementor frontend element-ready hook so sbi_init() runs inside the
 * preview iframe when the legacy widget mounts.
 *
 * The framework's sb-elementor-editor.js registers the same hook for the
 * modern widget name (sb-instagram-feed). Without this file, a page that
 * has only the legacy widget never triggers sbi_init() in the preview.
 *
 * Loaded only inside Elementor preview by SBI_Elementor_Base::enqueue_preview_feed_assets().
 */
(function () {
	var bind = function () {
		if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
			return setTimeout(bind, 250);
		}
		window.elementorFrontend.hooks.addAction(
			'frontend/element_ready/sbi-widget.default',
			function () {
				if (typeof window.sbi_init === 'function') {
					window.sbi_init();
				}
			}
		);
	};
	bind();
})();
