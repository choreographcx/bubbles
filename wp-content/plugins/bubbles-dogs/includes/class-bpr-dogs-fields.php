<?php
/**
 * The dog field schema — the single source of truth for this plugin.
 *
 * This one array drives the admin meta boxes, the sanitising on save, the CSV
 * importer's column mapping, and the front-end detail table. Add a field here
 * and it appears in all four places; there is deliberately nowhere else to
 * define a field.
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions and value helpers.
 */
class BPR_Dogs_Fields {

	/**
	 * Meta key prefix. Underscore-prefixed so the fields stay out of the
	 * generic "Custom Fields" box and don't get edited by accident.
	 */
	const PREFIX = '_bpr_dog_';

	/**
	 * Adoption status values.
	 *
	 * @return array<string,string> Value => label.
	 */
	public static function statuses() {
		return array(
			'available' => __( 'Available', 'bubbles-dogs' ),
			'reserved'  => __( 'Reserved', 'bubbles-dogs' ),
			'hold'      => __( 'On hold', 'bubbles-dogs' ),
			'adopted'   => __( 'Adopted', 'bubbles-dogs' ),
		);
	}

	/**
	 * Statuses that appear on the public archive.
	 *
	 * Adopted dogs stay published (their page keeps working, which is good for
	 * SEO and for the people who shared it) but drop off the main listing.
	 *
	 * @return string[]
	 */
	public static function public_statuses() {
		return array( 'available', 'reserved', 'hold' );
	}

	/**
	 * Tri-state options for the "good with" fields.
	 *
	 * "Unknown" is the default and is the honest answer for most street
	 * rescues — it is not the same as "no", and adopters deserve the
	 * difference.
	 *
	 * @return array<string,string>
	 */
	public static function tristate() {
		return array(
			''        => __( 'Unknown', 'bubbles-dogs' ),
			'yes'     => __( 'Yes', 'bubbles-dogs' ),
			'no'      => __( 'No', 'bubbles-dogs' ),
			'maybe'   => __( 'With introductions', 'bubbles-dogs' ),
		);
	}

