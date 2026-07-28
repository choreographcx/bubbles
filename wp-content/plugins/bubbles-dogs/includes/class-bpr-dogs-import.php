<?php
/**
 * WP-CLI importer for dog listings.
 *
 * Reads the reviewed CSV, creates or updates a dog per row, and sideloads the
 * photos. Safe to run more than once: rows are matched on the dog's name, so a
 * second run updates the existing listings rather than creating duplicates.
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bulk-load dogs from a CSV.
 */
class BPR_Dogs_Import {

	/**
	 * Meta key recording which source file an attachment came from, so
	 * re-running the import doesn't upload the same photo twice.
	 */
	const ATTACHMENT_SOURCE_KEY = '_bpr_dog_photo_source';

	/**
	 * Import dogs from a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the CSV. Must have a header row.
	 *
	 * [--media-root=<path>]
	 * : Folder that relative photo paths are resolved against — normally the
	 * folder you unzipped the Meta export into. Ignored for photo URLs.
	 *
	 * [--dry-run]
	 * : Report what would happen without writing anything.
	 *
	 * [--skip-photos]
	 * : Create the listings but don't upload any images. Useful for a fast
	 * first pass to check the text, since photos are the slow part.
	 *
	 * [--post-status=<status>]
	 * : Post status for newly created dogs. Use `draft` to review before they
	 * go live. Default: publish.
	 *
	 * ## EXAMPLES
	 *
	 *     # Check the CSV parses and see what would be created
	 *     wp bubbles-dogs import dogs.csv --dry-run
	 *
	 *     # Import as drafts, with photos from an unzipped Instagram export
	 *     wp bubbles-dogs import dogs.csv --media-root=./instagram-export --post-status=draft
	 *
	 * @param array<int,string>    $args       Positional arguments.
	 * @param array<string,string> $assoc_args Flags.
	 */
	public function import( $args, $assoc_args ) {
		$file = isset( $args[0] ) ? $args[0] : '';

		if ( ! is_readable( $file ) ) {
			WP_CLI::error( "Cannot read CSV: {$file}" );
		}

		$dry_run     = ! empty( $assoc_args['dry-run'] );
		$skip_photos = ! empty( $assoc_args['skip-photos'] );
		$media_root  = isset( $assoc_args['media-root'] ) ? rtrim( $assoc_args['media-root'], '/' ) : '';
		$post_status = isset( $assoc_args['post-status'] ) ? sanitize_key( $assoc_args['post-status'] ) : 'publish';

		if ( ! in_array( $post_status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
			WP_CLI::error( "Unsupported --post-status: {$post_status}" );
		}

		$rows = $this->read_csv( $file );

		if ( empty( $rows ) ) {
			WP_CLI::error( 'No data rows found in the CSV.' );
		}

		if ( $dry_run ) {
			WP_CLI::log( WP_CLI::colorize( '%yDry run — nothing will be saved.%n' ) );
		}

		$created = 0;
		$updated = 0;
		$photos  = 0;
		$skipped = 0;

		foreach ( $rows as $index => $row ) {
			$line = $index + 2; // +1 for zero-index, +1 for the header row.
			$name = isset( $row['name'] ) ? trim( $row['name'] ) : '';

			if ( '' === $name ) {
				WP_CLI::warning( "Line {$line}: no name, skipping." );
				++$skipped;
				continue;
			}

			// A left-over needs_review flag means the row wasn't checked yet.
			if ( ! empty( $row['needs_review'] ) ) {
				WP_CLI::warning(
					sprintf(
						'Line %d (%s): still flagged "%s" — skipping. Clear the needs_review column once checked.',
						$line,
						$name,
						$row['needs_review']
					)
				);
				++$skipped;
				continue;
			}

			$existing = $this->find_dog_by_name( $name );

			if ( $dry_run ) {
				WP_CLI::log(
					sprintf(
						'  %s %s%s',
						$existing ? 'update' : 'create',
						$name,
						$skip_photos ? '' : ' (' . count( $this->photo_list( $row ) ) . ' photos)'
					)
				);
				$existing ? ++$updated : ++$created;
				continue;
			}

			$post_id = $this->upsert_dog( $row, $existing, $post_status );

			if ( is_wp_error( $post_id ) ) {
				WP_CLI::warning( "Line {$line} ({$name}): " . $post_id->get_error_message() );
				++$skipped;
				continue;
			}

			if ( $existing ) {
				++$updated;
				WP_CLI::log( "  updated {$name} (#{$post_id})" );
			} else {
				++$created;
				WP_CLI::log( "  created {$name} (#{$post_id})" );
			}

			if ( ! $skip_photos ) {
				$photos += $this->attach_photos( $post_id, $this->photo_list( $row ), $media_root );
			}
		}

		WP_CLI::success(
			sprintf(
				'%d created, %d updated, %d photos attached, %d skipped.',
				$created,
				$updated,
				$photos,
				$skipped
			)
		);

		if ( ! $dry_run && $created > 0 ) {
			WP_CLI::log( 'Now visit Dogs in wp-admin to spot-check the listings and set main photos where the importer guessed wrong.' );
		}
	}

	/**
	 * Read a CSV into an array of header-keyed rows.
	 *
	 * @param string $file CSV path.
	 * @return array<int,array<string,string>>
	 */
	private function read_csv( $file ) {
		$handle = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- streaming a local CSV.

		if ( false === $handle ) {
			WP_CLI::error( "Could not open {$file}" );
		}

		$header = fgetcsv( $handle );

		if ( ! is_array( $header ) ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			WP_CLI::error( 'The CSV has no header row.' );
		}

		// Strip a UTF-8 BOM off the first column name, which Excel adds.
		if ( isset( $header[0] ) ) {
			$header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $header[0] );
		}

		$header = array_map(
			static function ( $key ) {
				return strtolower( trim( (string) $key ) );
			},
			$header
		);

		$rows = array();

		while ( false !== ( $line = fgetcsv( $handle ) ) ) {
			// Skip entirely blank lines.
			if ( array() === array_filter( $line, static function ( $v ) {
				return null !== $v && '' !== trim( (string) $v );
			} ) ) {
				continue;
			}

			// Pad or trim so array_combine never fails on a ragged row.
			$line = array_slice( array_pad( $line, count( $header ), '' ), 0, count( $header ) );

			$rows[] = array_map(
				static function ( $value ) {
					return trim( (string) $value );
				},
				array_combine( $header, $line )
			);
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return $rows;
	}

	/**
	 * Find an existing dog by exact name.
	 *
	 * @param string $name Dog name.
	 * @return int|false Post ID, or false when not found.
	 */
	private function find_dog_by_name( $name ) {
		$query = new WP_Query(
			array(
				'post_type'              => BPR_Dogs_Post_Type::POST_TYPE,
				'title'                  => $name,
				'post_status'            => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		return ! empty( $query->posts ) ? (int) $query->posts[0] : false;
	}

	/**
	 * Create or update a dog from a CSV row.
	 *
	 * @param array<string,string> $row         CSV row.
	 * @param int|false            $existing    Existing post ID, if any.
	 * @param string               $post_status Status for new posts.
	 * @return int|WP_Error Post ID on success.
	 */
	private function upsert_dog( $row, $existing, $post_status ) {
		$postarr = array(
			'post_type'  => BPR_Dogs_Post_Type::POST_TYPE,
			'post_title' => $row['name'],
		);

		if ( isset( $row['bio'] ) && '' !== $row['bio'] ) {
			$postarr['post_content'] = wp_kses_post( $row['bio'] );
		}

		if ( $existing ) {
			$postarr['ID'] = $existing;
			$post_id       = wp_update_post( $postarr, true );
		} else {
			$postarr['post_status'] = $post_status;
			$post_id                = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$post_id = (int) $post_id;

		// Adoption status.
		$status = isset( $row['status'] ) ? strtolower( trim( $row['status'] ) ) : 'available';
		if ( ! array_key_exists( $status, BPR_Dogs_Fields::statuses() ) ) {
			$status = 'available';
		}
		update_post_meta( $post_id, BPR_Dogs_Fields::meta_key( 'status' ), $status );

		// Detail fields, straight off the schema.
		foreach ( BPR_Dogs_Fields::schema() as $field ) {
			if ( ! array_key_exists( $field['key'], $row ) ) {
				continue;
			}

			$raw = $row[ $field['key'] ];

			// Let human-friendly spellings through for checkboxes and selects.
			if ( 'checkbox' === $field['type'] ) {
				$raw = in_array( strtolower( $raw ), array( '1', 'y', 'yes', 'true', 'x' ), true ) ? '1' : '';
			} elseif ( 'select' === $field['type'] ) {
				$raw = strtolower( $raw );
			}

			$clean = BPR_Dogs_Fields::sanitize( $field, $raw );
			$key   = BPR_Dogs_Fields::meta_key( $field['key'] );

			if ( '' === $clean ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $clean );
			}
		}

		// Taxonomies.
		$this->set_terms( $post_id, BPR_Dogs_Post_Type::TAX_SIZE, isset( $row['size'] ) ? $row['size'] : '' );
		$this->set_terms( $post_id, BPR_Dogs_Post_Type::TAX_AGE, isset( $row['age_group'] ) ? $row['age_group'] : '' );
		$this->set_terms( $post_id, BPR_Dogs_Post_Type::TAX_BREED, isset( $row['breed'] ) ? $row['breed'] : '' );

		return $post_id;
	}

	/**
	 * Assign comma-separated terms, matching existing terms by slug or name.
	 *
	 * @param int    $post_id  Dog post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @param string $value    Comma-separated terms.
	 */
	private function set_terms( $post_id, $taxonomy, $value ) {
		$value = trim( $value );

		if ( '' === $value ) {
			return;
		}

		$names    = array_filter( array_map( 'trim', explode( ',', $value ) ) );
		$term_ids = array();

		foreach ( $names as $name ) {
			// Match on slug first so "small" finds "Small (up to 10kg)".
			$term = get_term_by( 'slug', sanitize_title( $name ), $taxonomy );

			if ( ! $term ) {
				$term = get_term_by( 'name', $name, $taxonomy );
			}

			if ( ! $term ) {
				$created = wp_insert_term( $name, $taxonomy );
				if ( is_wp_error( $created ) ) {
					WP_CLI::warning( "Could not create {$taxonomy} term \"{$name}\": " . $created->get_error_message() );
					continue;
				}
				$term_ids[] = (int) $created['term_id'];
				continue;
			}

			$term_ids[] = (int) $term->term_id;
		}

		if ( ! empty( $term_ids ) ) {
			wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
		}
	}

	/**
	 * Photo references for a row.
	 *
	 * @param array<string,string> $row CSV row.
	 * @return string[]
	 */
	private function photo_list( $row ) {
		if ( empty( $row['photos'] ) ) {
			return array();
		}

		// Pipe-separated so filenames containing commas survive.
		$parts = preg_split( '/\s*[|]\s*/', $row['photos'] );

		return array_values( array_filter( array_map( 'trim', (array) $parts ) ) );
	}

	/**
	 * Sideload a dog's photos and set the first as the main photo.
	 *
	 * @param int      $post_id    Dog post ID.
	 * @param string[] $photos     Photo URLs or paths relative to $media_root.
	 * @param string   $media_root Folder relative paths resolve against.
	 * @return int Number of photos newly attached.
	 */
	private function attach_photos( $post_id, $photos, $media_root ) {
		if ( empty( $photos ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attached = 0;

		foreach ( $photos as $photo ) {
			// Skip anything already imported for this dog on an earlier run.
			if ( $this->photo_already_attached( $post_id, $photo ) ) {
				continue;
			}

			$attachment_id = $this->sideload( $post_id, $photo, $media_root );

			if ( is_wp_error( $attachment_id ) ) {
				WP_CLI::warning( "    photo failed ({$photo}): " . $attachment_id->get_error_message() );
				continue;
			}

			update_post_meta( $attachment_id, self::ATTACHMENT_SOURCE_KEY, $photo );

			// Screen readers need something better than "IMG_4821.jpg".
			update_post_meta(
				$attachment_id,
				'_wp_attachment_image_alt',
				sprintf(
					/* translators: %s: dog's name. */
					__( '%s, a dog looking for a home', 'bubbles-dogs' ),
					get_the_title( $post_id )
				)
			);

			++$attached;

			// First successful image becomes the main photo.
			if ( ! has_post_thumbnail( $post_id ) ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
		}

		return $attached;
	}

	/**
	 * Has this exact photo reference already been attached to this dog?
	 *
	 * @param int    $post_id Dog post ID.
	 * @param string $photo   Photo reference.
	 * @return bool
	 */
	private function photo_already_attached( $post_id, $photo ) {
		$existing = get_posts(
			array(
				'post_parent'    => $post_id,
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => self::ATTACHMENT_SOURCE_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off import.
				'meta_value'     => $photo, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- one-off import.
			)
		);

		return ! empty( $existing );
	}

	/**
	 * Sideload one image from a URL or a local file.
	 *
	 * @param int    $post_id    Dog post ID.
	 * @param string $photo      URL or path relative to $media_root.
	 * @param string $media_root Folder relative paths resolve against.
	 * @return int|WP_Error Attachment ID.
	 */
	private function sideload( $post_id, $photo, $media_root ) {
		$is_url = (bool) preg_match( '~^https?://~i', $photo );

		if ( $is_url ) {
			$tmp = download_url( $photo, 60 );

			if ( is_wp_error( $tmp ) ) {
				return $tmp;
			}

			$filename = basename( (string) wp_parse_url( $photo, PHP_URL_PATH ) );
		} else {
			$source = '' !== $media_root ? $media_root . '/' . ltrim( $photo, '/' ) : $photo;

			if ( ! is_readable( $source ) ) {
				return new WP_Error(
					'bpr_photo_missing',
					'file not found — check --media-root points at the unzipped export'
				);
			}

			// media_handle_sideload moves the file, so work on a copy and leave
			// the export untouched.
			$tmp = wp_tempnam( basename( $source ) );

			if ( ! $tmp || ! copy( $source, $tmp ) ) {
				if ( $tmp ) {
					wp_delete_file( $tmp );
				}
				return new WP_Error( 'bpr_photo_copy_failed', 'could not copy file to a temporary location' );
			}

			$filename = basename( $source );
		}

		// Give the file a sane extension if the source had none.
		if ( ! preg_match( '/\.(jpe?g|png|gif|webp|avif)$/i', $filename ) ) {
			$filename .= '.jpg';
		}

		$attachment_id = media_handle_sideload(
			array(
				'name'     => $filename,
				'tmp_name' => $tmp,
			),
			$post_id,
			null,
			array( 'post_title' => get_the_title( $post_id ) )
		);

		if ( is_wp_error( $attachment_id ) ) {
			// media_handle_sideload cleans up on success but not always on error.
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			return $attachment_id;
		}

		return (int) $attachment_id;
	}
}

WP_CLI::add_command( 'bubbles-dogs', 'BPR_Dogs_Import' );
