<?php
/**
 * Front-end output: templates, archive behaviour, shortcodes and the
 * "apply to adopt" button.
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Everything visitors see.
 */
class BPR_Dogs_Display {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_archive' ) );
		add_filter( 'the_content', array( __CLASS__, 'append_dog_details' ), 20 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_shortcode( 'bubbles_dogs', array( __CLASS__, 'shortcode' ) );
		add_filter( 'document_title_parts', array( __CLASS__, 'archive_title' ) );
	}

	/**
	 * Load the stylesheet on dog pages and anywhere the shortcode is used.
	 */
	public static function enqueue() {
		wp_register_style(
			'bpr-dogs',
			BPR_DOGS_URL . 'assets/dogs.css',
			array(),
			BPR_DOGS_VERSION
		);

		if ( is_singular( BPR_Dogs_Post_Type::POST_TYPE ) || self::is_dog_archive() ) {
			wp_enqueue_style( 'bpr-dogs' );
		}
	}

	/**
	 * Is this a dog archive or dog taxonomy page?
	 *
	 * @return bool
	 */
	private static function is_dog_archive() {
		return is_post_type_archive( BPR_Dogs_Post_Type::POST_TYPE )
			|| is_tax(
				array(
					BPR_Dogs_Post_Type::TAX_SIZE,
					BPR_Dogs_Post_Type::TAX_AGE,
					BPR_Dogs_Post_Type::TAX_BREED,
				)
			);
	}

	/**
	 * Shape the public archive query.
	 *
	 * Adopted dogs are hidden by default (their individual pages still work),
	 * available dogs come first, and `menu_order` lets staff pin a dog to the
	 * top — useful for long-stay dogs who need the extra visibility.
	 *
	 * @param WP_Query $query Current query.
	 */
	public static function filter_archive( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$is_archive = $query->is_post_type_archive( BPR_Dogs_Post_Type::POST_TYPE )
			|| $query->is_tax(
				array(
					BPR_Dogs_Post_Type::TAX_SIZE,
					BPR_Dogs_Post_Type::TAX_AGE,
					BPR_Dogs_Post_Type::TAX_BREED,
				)
			);

		if ( ! $is_archive ) {
			return;
		}

		$query->set( 'meta_query', self::status_meta_query( self::requested_statuses() ) );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$query->set( 'posts_per_page', 24 );
	}

	/**
	 * Which statuses the visitor asked for, via ?status= on the archive.
	 *
	 * @return string[]
	 */
	private static function requested_statuses() {
		$requested = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only filter.

		if ( 'adopted' === $requested ) {
			return array( 'adopted' );
		}
		if ( 'all' === $requested ) {
			return array_keys( BPR_Dogs_Fields::statuses() );
		}

		return BPR_Dogs_Fields::public_statuses();
	}

	/**
	 * Build a meta query matching a set of statuses.
	 *
	 * Dogs imported or created before the status field existed have no status
	 * meta at all, so "available" also has to match a missing row — otherwise
	 * they silently vanish from the listing.
	 *
	 * @param string[] $statuses Statuses to include.
	 * @return array<int|string,mixed>
	 */
	private static function status_meta_query( $statuses ) {
		$key    = BPR_Dogs_Fields::meta_key( 'status' );
		$clause = array( 'relation' => 'OR' );

		foreach ( $statuses as $status ) {
			$clause[] = array(
				'key'   => $key,
				'value' => $status,
			);
		}

		if ( in_array( 'available', $statuses, true ) ) {
			$clause[] = array(
				'key'     => $key,
				'compare' => 'NOT EXISTS',
			);
		}

		return array( $clause );
	}

	/**
	 * Append the details table, gallery and apply button to a dog's page.
	 *
	 * Done as a content filter rather than a custom template on purpose: it
	 * works with classic themes, block themes and page builders alike, and it
	 * inherits the theme's own typography instead of fighting it.
	 *
	 * A theme that wants full control can add its own `single-dog.php`, which
	 * WordPress picks up automatically, and remove this filter.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function append_dog_details( $content ) {
		if ( ! is_singular( BPR_Dogs_Post_Type::POST_TYPE ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		return $content
			. self::details_list( $post_id )
			. self::apply_button( $post_id )
			. self::gallery( $post_id );
	}

	/**
	 * Title the Happy endings view distinctly from the main archive.
	 *
	 * @param array<string,string> $parts Title parts.
	 * @return array<string,string>
	 */
	public static function archive_title( $parts ) {
		if ( is_post_type_archive( BPR_Dogs_Post_Type::POST_TYPE ) && array( 'adopted' ) === self::requested_statuses() ) {
			$parts['title'] = __( 'Happy endings', 'bubbles-dogs' );
		}
		return $parts;
	}

