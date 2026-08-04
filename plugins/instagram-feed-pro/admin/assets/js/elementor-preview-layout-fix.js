/**
 * Instagram Feed - Elementor editor preview masonry/highlight recompute.
 *
 * In the Chromium preview iframe the masonry/highlight engine (smashotope)
 * runs before the iframe is sized, so it measures the container at ~0 width and
 * collapses #sbi_images to height:24px. Firefox / the live frontend are fine.
 *
 * Two things keep it collapsed and must both be undone on the live node:
 *   1. the failed layout pins an inline height:24px on #sbi_images;
 *   2. the base stylesheet leaves #sbi_images as display:grid, which fights the
 *      engine's float/absolute layout model.
 * After clearing both we re-run the REAL layout (reloadItems + layout) on the
 * existing engine instance -- NOT a CSS substitute -- so it recalculates from
 * the correct width and produces the true masonry. We deliberately avoid
 * destroy()+re-init: that re-triggers imagesloaded against now-detached nodes
 * and throws inside the vendor lib.
 *
 * Every feed reuses id="sb_instagram"/id="sbi_images", so we never look up by
 * id: feeds are selected by CLASS and each container's own #sbi_images is taken
 * with an element-scoped querySelector. A container that is already taller than
 * 50px is skipped, so once a feed lays out the MutationObserver stops touching
 * it; and because we only reloadItems/layout (never destroy), any repeat pass
 * is cheap and cannot re-trigger the imagesloaded crash.
 *
 * Chromium-only (Firefox is fine) and preview-only (enqueued on
 * elementor/preview/enqueue_scripts), so the live frontend is untouched.
 */
( function ( $ ) {
	// Chromium-based only (Chrome/Edge/Opera). Firefox renders the preview
	// masonry fine, so we must NOT touch it. A UA test is more reliable than
	// the non-standard window.chrome (undefined in some headless Chromium).
	if ( ! ( /Chrome\//.test( navigator.userAgent ) || /Edg\//.test( navigator.userAgent ) ) ) {
		return;
	}

	// Engine collapses a failed layout to ~24px; anything taller is healthy.
	var SBI_COLLAPSED_HEIGHT_PX = 50;

	// Mirror the plugin's getColumnCount() responsive logic.
	var colCount = function ( $sbi ) {
		var w = $( window ).width();
		var d = parseInt( $sbi.attr( 'data-cols' ), 10 ) || 4;
		var t = parseInt( $sbi.attr( 'data-colstablet' ), 10 ) || d;
		var m = parseInt( $sbi.attr( 'data-colsmobile' ), 10 ) || 1;
		if ( w < 480 ) { return m; }
		if ( w < 640 ) { return t; }
		return d;
	};

	// Re-run the real layout on one feed's live #sbi_images.
	var recompute = function ( $images, $sbi ) {
		var images = $images.get( 0 );

		// Undo the two things that keep the preview collapsed.
		images.style.height = '';
		images.style.minHeight = '';
		images.style.display = 'block';

		if ( ! $.fn.smashotope ) { return; }

		if ( $images.data( 'smashotope' ) ) {
			// Existing instance: just force a re-measure + re-pack now that the
			// iframe is sized and images are loaded.
			try {
				$images.smashotope( 'reloadItems' );
				$images.smashotope( 'layout' );
			} catch ( e ) {}
			return;
		}

		// No instance yet (e.g. a freshly re-rendered block): create one with
		// the plugin's own packery args.
		var isHighlight = $sbi.hasClass( 'sbi_highlight' );
		var firstItem = images.querySelector( '.sbi_item' );
		var cols = colCount( $sbi );
		var colW = isHighlight
			? ( images.clientWidth / cols )
			: ( firstItem ? firstItem.offsetWidth : ( images.clientWidth / cols ) );
		try {
			$images.smashotope( {
				itemSelector: '.sbi_item',
				layoutMode: 'packery',
				transitionDuration: 0,
				resizable: false,
				masonry: { columnWidth: colW }
			} );
			$images.smashotope( 'layout' );
		} catch ( e ) {}
	};

	// Heal one container if it is collapsed (once per container).
	var heal = function ( sbi ) {
		var images = sbi.querySelector( '[id="sbi_images"]' );
		if ( ! images ) { return; }
		var $images = $( images );
		if ( $images.height() > SBI_COLLAPSED_HEIGHT_PX ) { return; } // already laid out

		var $sbi = $( sbi );
		recompute( $images, $sbi );

		// Backstop: if any image was still loading, recompute when it finishes.
		$images.find( 'img' ).each( function () {
			if ( ! this.complete ) {
				this.addEventListener( 'load', function () {
					try {
						$images.smashotope( 'reloadItems' );
						$images.smashotope( 'layout' );
					} catch ( e ) {}
				}, { once: true } );
			}
		} );
	};

	var healAll = function () {
		var nodes = document.querySelectorAll( '.sbi.sbi_masonry, .sbi.sbi_highlight' );
		for ( var i = 0; i < nodes.length; i++ ) {
			heal( nodes[ i ] );
		}
	};

	var start = function () {
		healAll();
		setTimeout( healAll, 500 );
		setTimeout( healAll, 1500 );

		if ( window.MutationObserver && document.body ) {
			var t;
			// Intentionally left connected for the whole editor session: new
			// feeds can be dragged in and the modern block re-renders (SSR) at
			// any time, and this observer is what re-heals them. The height
			// guard in heal() makes each pass near-free, so there is nothing to
			// gain by disconnecting.
			var mo = new MutationObserver( function () {
				clearTimeout( t );
				t = setTimeout( healAll, 250 );
			} );
			mo.observe( document.body, { childList: true, subtree: true } );
		}

		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			elementorFrontend.hooks.addAction( 'frontend/element_ready/sb-instagram-feed.default', function () {
				setTimeout( healAll, 100 );
			} );
			elementorFrontend.hooks.addAction( 'frontend/element_ready/sbi-widget.default', function () {
				setTimeout( healAll, 100 );
			} );
		}
	};

	$( window ).on( 'load', healAll );
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )( jQuery );
