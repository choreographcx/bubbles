<?php
/**
 * Admin screens for dogs: meta boxes, saving, list columns and filters.
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Everything that only matters inside wp-admin.
 */
class BPR_Dogs_Admin {

	const NONCE_ACTION = 'bpr_dogs_save_details';
	const NONCE_NAME   = 'bpr_dogs_nonce';

	/**
	 * Meta box groups, in the order they appear.
	 *
	 * @return array<string,string> Group key => box title.
	 */
	private static function groups() {
		return array(
			'about'     => __( 'About this dog', 'bubbles-dogs' ),
			'health'    => __( 'Health', 'bubbles-dogs' ),
			'behaviour' => __( 'Temperament', 'bubbles-dogs' ),
			'admin'     => __( 'Rescue admin (not shown publicly)', 'bubbles-dogs' ),
		);
	}

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . BPR_Dogs_Post_Type::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );

		$pt = BPR_Dogs_Post_Type::POST_TYPE;
		add_filter( "manage_{$pt}_posts_columns", array( __CLASS__, 'columns' ) );
		add_action( "manage_{$pt}_posts_custom_column", array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( "manage_edit-{$pt}_sortable_columns", array( __CLASS__, 'sortable_columns' ) );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'status_filter_dropdown' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'apply_admin_filters' ) );
	}

	/**
	 * Load the admin stylesheet on dog edit and list screens only.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || BPR_Dogs_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'bpr-dogs-admin',
			BPR_DOGS_URL . 'assets/admin.css',
			array(),
			BPR_DOGS_VERSION
		);
	}

	/**
	 * Register the meta boxes.
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			'bpr-dog-status',
			__( 'Adoption status', 'bubbles-dogs' ),
			array( __CLASS__, 'render_status_box' ),
			BPR_Dogs_Post_Type::POST_TYPE,
			'side',
			'high'
		);

		foreach ( self::groups() as $group => $title ) {
			add_meta_box(
				'bpr-dog-' . $group,
				$title,
				array( __CLASS__, 'render_group_box' ),
				BPR_Dogs_Post_Type::POST_TYPE,
				'normal',
				'default',
				array( 'group' => $group )
			);
		}
	}

	/**
	 * The status box. Kept separate and in the sidebar because it is the field
	 * that changes most often and matters most.
	 *
	 * @param WP_Post $post Current dog.
	 */
	public static function render_status_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$current  = BPR_Dogs_Fields::get_status( $post->ID );
		$statuses = BPR_Dogs_Fields::statuses();
		?>
		<p>
			<label class="screen-reader-text" for="bpr_dog_status">
				<?php esc_html_e( 'Adoption status', 'bubbles-dogs' ); ?>
			</label>
			<select name="bpr_dog_status" id="bpr_dog_status" class="widefat">
				<?php foreach ( $statuses as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="description">
			<?php
			esc_html_e(
				'Dogs marked "Adopted" drop off the Adopt a dog listing but keep their page, so old links and shares still work. Show them on a Happy endings page with the [bubbles_dogs status="adopted"] shortcode.',
				'bubbles-dogs'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render one group of fields.
	 *
	 * @param WP_Post              $post Current dog.
	 * @param array<string,mixed>  $box  Meta box args.
	 */
	public static function render_group_box( $post, $box ) {
		$group  = isset( $box['args']['group'] ) ? $box['args']['group'] : '';
		$fields = BPR_Dogs_Fields::by_group( $group );

		if ( empty( $fields ) ) {
			return;
		}

		echo '<table class="form-table bpr-dogs-form" role="presentation"><tbody>';

		foreach ( $fields as $field ) {
			$name  = 'bpr_dog_' . $field['key'];
			$value = BPR_Dogs_Fields::get( $post->ID, $field['key'] );

			echo '<tr>';
			printf(
				'<th scope="row"><label for="%1$s">%2$s</label></th>',
				esc_attr( $name ),
				esc_html( $field['label'] )
			);
			echo '<td>';

			self::render_input( $field, $name, $value );

			if ( ! empty( $field['hint'] ) ) {
				printf( '<p class="description">%s</p>', esc_html( $field['hint'] ) );
			}

			echo '</td></tr>';
		}

		echo '</tbody></table>';

		// The gallery field lives in the "about" box, below the other details.
		if ( 'about' === $group ) {
			self::render_gallery_note( $post );
		}
	}

	/**
	 * Render a single input by type.
	 *
	 * @param array<string,mixed> $field Field definition.
	 * @param string              $name  Input name.
	 * @param string              $value Current value.
	 */
	private static function render_input( $field, $name, $value ) {
		switch ( $field['type'] ) {
			case 'checkbox':
				printf(
					'<label><input type="checkbox" name="%1$s" id="%1$s" value="1" %2$s /> %3$s</label>',
					esc_attr( $name ),
					checked( $value, '1', false ),
					esc_html__( 'Yes', 'bubbles-dogs' )
				);
				break;

			case 'select':
				printf( '<select name="%1$s" id="%1$s">', esc_attr( $name ) );
				foreach ( BPR_Dogs_Fields::options_for( $field ) as $opt_value => $opt_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $opt_value ),
						selected( $value, $opt_value, false ),
						esc_html( $opt_label )
					);
				}
				echo '</select>';
				break;

			case 'textarea':
				printf(
					'<textarea name="%1$s" id="%1$s" rows="4" class="large-text">%2$s</textarea>',
					esc_attr( $name ),
					esc_textarea( $value )
				);
				break;

			case 'date':
				printf(
					'<input type="date" name="%1$s" id="%1$s" value="%2$s" />',
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;

			case 'number':
				printf(
					'<input type="number" step="0.1" min="0" name="%1$s" id="%1$s" value="%2$s" class="small-text" /> %3$s',
					esc_attr( $name ),
					esc_attr( $value ),
					esc_html( isset( $field['unit'] ) ? $field['unit'] : '' )
				);
				break;

			case 'url':
				printf(
					'<input type="url" name="%1$s" id="%1$s" value="%2$s" class="large-text code" />',
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;

			case 'text':
			default:
				printf(
					'<input type="text" name="%1$s" id="%1$s" value="%2$s" class="regular-text" />',
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;
		}
	}

	/**
	 * Explain how extra photos work, and list the ones already attached.
	 *
	 * Extra photos are ordinary attachments on the dog post, so the standard
	 * media library "Uploaded to this post" flow is all anyone needs to learn.
	 *
	 * @param WP_Post $post Current dog.
	 */
	private static function render_gallery_note( $post ) {
		$attachments = get_attached_media( 'image', $post->ID );
		$count       = count( $attachments );

		echo '<p class="description bpr-dogs-gallery-note">';
		if ( $count > 0 ) {
			printf(
				/* translators: %d: number of photos. */
				esc_html( _n( '%d extra photo is attached to this dog and will show in the gallery on their page.', '%d extra photos are attached to this dog and will show in the gallery on their page.', $count, 'bubbles-dogs' ) ),
				(int) $count
			);
		} else {
			esc_html_e( 'No extra photos yet. Upload more images through the Main photo box (choose "Upload files") and they will appear as a gallery on this dog\'s page.', 'bubbles-dogs' );
		}
		echo '</p>';
	}

	/**
	 * Persist the submitted fields.
	 *
	 * @param int     $post_id Dog post ID.
	 * @param WP_Post $post    Dog post object.
	 */
	public static function save( $post_id, $post ) {
		// Bail on autosaves, revisions and REST/quick-edit writes we don't own.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Status.
		$status_key = BPR_Dogs_Fields::meta_key( 'status' );
		$submitted  = isset( $_POST['bpr_dog_status'] )
			? sanitize_text_field( wp_unslash( $_POST['bpr_dog_status'] ) )
			: '';
		$status     = array_key_exists( $submitted, BPR_Dogs_Fields::statuses() ) ? $submitted : 'available';
		update_post_meta( $post_id, $status_key, $status );

		// Detail fields.
		foreach ( BPR_Dogs_Fields::schema() as $field ) {
			$input = 'bpr_dog_' . $field['key'];
			$raw   = isset( $_POST[ $input ] ) ? wp_unslash( $_POST[ $input ] ) : '';
			$clean = BPR_Dogs_Fields::sanitize( $field, $raw );
			$key   = BPR_Dogs_Fields::meta_key( $field['key'] );

			if ( '' === $clean ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $clean );
			}
		}

		// Stamp the adoption date the first time a dog is marked adopted, so
		// nobody has to remember to fill it in.
		if ( 'adopted' === $status && '' === BPR_Dogs_Fields::get( $post_id, 'adopted_date' ) ) {
			update_post_meta(
				$post_id,
				BPR_Dogs_Fields::meta_key( 'adopted_date' ),
				current_time( 'Y-m-d' )
			);
		}
	}

	/**
	 * Admin list columns.
	 *
	 * @param array<string,string> $columns Existing columns.
	 * @return array<string,string>
	 */
	public static function columns( $columns ) {
		$new = array();

		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['bpr_photo'] = __( 'Photo', 'bubbles-dogs' );
			}
			$new[ $key ] = $label;

			if ( 'title' === $key ) {
				$new['bpr_status'] = __( 'Status', 'bubbles-dogs' );
				$new['bpr_age']    = __( 'Age', 'bubbles-dogs' );
				$new['bpr_days']   = __( 'Days in rescue', 'bubbles-dogs' );
			}
		}

		return $new;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Dog post ID.
	 */
	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'bpr_photo':
				$thumb = get_the_post_thumbnail( $post_id, array( 60, 60 ) );
				echo $thumb ? wp_kses_post( $thumb ) : '<span class="bpr-dogs-nophoto" aria-hidden="true">—</span>';
				break;

			case 'bpr_status':
				$status = BPR_Dogs_Fields::get_status( $post_id );
				$labels = BPR_Dogs_Fields::statuses();
				printf(
					'<span class="bpr-dogs-pill bpr-dogs-pill--%1$s">%2$s</span>',
					esc_attr( $status ),
					esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status )
				);
				break;

			case 'bpr_age':
				$age = BPR_Dogs_Fields::age_label( $post_id );
				echo '' !== $age ? esc_html( $age ) : '—';
				break;

			case 'bpr_days':
				$intake = BPR_Dogs_Fields::get( $post_id, 'intake_date' );
				if ( '' === $intake ) {
					echo '—';
					break;
				}
				$start = date_create_immutable( $intake );
				$now   = date_create_immutable( current_time( 'Y-m-d' ) );
				if ( ! $start || ! $now || $start > $now ) {
					echo '—';
					break;
				}
				$days = (int) $start->diff( $now )->days;
				// Flag long stays — these are the dogs that need a fresh push.
				$long = $days >= 180 ? ' class="bpr-dogs-longstay"' : '';
				printf( '<span%1$s>%2$s</span>', $long, esc_html( number_format_i18n( $days ) ) );
				break;
		}
	}

	/**
	 * Make the age and days columns sortable.
	 *
	 * @param array<string,string> $columns Sortable columns.
	 * @return array<string,string>
	 */
	public static function sortable_columns( $columns ) {
		$columns['bpr_days']   = 'bpr_intake';
		$columns['bpr_status'] = 'bpr_status';
		return $columns;
	}

	/**
	 * A status dropdown above the dog list.
	 */
	public static function status_filter_dropdown() {
		$screen = get_current_screen();
		if ( ! $screen || BPR_Dogs_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$current = isset( $_GET['bpr_status'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list filter.
			? sanitize_text_field( wp_unslash( $_GET['bpr_status'] ) )
			: '';
		?>
		<label class="screen-reader-text" for="bpr_status">
			<?php esc_html_e( 'Filter by adoption status', 'bubbles-dogs' ); ?>
		</label>
		<select name="bpr_status" id="bpr_status">
			<option value=""><?php esc_html_e( 'All statuses', 'bubbles-dogs' ); ?></option>
			<?php foreach ( BPR_Dogs_Fields::statuses() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Apply the status filter and column sorting to the admin query.
	 *
	 * @param WP_Query $query Current query.
	 */
	public static function apply_admin_filters( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( BPR_Dogs_Post_Type::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
		if ( ! empty( $_GET['bpr_status'] ) ) {
			$status = sanitize_text_field( wp_unslash( $_GET['bpr_status'] ) );
			if ( array_key_exists( $status, BPR_Dogs_Fields::statuses() ) ) {
				$query->set(
					'meta_query',
					array(
						array(
							'key'   => BPR_Dogs_Fields::meta_key( 'status' ),
							'value' => $status,
						),
					)
				);
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$orderby = $query->get( 'orderby' );

		if ( 'bpr_intake' === $orderby ) {
			$query->set( 'meta_key', BPR_Dogs_Fields::meta_key( 'intake_date' ) );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'bpr_status' === $orderby ) {
			$query->set( 'meta_key', BPR_Dogs_Fields::meta_key( 'status' ) );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