	/**
	 * The full field schema.
	 *
	 * Recognised keys per field:
	 *   key      - meta key without the prefix, and the CSV column name
	 *   label    - admin + front-end label
	 *   type     - text|textarea|select|checkbox|date|number|url
	 *   options  - value => label map, for `select`
	 *   group    - which meta box it renders in: about|health|behaviour|admin
	 *   summary  - show in the front-end "at a glance" list
	 *   hint     - help text under the admin field
	 *   unit     - suffix appended when displaying (e.g. "kg")
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function schema() {
		return array(
			array(
				'key'     => 'sex',
				'label'   => __( 'Sex', 'bubbles-dogs' ),
				'type'    => 'select',
				'options' => array(
					''       => __( 'Unknown', 'bubbles-dogs' ),
					'female' => __( 'Female', 'bubbles-dogs' ),
					'male'   => __( 'Male', 'bubbles-dogs' ),
				),
				'group'   => 'about',
				'summary' => true,
			),
			array(
				'key'     => 'dob',
				'label'   => __( 'Date of birth', 'bubbles-dogs' ),
				'type'    => 'date',
				'group'   => 'about',
				'hint'    => __( 'If known. The age shown on the site is worked out from this and stays correct as the dog gets older.', 'bubbles-dogs' ),
			),
			array(
				'key'     => 'age_text',
				'label'   => __( 'Approximate age', 'bubbles-dogs' ),
				'type'    => 'text',
				'group'   => 'about',
				'summary' => true,
				'hint'    => __( 'Free text, e.g. "about 2 years". Only used when no date of birth is set.', 'bubbles-dogs' ),
			),
			array(
				'key'     => 'weight_kg',
				'label'   => __( 'Weight', 'bubbles-dogs' ),
				'type'    => 'number',
				'group'   => 'about',
				'summary' => true,
				'unit'    => 'kg',
			),
			array(
				'key'     => 'colour',
				'label'   => __( 'Colour / markings', 'bubbles-dogs' ),
				'type'    => 'text',
				'group'   => 'about',
			),
			array(
				'key'     => 'location',
				'label'   => __( 'Currently staying', 'bubbles-dogs' ),
				'type'    => 'text',
				'group'   => 'about',
				'summary' => true,
				'hint'    => __( 'Foster area, boarding kennel or shelter — whatever you are happy showing publicly.', 'bubbles-dogs' ),
			),

			// --- Health ---
			array(
				'key'     => 'vaccinated',
				'label'   => __( 'Vaccinations up to date', 'bubbles-dogs' ),
				'type'    => 'checkbox',
				'group'   => 'health',
				'summary' => true,
			),
			array(
				'key'     => 'sterilised',
				'label'   => __( 'Spayed / neutered', 'bubbles-dogs' ),
				'type'    => 'checkbox',
				'group'   => 'health',
				'summary' => true,
			),
			array(
				'key'     => 'microchipped',
				'label'   => __( 'Microchipped', 'bubbles-dogs' ),
				'type'    => 'checkbox',
				'group'   => 'health',
				'summary' => true,
			),
			array(
				'key'     => 'health_notes',
				'label'   => __( 'Health notes', 'bubbles-dogs' ),
				'type'    => 'textarea',
				'group'   => 'health',
				'hint'    => __( 'Ongoing conditions, medication, special diet. Shown on the dog\'s page — be honest here, it saves failed adoptions.', 'bubbles-dogs' ),
			),

			// --- Behaviour ---
			array(
				'key'     => 'good_with_kids',
				'label'   => __( 'Good with children', 'bubbles-dogs' ),
				'type'    => 'select',
				'options' => 'tristate',
				'group'   => 'behaviour',
				'summary' => true,
			),
			array(
				'key'     => 'good_with_dogs',
				'label'   => __( 'Good with dogs', 'bubbles-dogs' ),
				'type'    => 'select',
				'options' => 'tristate',
				'group'   => 'behaviour',
				'summary' => true,
			),
			array(
				'key'     => 'good_with_cats',
				'label'   => __( 'Good with cats', 'bubbles-dogs' ),
				'type'    => 'select',
				'options' => 'tristate',
				'group'   => 'behaviour',
				'summary' => true,
			),
			array(
				'key'     => 'house_trained',
				'label'   => __( 'House trained', 'bubbles-dogs' ),
				'type'    => 'select',
				'options' => 'tristate',
				'group'   => 'behaviour',
			),
			array(
				'key'     => 'energy',
				'label'   => __( 'Energy level', 'bubbles-dogs' ),
				'type'    => 'select',
				'options' => array(
					''         => __( 'Not recorded', 'bubbles-dogs' ),
					'calm'     => __( 'Calm', 'bubbles-dogs' ),
					'moderate' => __( 'Moderate', 'bubbles-dogs' ),
					'active'   => __( 'Active', 'bubbles-dogs' ),
				),
				'group'   => 'behaviour',
				'summary' => true,
			),

			// --- Internal / admin ---
			array(
				'key'     => 'travel_ready',
				'label'   => __( 'Can be rehomed overseas', 'bubbles-dogs' ),
				'type'    => 'checkbox',
				'group'   => 'admin',
				'hint'    => __( 'Tick if this dog can fly with a volunteer flight buddy. Remove this field from the schema if you only rehome inside the UAE.', 'bubbles-dogs' ),
			),
			array(
				'key'     => 'intake_date',
				'label'   => __( 'Came into rescue', 'bubbles-dogs' ),
				'type'    => 'date',
				'group'   => 'admin',
				'hint'    => __( 'Used to spot long-stay dogs. Not shown publicly.', 'bubbles-dogs' ),
			),
			array(
				'key'     => 'adopted_date',
				'label'   => __( 'Adopted on', 'bubbles-dogs' ),
				'type'    => 'date',
				'group'   => 'admin',
			),
			array(
				'key'     => 'source_url',
				'label'   => __( 'Original social post', 'bubbles-dogs' ),
				'type'    => 'url',
				'group'   => 'admin',
				'hint'    => __( 'Filled in automatically by the importer so you can trace a listing back to the post it came from.', 'bubbles-dogs' ),
			),
		);
	}

	/**
	 * Fields belonging to one meta box group.
	 *
	 * @param string $group Group name.
	 * @return array<int,array<string,mixed>>
	 */
	public static function by_group( $group ) {
		return array_values(
			array_filter(
				self::schema(),
				static function ( $field ) use ( $group ) {
					return $field['group'] === $group;
				}
			)
		);
	}

	/**
	 * Look up a single field definition.
	 *
	 * @param string $key Field key.
	 * @return array<string,mixed>|null
	 */
	public static function get_field( $key ) {
		foreach ( self::schema() as $field ) {
			if ( $field['key'] === $key ) {
				return $field;
			}
		}
		return null;
	}

	/**
	 * Resolve a field's options, expanding the 'tristate' shorthand.
	 *
	 * @param array<string,mixed> $field Field definition.
	 * @return array<string,string>
	 */
	public static function options_for( $field ) {
		if ( empty( $field['options'] ) ) {
			return array();
		}
		if ( 'tristate' === $field['options'] ) {
			return self::tristate();
		}
		return (array) $field['options'];
	}

	/**
	 * Full meta key for a field.
	 *
	 * @param string $key Field key.
	 * @return string
	 */
	public static function meta_key( $key ) {
		return self::PREFIX . $key;
	}

