<?php
/**
 * The "Share this dog" box on the dog editor.
 *
 * Posting is always deliberate: pick the accounts, check the caption, press the
 * button. There is no hook here that posts anything on save or on publish.
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Share meta box and its AJAX handler.
 */
class BPR_Dogs_Social_Admin {

	const AJAX_ACTION = 'bpr_dogs_share';
	const NONCE       = 'bpr_dogs_share_nonce';

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'handle_share' ) );
		add_action( 'wp_ajax_bpr_dogs_preview_caption', array( __CLASS__, 'handle_preview' ) );
	}

	/**
	 * Register the box, unless nothing is configured yet.
	 */
	public static function add_meta_box() {
		add_meta_box(
			'bpr-dog-social',
			__( 'Share this dog', 'bubbles-dogs' ),
			array( __CLASS__, 'render' ),
			BPR_Dogs_Post_Type::POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Load the script on the dog editor.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || BPR_Dogs_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'bpr-dogs-social',
			BPR_DOGS_URL . 'assets/social.js',
			array( 'jquery' ),
			BPR_DOGS_VERSION,
			true
		);

		wp_localize_script(
			'bpr-dogs-social',
			'bprDogsSocial',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'action'   => self::AJAX_ACTION,
				'nonce'    => wp_create_nonce( self::NONCE ),
				'strings'  => array(
					'posting'     => __( 'Posting…', 'bubbles-dogs' ),
					'pickOne'     => __( 'Choose at least one account to post to.', 'bubbles-dogs' ),
					'confirm'     => __( 'Post %s now? This publishes to the live accounts straight away.', 'bubbles-dogs' ),
					'confirmAgain' => __( 'This dog has already been posted to %1$s. Post to %2$s again anyway?', 'bubbles-dogs' ),
					'failed'      => __( 'Something went wrong and nothing was posted.', 'bubbles-dogs' ),
					'view'        => __( 'View post', 'bubbles-dogs' ),
				),
			)
		);
	}

	/**
	 * Render the box.
	 *
	 * @param WP_Post $post Current dog.
	 */
	public static function render( $post ) {
		$configured = array_filter(
			array_keys( BPR_Dogs_Social::platforms() ),
			array( 'BPR_Dogs_Social', 'is_configured' )
		);

		if ( empty( $configured ) ) {
			printf(
				'<p>%s</p><p><a href="%s">%s</a></p>',
				esc_html__( 'Facebook and Instagram sharing are not set up yet.', 'bubbles-dogs' ),
				esc_url( admin_url( 'edit.php?post_type=' . BPR_Dogs_Post_Type::POST_TYPE . '&page=bpr-dogs-settings' ) ),
				esc_html__( 'Add the account details', 'bubbles-dogs' )
			);
			return;
		}

		$history   = BPR_Dogs_Social::history( $post->ID );
		$platforms = BPR_Dogs_Social::platforms();
		$published = 'publish' === $post->post_status;
		$images    = BPR_Dogs_Social::image_ids( $post->ID );

		// The nonce for sharing travels with the AJAX request via
		// wp_localize_script, so there is deliberately no nonce field here —
		// sharing is not part of the post form.
		?>
		<div class="bpr-share" data-post-id="<?php echo esc_attr( (string) $post->ID ); ?>">

			<?php if ( ! $published ) : ?>
				<p class="bpr-share__blocked">
					<?php esc_html_e( 'Publish this dog before sharing — the post links back to their page.', 'bubbles-dogs' ); ?>
				</p>
			<?php endif; ?>

			<p class="bpr-share__where"><strong><?php esc_html_e( 'Post to', 'bubbles-dogs' ); ?></strong></p>

			<?php foreach ( $configured as $platform ) : ?>
				<?php
				$already = isset( $history[ $platform ] );
				$needs_image = 'instagram' === $platform && empty( $images );
				?>
				<p class="bpr-share__option">
					<label>
						<input type="checkbox" class="bpr-share__platform"
							value="<?php echo esc_attr( $platform ); ?>"
							data-label="<?php echo esc_attr( $platforms[ $platform ] ); ?>"
							data-posted="<?php echo $already ? '1' : '0'; ?>"
							<?php disabled( ! $published || $needs_image ); ?>
							<?php checked( ! $already && $published && ! $needs_image ); ?> />
						<?php echo esc_html( $platforms[ $platform ] ); ?>
					</label>

					<?php if ( $needs_image ) : ?>
						<span class="bpr-share__note">
							<?php esc_html_e( 'Needs a photo', 'bubbles-dogs' ); ?>
						</span>
					<?php endif; ?>
				</p>
			<?php endforeach; ?>

			<?php if ( count( $images ) > 1 ) : ?>
				<p class="bpr-share__note">
					<?php
					printf(
						/* translators: %d: number of photos. */
						esc_html( _n( 'Posting %d photo.', 'Posting %d photos as a carousel.', count( $images ), 'bubbles-dogs' ) ),
						count( $images )
					);
					?>
				</p>
			<?php endif; ?>

			<p>
				<label for="bpr-share-caption"><strong><?php esc_html_e( 'Caption', 'bubbles-dogs' ); ?></strong></label>
				<textarea id="bpr-share-caption" class="widefat bpr-share__caption" rows="10"><?php
					echo esc_textarea( BPR_Dogs_Social::build_caption( $post->ID, 'facebook' ) );
				?></textarea>
			</p>

			<p class="bpr-share__note bpr-share__caption-note">
				<?php
				esc_html_e(
					'Built from your template. Edit it here and this exact text is what goes out — to both accounts, if you pick both.',
					'bubbles-dogs'
				);
				?>
			</p>

			<p class="bpr-share__actions">
				<button type="button" class="button button-primary bpr-share__go" <?php disabled( ! $published ); ?>>
					<?php esc_html_e( 'Post now', 'bubbles-dogs' ); ?>
				</button>
				<button type="button" class="button-link bpr-share__reset">
					<?php esc_html_e( 'Reset caption', 'bubbles-dogs' ); ?>
				</button>
				<span class="spinner bpr-share__spinner"></span>
			</p>

			<div class="bpr-share__result" aria-live="polite"></div>

			<div class="bpr-share__history">
				<?php self::render_history( $post->ID ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the "already posted" list.
	 *
	 * @param int $post_id Dog post ID.
	 */
	public static function render_history( $post_id ) {
		$history   = BPR_Dogs_Social::history( $post_id );
		$platforms = BPR_Dogs_Social::platforms();

		if ( empty( $history ) ) {
			return;
		}

		echo '<p class="bpr-share__where"><strong>' . esc_html__( 'Already posted', 'bubbles-dogs' ) . '</strong></p>';
		echo '<ul class="bpr-share__log">';

		foreach ( $history as $platform => $entry ) {
			$label = isset( $platforms[ $platform ] ) ? $platforms[ $platform ] : $platform;
			$time  = ! empty( $entry['time'] ) ? mysql2date( get_option( 'date_format' ), $entry['time'] ) : '';

			echo '<li>';
			echo '<strong>' . esc_html( $label ) . '</strong>';

			if ( '' !== $time ) {
				echo ' <span class="bpr-share__note">' . esc_html( $time ) . '</span>';
			}

			if ( ! empty( $entry['permalink'] ) ) {
				printf(
					' <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
					esc_url( $entry['permalink'] ),
					esc_html__( 'View', 'bubbles-dogs' )
				);
			}

			echo '</li>';
		}

		echo '</ul>';
	}

	/**
	 * Rebuild the caption for a platform, for the Reset button.
	 */
	public static function handle_preview() {
		check_ajax_referer( self::NONCE, 'nonce' );

		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$platform = isset( $_POST['platform'] ) ? sanitize_key( wp_unslash( $_POST['platform'] ) ) : 'facebook';

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You cannot edit this dog.', 'bubbles-dogs' ) ), 403 );
		}

		if ( ! array_key_exists( $platform, BPR_Dogs_Social::platforms() ) ) {
			$platform = 'facebook';
		}

		wp_send_json_success( array( 'caption' => BPR_Dogs_Social::build_caption( $post_id, $platform ) ) );
	}

	/**
	 * Do the sharing.
	 *
	 * Each platform is attempted independently and reported on separately, so
	 * one failing doesn't hide the other succeeding.
	 */
	public static function handle_share() {
		check_ajax_referer( self::NONCE, 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id || BPR_Dogs_Post_Type::POST_TYPE !== get_post_type( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'That is not a dog.', 'bubbles-dogs' ) ), 400 );
		}

		// Posting to the rescue's public accounts is a publishing action, so it
		// needs publish rights on the dog, not merely edit rights.
		if ( ! current_user_can( 'edit_post', $post_id ) || ! current_user_can( 'publish_posts' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to post to the rescue accounts.', 'bubbles-dogs' ) ),
				403
			);
		}

		$requested = isset( $_POST['platforms'] ) ? (array) wp_unslash( $_POST['platforms'] ) : array();
		$requested = array_values(
			array_intersect(
				array_map( 'sanitize_key', $requested ),
				array_keys( BPR_Dogs_Social::platforms() )
			)
		);

		if ( empty( $requested ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Choose at least one account to post to.', 'bubbles-dogs' ) ),
				400
			);
		}

		$caption = isset( $_POST['caption'] )
			? sanitize_textarea_field( wp_unslash( $_POST['caption'] ) )
			: '';

		$results = array();
		$any_ok  = false;

		foreach ( $requested as $platform ) {
			$result = BPR_Dogs_Social::share( $post_id, $platform, $caption );

			if ( is_wp_error( $result ) ) {
				$results[] = array(
					'platform' => $platform,
					'ok'       => false,
					'message'  => $result->get_error_message(),
				);
				continue;
			}

			$any_ok    = true;
			$results[] = array(
				'platform'  => $platform,
				'ok'        => true,
				'permalink' => $result['permalink'],
				'message'   => sprintf(
					/* translators: %s: platform name. */
					__( 'Posted to %s.', 'bubbles-dogs' ),
					BPR_Dogs_Social::platforms()[ $platform ]
				),
			);
		}

		ob_start();
		self::render_history( $post_id );
		$history_html = (string) ob_get_clean();

		wp_send_json_success(
			array(
				'results'     => $results,
				'anySuccess'  => $any_ok,
				'historyHtml' => $history_html,
			)
		);
	}
}
