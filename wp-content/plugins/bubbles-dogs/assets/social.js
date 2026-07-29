/**
 * The "Share this dog" box.
 *
 * Posting to the live accounts is irreversible from here, so the flow is
 * deliberately a little slow: pick accounts, read the caption, confirm, and
 * confirm again if this dog has already gone out to that account.
 */
( function () {
	'use strict';

	var config = window.bprDogsSocial || {};
	var strings = config.strings || {};

	document.addEventListener( 'DOMContentLoaded', function () {
		var box = document.querySelector( '.bpr-share' );

		if ( ! box ) {
			return;
		}

		var postId = box.getAttribute( 'data-post-id' );
		var caption = box.querySelector( '.bpr-share__caption' );
		var goButton = box.querySelector( '.bpr-share__go' );
		var resetButton = box.querySelector( '.bpr-share__reset' );
		var spinner = box.querySelector( '.bpr-share__spinner' );
		var result = box.querySelector( '.bpr-share__result' );
		var history = box.querySelector( '.bpr-share__history' );

		/**
		 * Currently ticked platforms.
		 *
		 * @return {Array} Checkbox elements.
		 */
		function checkedPlatforms() {
			return Array.prototype.slice
				.call( box.querySelectorAll( '.bpr-share__platform' ) )
				.filter( function ( input ) {
					return input.checked && ! input.disabled;
				} );
		}

		/**
		 * Replace the result area with a list of messages.
		 *
		 * @param {Array} messages Objects with ok, message and permalink.
		 */
		function showResults( messages ) {
			result.innerHTML = '';

			messages.forEach( function ( item ) {
				var line = document.createElement( 'p' );
				line.className = item.ok
					? 'bpr-share__msg bpr-share__msg--ok'
					: 'bpr-share__msg bpr-share__msg--bad';
				line.textContent = item.message;

				if ( item.ok && item.permalink ) {
					line.appendChild( document.createTextNode( ' ' ) );
					var link = document.createElement( 'a' );
					link.href = item.permalink;
					link.target = '_blank';
					link.rel = 'noopener noreferrer';
					link.textContent = strings.view || 'View post';
					line.appendChild( link );
				}

				result.appendChild( line );
			} );
		}

		/**
		 * Show a single error message.
		 *
		 * @param {string} message Text to show.
		 */
		function showError( message ) {
			showResults( [ { ok: false, message: message } ] );
		}

		function setBusy( busy ) {
			goButton.disabled = busy;
			spinner.classList.toggle( 'is-active', busy );
		}

		goButton.addEventListener( 'click', function () {
			var selected = checkedPlatforms();

			if ( ! selected.length ) {
				showError( strings.pickOne || 'Choose at least one account.' );
				return;
			}

			var labels = selected.map( function ( input ) {
				return input.getAttribute( 'data-label' );
			} );

			// Warn separately about anything already posted — accidentally
			// double-posting a dog is the mistake worth guarding hardest.
			var repeats = selected.filter( function ( input ) {
				return '1' === input.getAttribute( 'data-posted' );
			} );

			if ( repeats.length ) {
				var repeatLabels = repeats
					.map( function ( input ) {
						return input.getAttribute( 'data-label' );
					} )
					.join( ', ' );

				var repeatMessage = ( strings.confirmAgain || 'Already posted to %1$s. Post to %2$s again?' )
					.replace( '%1$s', repeatLabels )
					.replace( '%2$s', repeatLabels );

				if ( ! window.confirm( repeatMessage ) ) {
					return;
				}
			} else if (
				! window.confirm(
					( strings.confirm || 'Post to %s now?' ).replace( '%s', labels.join( ' and ' ) )
				)
			) {
				return;
			}

			var body = new URLSearchParams();
			body.append( 'action', config.action );
			body.append( 'nonce', config.nonce );
			body.append( 'post_id', postId );
			body.append( 'caption', caption ? caption.value : '' );

			selected.forEach( function ( input ) {
				body.append( 'platforms[]', input.value );
			} );

			setBusy( true );
			result.innerHTML = '';

			window
				.fetch( config.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: body,
				} )
				.then( function ( response ) {
					return response.json().catch( function () {
						throw new Error( strings.failed || 'Something went wrong.' );
					} );
				} )
				.then( function ( payload ) {
					if ( ! payload || ! payload.success ) {
						var message =
							payload && payload.data && payload.data.message
								? payload.data.message
								: strings.failed || 'Something went wrong.';
						showError( message );
						return;
					}

					showResults( payload.data.results || [] );

					if ( payload.data.historyHtml && history ) {
						history.innerHTML = payload.data.historyHtml;
					}

					// Anything that succeeded is now a repeat, and gets unticked
					// so a second click can't quietly post it twice.
					( payload.data.results || [] ).forEach( function ( item ) {
						if ( ! item.ok ) {
							return;
						}
						var input = box.querySelector(
							'.bpr-share__platform[value="' + item.platform + '"]'
						);
						if ( input ) {
							input.setAttribute( 'data-posted', '1' );
							input.checked = false;
						}
					} );
				} )
				.catch( function ( error ) {
					showError( error.message || strings.failed || 'Something went wrong.' );
				} )
				.then( function () {
					setBusy( false );
				} );
		} );

		if ( resetButton && caption ) {
			resetButton.addEventListener( 'click', function () {
				var body = new URLSearchParams();
				body.append( 'action', 'bpr_dogs_preview_caption' );
				body.append( 'nonce', config.nonce );
				body.append( 'post_id', postId );
				body.append( 'platform', 'facebook' );

				setBusy( true );

				window
					.fetch( config.ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						body: body,
					} )
					.then( function ( response ) {
						return response.json();
					} )
					.then( function ( payload ) {
						if ( payload && payload.success && payload.data ) {
							caption.value = payload.data.caption;
						}
					} )
					.catch( function () {
						// Nothing posted, nothing lost — leave the caption alone.
					} )
					.then( function () {
						setBusy( false );
					} );
			} );
		}
	} );
} )();