	/**
	 * Read a raw stored value.
	 *
	 * @param int    $post_id Dog post ID.
	 * @param string $key     Field key.
	 * @return string
	 */
	public static function get( $post_id, $key ) {
		return (string) get_post_meta( $post_id, self::meta_key( $key ), true );
	}

	/**
	 * A dog's adoption status, defaulting to available.
	 *
	 * @param int $post_id Dog post ID.
	 * @return string
	 */
	public static function get_status( $post_id ) {
		$status = get_post_meta( $post_id, self::meta_key( 'status' ), true );
		$status = is_scalar( $status ) ? (string) $status : '';
		return array_key_exists( $status, self::statuses() ) ? $status : 'available';
	}

	/**
	 * Sanitise a submitted value according to its field type.
	 *
	 * @param array<string,mixed> $field Field definition.
	 * @param mixed               $raw   Raw submitted value.
	 * @return string
	 */
	public static function sanitize( $field, $raw ) {
		switch ( $field['type'] ) {
			case 'checkbox':
				return $raw ? '1' : '';

			case 'select':
				$options = self::options_for( $field );
				$raw     = is_scalar( $raw ) ? (string) $raw : '';
				return array_key_exists( $raw, $options ) ? $raw : '';

			case 'date':
				$raw = is_scalar( $raw ) ? trim( (string) $raw ) : '';
				// Store as Y-m-d so dates sort correctly as strings.
				if ( '' === $raw ) {
					return '';
				}
				$ts = strtotime( $raw );
				return $ts ? gmdate( 'Y-m-d', $ts ) : '';

			case 'number':
				$raw = is_scalar( $raw ) ? trim( (string) $raw ) : '';
				if ( '' === $raw || ! is_numeric( $raw ) ) {
					return '';
				}
				// Trim a trailing ".0" so "12.0" displays as "12".
				return rtrim( rtrim( number_format( (float) $raw, 1, '.', '' ), '0' ), '.' );

			case 'url':
				return esc_url_raw( is_scalar( $raw ) ? (string) $raw : '' );

			case 'textarea':
				return sanitize_textarea_field( is_scalar( $raw ) ? (string) $raw : '' );

			case 'text':
			default:
				return sanitize_text_field( is_scalar( $raw ) ? (string) $raw : '' );
		}
	}

	/**
	 * Human-readable value for display.
	 *
	 * @param int                 $post_id Dog post ID.
	 * @param array<string,mixed> $field   Field definition.
	 * @return string Empty string when there is nothing worth showing.
	 */
	public static function display_value( $post_id, $field ) {
		$value = self::get( $post_id, $field['key'] );

		switch ( $field['type'] ) {
			case 'checkbox':
				// Only worth a row when true — a wall of "No" reads badly.
				return '' !== $value ? __( 'Yes', 'bubbles-dogs' ) : '';

			case 'select':
				$options = self::options_for( $field );
				if ( '' === $value || ! isset( $options[ $value ] ) ) {
					return '';
				}
				return $options[ $value ];

			case 'date':
				if ( '' === $value ) {
					return '';
				}
				$ts = strtotime( $value );
				return $ts ? date_i18n( get_option( 'date_format' ), $ts ) : '';

			case 'number':
				if ( '' === $value ) {
					return '';
				}
				return isset( $field['unit'] ) ? $value . ' ' . $field['unit'] : $value;

			default:
				return $value;
		}
	}

	/**
	 * The age to show for a dog.
	 *
	 * Prefers a calculated age from the date of birth so listings don't go
	 * stale, and falls back to the free-text field.
	 *
	 * @param int $post_id Dog post ID.
	 * @return string
	 */
	public static function age_label( $post_id ) {
		$dob = self::get( $post_id, 'dob' );

		if ( '' !== $dob ) {
			$birth = date_create_immutable( $dob );
			$now   = date_create_immutable( current_time( 'Y-m-d' ) );

			if ( $birth && $now && $birth <= $now ) {
				$diff   = $birth->diff( $now );
				$years  = (int) $diff->y;
				$months = (int) $diff->m;

				if ( $years >= 1 ) {
					$label = sprintf(
						/* translators: %d: number of years. */
						_n( '%d year', '%d years', $years, 'bubbles-dogs' ),
						$years
					);
					if ( $months > 0 ) {
						$label .= ' ' . sprintf(
							/* translators: %d: number of months. */
							_n( '%d month', '%d months', $months, 'bubbles-dogs' ),
							$months
						);
					}
					return $label;
				}

				if ( $months >= 1 ) {
					return sprintf(
						/* translators: %d: number of months. */
						_n( '%d month', '%d months', $months, 'bubbles-dogs' ),
						$months
					);
				}

				return __( 'Under a month', 'bubbles-dogs' );
			}
		}

		return self::get( $post_id, 'age_text' );
	}
}
