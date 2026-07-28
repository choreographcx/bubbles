<?php
/**
 * The "Dog" post type and its taxonomies.
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the dog post type, its taxonomies and their default terms.
 */
class BPR_Dogs_Post_Type {

	const POST_TYPE = 'dog';
	const TAX_SIZE  = 'dog_size';
	const TAX_AGE   = 'dog_age';
	const TAX_BREED = 'dog_breed';

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
	}

	/**
	 * Register the post type.
	 *
	 * Public, archived at /adopt-a-dog/, and REST-enabled so the block editor
	 * and any future integrations can read it.
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Dogs', 'post type general name', 'bubbles-dogs' ),
			'singular_name'         => _x( 'Dog', 'post type singular name', 'bubbles-dogs' ),
			'menu_name'             => _x( 'Dogs', 'admin menu', 'bubbles-dogs' ),
			'add_new'               => __( 'Add dog', 'bubbles-dogs' ),
			'add_new_item'          => __( 'Add a dog', 'bubbles-dogs' ),
			'edit_item'             => __( 'Edit dog', 'bubbles-dogs' ),
			'new_item'              => __( 'New dog', 'bubbles-dogs' ),
			'view_item'             => __( 'View dog', 'bubbles-dogs' ),
			'view_items'            => __( 'View dogs', 'bubbles-dogs' ),
			'search_items'          => __( 'Search dogs', 'bubbles-dogs' ),
			'not_found'             => __( 'No dogs found.', 'bubbles-dogs' ),
			'not_found_in_trash'    => __( 'No dogs found in trash.', 'bubbles-dogs' ),
			'all_items'             => __( 'All dogs', 'bubbles-dogs' ),
			'featured_image'        => __( 'Main photo', 'bubbles-dogs' ),
			'set_featured_image'    => __( 'Set main photo', 'bubbles-dogs' ),
			'remove_featured_image' => __( 'Remove main photo', 'bubbles-dogs' ),
			'use_featured_image'    => __( 'Use as main photo', 'bubbles-dogs' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => $labels,
				'description'  => __( 'Dogs available for adoption.', 'bubbles-dogs' ),
				'public'       => true,
				'has_archive'  => 'adopt-a-dog',
				'rewrite'      => array(
					'slug'       => 'dog',
					'with_front' => false,
				),
				'menu_icon'    => 'dashicons-pets',
				'menu_position' => 20,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
				'taxonomies'   => array( self::TAX_SIZE, self::TAX_AGE, self::TAX_BREED ),
				'show_in_rest' => true,
				'map_meta_cap' => true,
			)
		);
	}

	/**
	 * Register the filterable taxonomies.
	 *
	 * Size, age band and breed are taxonomies rather than plain text because
	 * they are the three things adopters actually filter by — as taxonomies
	 * they get archive pages and filtering without any extra code.
	 */
	public static function register_taxonomies() {
		register_taxonomy(
			self::TAX_SIZE,
			self::POST_TYPE,
			array(
				'labels'            => self::tax_labels( __( 'Sizes', 'bubbles-dogs' ), __( 'Size', 'bubbles-dogs' ) ),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'dog-size',
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			self::TAX_AGE,
			self::POST_TYPE,
			array(
				'labels'            => self::tax_labels( __( 'Age groups', 'bubbles-dogs' ), __( 'Age group', 'bubbles-dogs' ) ),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'dog-age',
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			self::TAX_BREED,
			self::POST_TYPE,
			array(
				'labels'            => self::tax_labels( __( 'Breeds', 'bubbles-dogs' ), __( 'Breed', 'bubbles-dogs' ) ),
				'public'            => true,
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'dog-breed',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Standard taxonomy label set.
	 *
	 * @param string $plural   Plural label.
	 * @param string $singular Singular label.
	 * @return array<string,string>
	 */
	private static function tax_labels( $plural, $singular ) {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			'menu_name'     => $plural,
			'all_items'     => sprintf(
				/* translators: %s: plural taxonomy label. */
				__( 'All %s', 'bubbles-dogs' ),
				strtolower( $plural )
			),
			'edit_item'     => sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'Edit %s', 'bubbles-dogs' ),
				strtolower( $singular )
			),
			'add_new_item'  => sprintf(
				/* translators: %s: singular taxonomy label. */
				__( 'Add %s', 'bubbles-dogs' ),
				strtolower( $singular )
			),
			'search_items'  => sprintf(
				/* translators: %s: plural taxonomy label. */
				__( 'Search %s', 'bubbles-dogs' ),
				strtolower( $plural )
			),
		);
	}

	/**
	 * Register meta keys so they are readable over REST and properly typed.
	 */
	public static function register_meta() {
		$keys = array( BPR_Dogs_Fields::meta_key( 'status' ) );

		foreach ( BPR_Dogs_Fields::schema() as $field ) {
			$keys[] = BPR_Dogs_Fields::meta_key( $field['key'] );
		}

		foreach ( $keys as $key ) {
			register_post_meta(
				self::POST_TYPE,
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}

		register_post_meta(
			self::POST_TYPE,
			BPR_Dogs_Fields::meta_key( 'gallery' ),
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Seed the default size and age-group terms.
	 *
	 * Called on activation only, so terms the rescue renames or deletes later
	 * do not keep reappearing.
	 */
	public static function insert_default_terms() {
		$defaults = array(
			self::TAX_SIZE => array(
				'small'  => __( 'Small (up to 10kg)', 'bubbles-dogs' ),
				'medium' => __( 'Medium (10–25kg)', 'bubbles-dogs' ),
				'large'  => __( 'Large (25kg+)', 'bubbles-dogs' ),
			),
			self::TAX_AGE  => array(
				'puppy'  => __( 'Puppy (under 1 year)', 'bubbles-dogs' ),
				'young'  => __( 'Young (1–3 years)', 'bubbles-dogs' ),
				'adult'  => __( 'Adult (3–8 years)', 'bubbles-dogs' ),
				'senior' => __( 'Senior (8 years+)', 'bubbles-dogs' ),
			),
		);

		foreach ( $defaults as $taxonomy => $terms ) {
			foreach ( $terms as $slug => $name ) {
				if ( ! term_exists( $slug, $taxonomy ) ) {
					wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
				}
			}
		}
	}
}