	/**
	 * The apply-to-adopt call to action for a dog.
	 *
	 * @param int $post_id Dog post ID.
	 * @return string HTML, or an empty string when there is nothing to show.
	 */
	public static function apply_button( $post_id ) {
		$status = BPR_Dogs_Fields::get_status( $post_id );
		$name   = get_the_title( $post_id );

		if ( 'adopted' === $status ) {
			return '<p class="bpr-dog-cta bpr-dog-cta--adopted">'
				. sprintf(
					/* translators: %s: dog's name. */
					esc_html__( '%s has found their forever home.', 'bubbles-dogs' ),
					esc_html( $name )
				)
				. '</p>';
		}

		if ( 'reserved' === $status ) {
			$notice = BPR_Dogs_Settings::get( 'reserved_notice' );
			return '<p class="bpr-dog-cta bpr-dog-cta--reserved">'
				. esc_html( sprintf( $notice, $name ) )
				. '</p>';
		}

		$url = BPR_Dogs_Settings::get( 'apply_url' );
		if ( '' === $url ) {
			return '';
		}

		$param = BPR_Dogs_Settings::get( 'apply_param' );
		$label = BPR_Dogs_Settings::get( 'apply_label' );

		$link = add_query_arg(
			array(
				$param      => rawurlencode( $name ),
				$param . '_id' => (int) $post_id,
			),
			$url
		);

		return sprintf(
			'<p class="bpr-dog-cta"><a class="bpr-dog-apply" href="%1$s">%2$s</a></p>',
			esc_url( $link ),
			esc_html( sprintf( $label, $name ) )
		);
	}

	/**
	 * Render one dog card, used by both the archive and the shortcode.
	 *
	 * @param int $post_id Dog post ID.
	 * @return string
	 */
	public static function card( $post_id ) {
		$status  = BPR_Dogs_Fields::get_status( $post_id );
		$labels  = BPR_Dogs_Fields::statuses();
		$age     = BPR_Dogs_Fields::age_label( $post_id );
		$sex     = BPR_Dogs_Fields::display_value( $post_id, BPR_Dogs_Fields::get_field( 'sex' ) );
		$sizes   = get_the_term_list( $post_id, BPR_Dogs_Post_Type::TAX_SIZE, '', ', ' );
		$excerpt = get_the_excerpt( $post_id );

		$meta = array_filter( array( $sex, $age ) );

		ob_start();
		?>
		<article class="bpr-dog-card bpr-dog-card--<?php echo esc_attr( $status ); ?>">
			<a class="bpr-dog-card__link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<div class="bpr-dog-card__media">
					<?php if ( has_post_thumbnail( $post_id ) ) : ?>
						<?php echo wp_kses_post( get_the_post_thumbnail( $post_id, 'medium_large' ) ); ?>
					<?php else : ?>
						<span class="bpr-dog-card__noimage" aria-hidden="true">🐾</span>
					<?php endif; ?>

					<?php if ( 'available' !== $status ) : ?>
						<span class="bpr-dog-card__badge bpr-dog-card__badge--<?php echo esc_attr( $status ); ?>">
							<?php echo esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status ); ?>
						</span>
					<?php endif; ?>
				</div>

				<h3 class="bpr-dog-card__name"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>

				<?php if ( ! empty( $meta ) ) : ?>
					<p class="bpr-dog-card__meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p>
				<?php endif; ?>

				<?php if ( $sizes && ! is_wp_error( $sizes ) ) : ?>
					<p class="bpr-dog-card__size"><?php echo wp_kses_post( $sizes ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $excerpt ) : ?>
					<p class="bpr-dog-card__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 20 ) ); ?></p>
				<?php endif; ?>
			</a>
		</article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * A filter bar for size and age group.
	 *
	 * Plain links to taxonomy archives — no JavaScript, works everywhere, and
	 * each filtered view gets a real URL people can share.
	 *
	 * @return string
	 */
	public static function filter_bar() {
		$taxonomies = array(
			BPR_Dogs_Post_Type::TAX_SIZE => __( 'Size', 'bubbles-dogs' ),
			BPR_Dogs_Post_Type::TAX_AGE  => __( 'Age', 'bubbles-dogs' ),
		);

		$archive = get_post_type_archive_link( BPR_Dogs_Post_Type::POST_TYPE );

		ob_start();
		echo '<nav class="bpr-dog-filters" aria-label="' . esc_attr__( 'Filter dogs', 'bubbles-dogs' ) . '">';

		foreach ( $taxonomies as $taxonomy => $label ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => true,
				)
			);

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			echo '<div class="bpr-dog-filters__group">';
			printf( '<span class="bpr-dog-filters__label">%s</span>', esc_html( $label ) );

			foreach ( $terms as $term ) {
				printf(
					'<a class="bpr-dog-filters__item%1$s" href="%2$s">%3$s</a>',
					is_tax( $taxonomy, $term->term_id ) ? ' is-active' : '',
					esc_url( get_term_link( $term ) ),
					esc_html( $term->name )
				);
			}

