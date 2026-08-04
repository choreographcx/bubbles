/**
 * Custom Facebook Tooltip Manager
 *
 * @since 4.0
 */
'use strict';

var SBITooltipWizard = window.SBITooltipWizard || (function (document, window, $) {

	/**
	 * Public functions and properties.
	 *
	 * @since 4.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 4.0
		 */
		init: function () {

			//$( app.ready );
			$(window).on('load', function () {
				if ($.isFunction($.ready.then)) {
					$.ready.then(app.load);
				} else {
					app.load();
				}
			});
		},


		/**
		 * Window load.
		 *
		 * @since 4.0
		 */
		load: function () {
			if (sbi_admin_tooltip_wizard.sbi_wizard_gutenberg) {
				app.waitForInserterButton(function () {
					app.initGutenbergTooltip();
				});
			}
		},

		waitForInserterButton: function (callback, attempts) {
			attempts = attempts || 0;
			var maxAttempts = 20;
			var found = document.querySelector('.editor-document-tools__inserter-toggle, .edit-post-header-toolbar__inserter-toggle');

			if (found) {
				callback();
				return;
			}

			if (attempts < maxAttempts) {
				setTimeout(function () {
					app.waitForInserterButton(callback, attempts + 1);
				}, 500);
			} else {
				console.error('SBITooltipWizard: Inserter button not found after ' + maxAttempts + ' attempts.');
			}
		},


		initGutenbergTooltip: function () {
			if (typeof $.fn.tooltipster === 'undefined') {
				return;
			}
			var $dot = $('<span class="wpforms-admin-form-embed-wizard-dot">&nbsp;</span>');

			// Support both legacy (< WP 6.8) and current (WP 6.9+) inserter button selectors.
			var $inserterButton = $('.editor-document-tools__inserter-toggle, .edit-post-header-toolbar__inserter-toggle');

			if (!$inserterButton.length) {
				return;
			}

			var tooltipsterArgs = {
				content: $('#sbi-gutenberg-tooltip-content'),
				trigger: 'custom',
				interactive: true,
				animationDuration: 0,
				delay: 0,
				theme: ['tooltipster-default', 'sbi-tooltip-wizard'],
				side: 'bottom',
				distance: 3,
				functionReady: function (instance, helper) {
					instance._$tooltip.on('click', '.sbi-tlp-wizard-close', function () {
						instance.close();
					});

					instance.reposition();
				},
			};

			$inserterButton.on('click', function () {
				$('.sbi-tooltip-wizard.tooltipster-sidetip').hide();
			});

			$dot.insertAfter($inserterButton).tooltipster(tooltipsterArgs).tooltipster('open');
		},

		/**
		 * Check if we're in Gutenberg editor.
		 *
		 * @since 4.0
		 *
		 * @returns {boolean} Is Gutenberg or not.
		 */
		isGutenberg: function () {

			return typeof wp !== 'undefined' && Object.prototype.hasOwnProperty.call(wp, 'blocks');
		},
	}

	return app;
}(document, window, jQuery));

SBITooltipWizard.init();