			echo '</div>';
		}

		if ( $archive ) {
			printf(
				'<div class="bpr-dog-filters__group"><a class="bpr-dog-filters__item bpr-dog-filters__reset" href="%1$s">%2$s</a></div>',
				esc_url( $archive ),
				esc_html__( 'Show all dogs', 'bubbles-dogs' )
			);
		}

		echo '</nav>';
		return (string) ob_get_clean();
	}

	/**
	 * The [bubbles_dogs] shortcode.
	 *
	 * @param array<string,string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'status'  => 'looking',
				'size'    => '',
				'age'     => '',
				'breed'   => '',
				'limit'   => 24,
				'filters' => 'no',
			),
			$atts,
			'bubbles_dogs'
		);

		wp_enqueue_style( 'bpr-dogs' );

		if ( 'adopted' === $atts['status'] ) {
			$statuses = array( 'adopted' );
		} elseif ( 'all' === $atts['status'] ) {
			$statuses = array_keys( BPR_Dogs_Fields::statuses() );
		} elseif ( array_key_exists( $atts['status'], BPR_Dogs_Fields::statuses() ) ) {
			$statuses = array( $atts['status'] );
		} else {
			$statuses = BPR_Dogs_Fields::public_statuses();
		}

		$args = array(
			'post_type'      => BPR_Dogs_Post_Type::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['limit'],
			'meta_query'     => self::status_meta_query( $statuses ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- small dataset, indexed by post_id.
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		);

		$tax_query = array();
		$tax_map   = array(
			BPR_Dogs_Post_Type::TAX_SIZE  => $atts['size'],
			BPR_Dogs_Post_Type::TAX_AGE   => $atts['age'],
			BPR_Dogs_Post_Type::TAX_BREED => $atts['breed'],
		);

		foreach ( $tax_map as $taxonomy => $value ) {
			$value = trim( (string) $value );
			if ( '' === $value ) {
				continue;
			}
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => array_map( 'trim', explode( ',', $value ) ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small dataset.
		}

		$dogs = new WP_Query( $args );

		if ( ! $dogs->have_posts() ) {
			return '<p class="bpr-dogs-empty">' . esc_html__( 'No dogs to show here just now — please check back soon.', 'bubbles-dogs' ) . '</p>';
		}

		ob_start();

		if ( 'yes' === $atts['filters'] || 'true' === $atts['filters'] ) {
			echo wp_kses_post( self::filter_bar() );
		}

		echo '<div class="bpr-dog-grid">';
		while ( $dogs->have_posts() ) {
			$dogs->the_post();
			echo wp_kses_post( self::card( get_the_ID() ) );
		}
		echo '</div>';

		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * The "at a glance" detail list for a single dog.
	 *
	 * @param int $post_id Dog post ID.
	 * @return string
	 */
	public static function details_list( $post_id ) {
		$rows = array();

		$age = BPR_Dogs_Fields::age_label( $post_id );
		if ( '' !== $age ) {
			$rows[ __( 'Age', 'bubbles-dogs' ) ] = $age;
		}

		foreach ( BPR_Dogs_Fields::schema() as $field ) {
			if ( empty( $field['summary'] ) ) {
				continue;
			}
			// Age is handled above so the calculated value wins.
			if ( 'age_text' === $field['key'] ) {
				continue;
			}

			$value = BPR_Dogs_Fields::display_value( $post_id, $field );
			if ( '' === $value ) {
				continue;
			}

			$rows[ $field['label'] ] = $value;
		}

		$breeds = get_the_term_list( $post_id, BPR_Dogs_Post_Type::TAX_BREED, '', ', ' );
		if ( $breeds && ! is_wp_error( $breeds ) ) {
			$rows[ __( 'Breed', 'bubbles-dogs' ) ] = $breeds;
		}

		$sizes = get_the_term_list( $post_id, BPR_Dogs_Post_Type::TAX_SIZE, '', ', ' );
		if ( $sizes && ! is_wp_error( $sizes ) ) {
			$rows[ __( 'Size', 'bubbles-dogs' ) ] = $sizes;
		}

		if ( empty( $rows ) ) {
			return '';
		}

		ob_start();
		echo '<dl class="bpr-dog-details">';
		foreach ( $rows as $label => $value ) {
			printf(
				'<div class="bpr-dog-details__row"><dt>%1$s</dt><dd>%2$s</dd></div>',
				esc_html( $label ),
				wp_kses_post( $value )
			);
		}
		echo '</dl>';

		return (string) ob_get_clean();
	}

	/**
	 * Extra photos attached to a dog, as a gallery.
	 *
	 * @param int $post_id Dog post ID.
	 * @return string
	 */
	public static function gallery( $post_id ) {
		$thumbnail_id = (int) get_post_thumbnail_id( $post_id );

		$images = get_posts(
			array(
				'post_parent'    => $post_id,
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => 12,
				'orderby'        => 'menu_order ID',
				'order'          => 'ASC',
				'exclude'        => $thumbnail_id ? array( $thumbnail_id ) : array(),
			)
		);

		if ( empty( $images ) ) {
			return '';
		}

		ob_start();
		echo '<div class="bpr-dog-gallery">';
		foreach ( $images as $image ) {
			printf(
				'<a class="bpr-dog-gallery__item" href="%1$s">%2$s</a>',
				esc_url( (string) wp_get_attachment_image_url( $image->ID, 'full' ) ),
				wp_kses_post( wp_get_attachment_image( $image->ID, 'medium', false, array( 'loading' => 'lazy' ) ) )
			);
		}
		echo '</div>';

		return (string) ob_get_clean();
	}
}
