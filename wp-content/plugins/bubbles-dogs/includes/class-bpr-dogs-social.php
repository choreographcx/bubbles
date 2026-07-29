<?php
/**
 * Posting dogs out to Facebook and Instagram via the Meta Graph API.
 *
 * Nothing in here runs on its own. Sharing is always an explicit action taken
 * by a person in wp-admin, per dog, per platform — see BPR_Dogs_Social_Admin.
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Graph API client and caption builder.
 */
class BPR_Dogs_Social {

	/**
	 * Meta key holding the share history for a dog.
	 *
	 * Shape: array<string platform, array{id, permalink, time, caption}>.
	 */
	const HISTORY_KEY = '_bpr_dog_social_history';

	/**
	 * Instagram's accepted aspect ratio range (width / height).
	 *
	 * Anything outside this is rejected by the API with an unhelpful error, so
	 * it's worth checking before we upload.
	 */
	const IG_MIN_RATIO = 0.8;   // 4:5 portrait.
	const IG_MAX_RATIO = 1.91;  // Landscape.

	/**
	 * Instagram limits.
	 */
	const IG_MAX_BYTES   = 8388608; // 8 MB.
	const IG_MIN_WIDTH   = 320;
	const IG_MAX_CHILDREN = 10;
	const IG_CAPTION_MAX = 2200;

	/**
	 * How long to wait for Instagram to finish processing a container.
	 */
	const IG_POLL_ATTEMPTS = 10;
	const IG_POLL_SECONDS  = 3;

	/**
	 * Platforms this class can post to.
	 *
	 * @return array<string,string>
	 */
	public static function platforms() {
		return array(
			'facebook'  => __( 'Facebook Page', 'bubbles-dogs' ),
			'instagram' => __( 'Instagram', 'bubbles-dogs' ),
		);
	}

	/**
	 * Is a platform configured well enough to post to?
	 *
	 * @param string $platform 'facebook' or 'instagram'.
	 * @return bool
	 */
	public static function is_configured( $platform ) {
		if ( '' === BPR_Dogs_Settings::get_token() ) {
			return false;
		}

		if ( 'facebook' === $platform ) {
			return '' !== BPR_Dogs_Settings::get( 'fb_page_id' );
		}

		if ( 'instagram' === $platform ) {
			return '' !== BPR_Dogs_Settings::get( 'ig_user_id' );
		}

		return false;
	}

	/**
	 * The Graph API base URL for a node.
	 *
	 * @param string $node Node path, e.g. "12345/photos".
	 * @return string
	 */
	private static function endpoint( $node ) {
		$version = BPR_Dogs_Settings::get( 'graph_version' );
		return 'https://graph.facebook.com/' . rawurlencode( $version ) . '/' . $node;
	}

	/**
	 * Call the Graph API.
	 *
	 * @param string               $node   Node path.
	 * @param array<string,mixed>  $params Request parameters, minus the token.
	 * @param string               $method GET or POST.
	 * @return array<string,mixed>|WP_Error Decoded response body.
	 */
	private static function request( $node, $params = array(), $method = 'POST' ) {
		$token = BPR_Dogs_Settings::get_token();

		if ( '' === $token ) {
			return new WP_Error(
				'bpr_no_token',
				__( 'No access token saved. Add one under Dogs → Settings.', 'bubbles-dogs' )
			);
		}

		$params['access_token'] = $token;
		$url                    = self::endpoint( $node );

		$args = array(
			'timeout'     => 45,
			'redirection' => 3,
		);

		if ( 'GET' === $method ) {
			$response = wp_remote_get( add_query_arg( $params, $url ), $args );
		} else {
			$args['body'] = $params;
			$response     = wp_remote_post( $url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			return new WP_Error(
				'bpr_bad_response',
				sprintf(
					/* translators: %d: HTTP status code. */
					__( 'Meta returned an unreadable response (HTTP %d).', 'bubbles-dogs' ),
					$code
				)
			);
		}

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'bpr_graph_error', self::describe_error( $body['error'] ) );
		}

		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error(
				'bpr_http_error',
				sprintf(
					/* translators: %d: HTTP status code. */
					__( 'Meta rejected the request (HTTP %d).', 'bubbles-dogs' ),
					$code
				)
			);
		}

		return $body;
	}

	/**
	 * Turn a Graph API error into something a human can act on.
	 *
	 * Meta's messages are written for developers, and the common failures here
	 * have specific causes worth naming.
	 *
	 * @param array<string,mixed> $error Error object from the response.
	 * @return string
	 */
	private static function describe_error( $error ) {
		$message = isset( $error['message'] ) ? (string) $error['message'] : __( 'Unknown error.', 'bubbles-dogs' );
		$code    = isset( $error['code'] ) ? (int) $error['code'] : 0;
		$subcode = isset( $error['error_subcode'] ) ? (int) $error['error_subcode'] : 0;

		$hint = '';

		if ( 190 === $code ) {
			$hint = __( 'The access token has expired or been revoked — generate a new one and save it under Dogs → Settings.', 'bubbles-dogs' );
		} elseif ( 200 === $code || 10 === $code ) {
			$hint = __( 'The token is missing a permission. It needs pages_manage_posts and pages_read_engagement, plus instagram_basic and instagram_content_publish for Instagram.', 'bubbles-dogs' );
		} elseif ( 9007 === $subcode || 2207051 === $subcode ) {
			$hint = __( 'Instagram could not fetch the image. Check the site is publicly reachable — this will not work on a password-protected or staging site.', 'bubbles-dogs' );
		} elseif ( 4 === $code || 17 === $code || 32 === $code ) {
			$hint = __( 'Meta is rate limiting the account. Wait a while before trying again.', 'bubbles-dogs' );
		} elseif ( 1 === $code && false !== stripos( $message, 'aspect ratio' ) ) {
			$hint = __( 'Instagram rejected the image shape. Crop it to between 4:5 and 1.91:1.', 'bubbles-dogs' );
		}

		return '' !== $hint ? $message . ' — ' . $hint : $message;
	}

	// -----------------------------------------------------------------------
	// Captions
	// -----------------------------------------------------------------------

	/**
	 * Available caption placeholders and what they mean.
	 *
	 * @return array<string,string>
	 */
	public static function placeholders() {
		return array(
			'{name}'     => __( "The dog's name", 'bubbles-dogs' ),
			'{sex}'      => __( 'Female / Male', 'bubbles-dogs' ),
			'{age}'      => __( 'Age, worked out from date of birth where set', 'bubbles-dogs' ),
			'{sex_age}'  => __( 'Sex and age together, e.g. "Female, about 2 years"', 'bubbles-dogs' ),
			'{size}'     => __( 'Size term', 'bubbles-dogs' ),
			'{breed}'    => __( 'Breed term(s)', 'bubbles-dogs' ),
			'{weight}'   => __( 'Weight in kg', 'bubbles-dogs' ),
			'{location}' => __( 'Where the dog is currently staying', 'bubbles-dogs' ),
			'{bio}'      => __( "The dog's write-up", 'bubbles-dogs' ),
			'{health}'   => __( 'Vaccinated / spayed / microchipped summary', 'bubbles-dogs' ),
			'{url}'      => __( "Link to the dog's page (not clickable on Instagram)", 'bubbles-dogs' ),
			'{hashtags}' => __( 'Your saved hashtags', 'bubbles-dogs' ),
		);
	}

	/**
	 * Build a caption for a dog on a platform.
	 *
	 * @param int    $post_id  Dog post ID.
	 * @param string $platform 'facebook' or 'instagram'.
	 * @return string
	 */
	public static function build_caption( $post_id, $platform ) {
		$template = 'instagram' === $platform
			? BPR_Dogs_Settings::get( 'caption_template_ig' )
			: BPR_Dogs_Settings::get( 'caption_template_fb' );

		$sex = BPR_Dogs_Fields::display_value( $post_id, BPR_Dogs_Fields::get_field( 'sex' ) );
		$age = BPR_Dogs_Fields::age_label( $post_id );

		$sex_age = implode( ', ', array_filter( array( $sex, $age ) ) );

		$health = array();
		foreach ( array( 'vaccinated', 'sterilised', 'microchipped' ) as $key ) {
			$field = BPR_Dogs_Fields::get_field( $key );
			if ( $field && '' !== BPR_Dogs_Fields::display_value( $post_id, $field ) ) {
				$health[] = strtolower( $field['label'] );
			}
		}

		$replacements = array(
			'{name}'     => get_the_title( $post_id ),
			'{sex}'      => $sex,
			'{age}'      => $age,
			'{sex_age}'  => $sex_age,
			'{size}'     => self::term_names( $post_id, BPR_Dogs_Post_Type::TAX_SIZE ),
			'{breed}'    => self::term_names( $post_id, BPR_Dogs_Post_Type::TAX_BREED ),
			'{weight}'   => BPR_Dogs_Fields::display_value( $post_id, BPR_Dogs_Fields::get_field( 'weight_kg' ) ),
			'{location}' => BPR_Dogs_Fields::get( $post_id, 'location' ),
			'{bio}'      => self::plain_bio( $post_id ),
			'{health}'   => empty( $health ) ? '' : ucfirst( implode( ', ', $health ) ),
			'{url}'      => (string) get_permalink( $post_id ),
			'{hashtags}' => BPR_Dogs_Settings::get( 'hashtags' ),
		);

		$caption = strtr( (string) $template, $replacements );

		// Collapse the gaps left by placeholders that had no value.
		$caption = preg_replace( "/[ \t]+\n/", "\n", $caption );
		$caption = preg_replace( "/\n{3,}/", "\n\n", (string) $caption );

		return trim( (string) $caption );
	}

	/**
	 * Comma-separated term names for a taxonomy.
	 *
	 * @param int    $post_id  Dog post ID.
	 * @param string $taxonomy Taxonomy.
	 * @return string
	 */
	private static function term_names( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '';
		}

		return implode( ', ', wp_list_pluck( $terms, 'name' ) );
	}

	/**
	 * The dog's write-up as plain text, fit for a social caption.
	 *
	 * @param int $post_id Dog post ID.
	 * @return string
	 */
	private static function plain_bio( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return '';
		}

		$content = strip_shortcodes( (string) $post->post_content );
		$content = wp_strip_all_tags( (string) apply_filters( 'the_content', $content ) );
		$content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );

		// Blank lines survive, runs of whitespace don't.
		$content = preg_replace( "/[ \t]+/", ' ', $content );
		$content = preg_replace( "/\n{3,}/", "\n\n", (string) $content );

		return trim( (string) $content );
	}

	// -----------------------------------------------------------------------
	// Images
	// -----------------------------------------------------------------------

	/**
	 * The images to post for a dog: featured image first, then attachments.
	 *
	 * @param int $post_id Dog post ID.
	 * @param int $limit   Maximum images to return.
	 * @return int[] Attachment IDs.
	 */
	public static function image_ids( $post_id, $limit = self::IG_MAX_CHILDREN ) {
		$ids       = array();
		$thumbnail = (int) get_post_thumbnail_id( $post_id );

		if ( $thumbnail ) {
			$ids[] = $thumbnail;
		}

		$attached = get_posts(
			array(
				'post_parent'    => $post_id,
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => $limit,
				'orderby'        => 'menu_order ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
				'exclude'        => $thumbnail ? array( $thumbnail ) : array(),
			)
		);

		foreach ( $attached as $id ) {
			$ids[] = (int) $id;
		}

		return array_slice( array_values( array_unique( $ids ) ), 0, max( 1, $limit ) );
	}

	/**
	 * A publicly reachable URL for an image, suitable for the given platform.
	 *
	 * Instagram fetches the URL itself, so it has to be public and the image
	 * has to satisfy its shape and size rules. This checks those up front
	 * rather than letting the API fail with something cryptic.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $platform      'facebook' or 'instagram'.
	 * @return string|WP_Error URL on success.
	 */
	public static function image_url( $attachment_id, $platform ) {
		$meta = wp_get_attachment_metadata( $attachment_id );
		$name = get_the_title( $attachment_id );

		if ( 'instagram' !== $platform ) {
			$url = wp_get_attachment_image_url( $attachment_id, 'large' );
			if ( ! $url ) {
				$url = wp_get_attachment_url( $attachment_id );
			}
			return $url ? $url : new WP_Error(
				'bpr_no_image_url',
				sprintf(
					/* translators: %s: image name. */
					__( 'Could not work out a URL for the image "%s".', 'bubbles-dogs' ),
					$name
				)
			);
		}

		// Instagram: check the shape of the original, since every generated
		// size keeps the same aspect ratio.
		$width  = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
		$height = isset( $meta['height'] ) ? (int) $meta['height'] : 0;

		if ( $width > 0 && $height > 0 ) {
			$ratio = $width / $height;

			if ( $ratio < self::IG_MIN_RATIO || $ratio > self::IG_MAX_RATIO ) {
				return new WP_Error(
					'bpr_ig_aspect',
					sprintf(
						/* translators: 1: image name, 2: aspect ratio. */
						__( 'Instagram will not accept "%1$s" — it is %2$s to 1, and Instagram only allows between 0.8 and 1.91 to 1. Crop it (square is safest) and try again.', 'bubbles-dogs' ),
						$name,
						number_format( $ratio, 2 )
					)
				);
			}
		}

		// Pick the largest generated size that is comfortably within the file
		// size limit and wide enough for Instagram.
		$candidates = array( 'full', 'large', 'medium_large', 'medium' );

		foreach ( $candidates as $size ) {
			$src = wp_get_attachment_image_src( $attachment_id, $size );

			if ( ! $src || empty( $src[0] ) ) {
				continue;
			}

			if ( (int) $src[1] < self::IG_MIN_WIDTH ) {
				continue;
			}

			$path = self::local_path( $attachment_id, $size, $meta );

			if ( $path && file_exists( $path ) && filesize( $path ) > self::IG_MAX_BYTES ) {
				// Too heavy — try the next size down.
				continue;
			}

			return $src[0];
		}

		return new WP_Error(
			'bpr_ig_no_size',
			sprintf(
				/* translators: %s: image name. */
				__( 'No usable version of "%s" — Instagram needs an image at least 320px wide and under 8MB.', 'bubbles-dogs' ),
				$name
			)
		);
	}

	/**
	 * Absolute path to a generated image size, for a file size check.
	 *
	 * @param int                 $attachment_id Attachment ID.
	 * @param string              $size          Size name.
	 * @param array<string,mixed>|false $meta    Attachment metadata.
	 * @return string|null
	 */
	private static function local_path( $attachment_id, $size, $meta ) {
		$original = get_attached_file( $attachment_id );

		if ( ! $original ) {
			return null;
		}

		if ( 'full' === $size || ! is_array( $meta ) || empty( $meta['sizes'][ $size ]['file'] ) ) {
			return $original;
		}

		return dirname( $original ) . '/' . $meta['sizes'][ $size ]['file'];
	}

	// -----------------------------------------------------------------------
	// Posting
	// -----------------------------------------------------------------------

	/**
	 * Share a dog to one platform.
	 *
	 * @param int    $post_id  Dog post ID.
	 * @param string $platform 'facebook' or 'instagram'.
	 * @param string $caption  Caption to post.
	 * @return array{id:string,permalink:string}|WP_Error
	 */
	public static function share( $post_id, $platform, $caption ) {
		if ( ! self::is_configured( $platform ) ) {
			return new WP_Error(
				'bpr_not_configured',
				__( 'That platform is not set up yet. Add the account details under Dogs → Settings.', 'bubbles-dogs' )
			);
		}

		if ( 'publish' !== get_post_status( $post_id ) ) {
			return new WP_Error(
				'bpr_not_published',
				__( 'Publish this dog first — the post links back to their page, which needs to be live.', 'bubbles-dogs' )
			);
		}

		$caption = trim( $caption );

		if ( '' === $caption ) {
			return new WP_Error( 'bpr_empty_caption', __( 'The caption is empty.', 'bubbles-dogs' ) );
		}

		$result = 'instagram' === $platform
			? self::share_to_instagram( $post_id, $caption )
			: self::share_to_facebook( $post_id, $caption );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		self::record( $post_id, $platform, $result, $caption );

		return $result;
	}

	/**
	 * Post to the Facebook Page.
	 *
	 * One image becomes a photo post. Several become unpublished photos
	 * attached to a single feed post, which is how Facebook does albums.
	 * No images at all still works — it just posts the text.
	 *
	 * @param int    $post_id Dog post ID.
	 * @param string $caption Caption.
	 * @return array{id:string,permalink:string}|WP_Error
	 */
	private static function share_to_facebook( $post_id, $caption ) {
		$page_id = BPR_Dogs_Settings::get( 'fb_page_id' );
		$images  = self::image_ids( $post_id );

		if ( empty( $images ) ) {
			$response = self::request(
				$page_id . '/feed',
				array(
					'message' => $caption,
					'link'    => get_permalink( $post_id ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			return self::facebook_result( $response );
		}

		if ( 1 === count( $images ) ) {
			$url = self::image_url( $images[0], 'facebook' );

			if ( is_wp_error( $url ) ) {
				return $url;
			}

			$response = self::request(
				$page_id . '/photos',
				array(
					'url'     => $url,
					'message' => $caption,
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			return self::facebook_result( $response );
		}

		// Several images: upload each unpublished, then attach to one post.
		$attached = array();

		foreach ( $images as $attachment_id ) {
			$url = self::image_url( $attachment_id, 'facebook' );

			if ( is_wp_error( $url ) ) {
				// Skip an individual bad image rather than losing the whole post.
				continue;
			}

			$response = self::request(
				$page_id . '/photos',
				array(
					'url'       => $url,
					'published' => 'false',
				)
			);

			if ( is_wp_error( $response ) || empty( $response['id'] ) ) {
				continue;
			}

			$attached[] = array( 'media_fbid' => (string) $response['id'] );
		}

		if ( empty( $attached ) ) {
			return new WP_Error(
				'bpr_fb_no_photos',
				__( 'None of the photos could be uploaded to Facebook.', 'bubbles-dogs' )
			);
		}

		$params = array( 'message' => $caption );

		// Graph expects attached_media[0], attached_media[1], ... each JSON.
		foreach ( $attached as $index => $item ) {
			$params[ 'attached_media[' . $index . ']' ] = wp_json_encode( $item );
		}

		$response = self::request( $page_id . '/feed', $params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return self::facebook_result( $response );
	}

	/**
	 * Normalise a Facebook post response into an id and permalink.
	 *
	 * @param array<string,mixed> $response Graph response.
	 * @return array{id:string,permalink:string}
	 */
	private static function facebook_result( $response ) {
		// A photo post returns both `id` (the photo) and `post_id` (the story).
		$id = '';

		if ( ! empty( $response['post_id'] ) ) {
			$id = (string) $response['post_id'];
		} elseif ( ! empty( $response['id'] ) ) {
			$id = (string) $response['id'];
		}

		return array(
			'id'        => $id,
			'permalink' => '' !== $id ? 'https://www.facebook.com/' . $id : '',
		);
	}

	/**
	 * Post to Instagram.
	 *
	 * Instagram publishing is a two-step dance: build a media container, wait
	 * for Instagram to finish processing it, then publish it. Carousels add a
	 * layer, with one container per image plus a parent.
	 *
	 * @param int    $post_id Dog post ID.
	 * @param string $caption Caption.
	 * @return array{id:string,permalink:string}|WP_Error
	 */
	private static function share_to_instagram( $post_id, $caption ) {
		$ig_id  = BPR_Dogs_Settings::get( 'ig_user_id' );
		$images = self::image_ids( $post_id, self::IG_MAX_CHILDREN );

		if ( empty( $images ) ) {
			return new WP_Error(
				'bpr_ig_no_image',
				__( 'Instagram posts need at least one photo. Set a main photo for this dog first.', 'bubbles-dogs' )
			);
		}

		if ( mb_strlen( $caption ) > self::IG_CAPTION_MAX ) {
			return new WP_Error(
				'bpr_ig_caption_long',
				sprintf(
					/* translators: 1: caption length, 2: maximum length. */
					__( 'The caption is %1$d characters and Instagram allows %2$d. Shorten it and try again.', 'bubbles-dogs' ),
					mb_strlen( $caption ),
					self::IG_CAPTION_MAX
				)
			);
		}

		// Resolve every image URL first, so we fail before creating anything.
		$urls   = array();
		$errors = array();

		foreach ( $images as $attachment_id ) {
			$url = self::image_url( $attachment_id, 'instagram' );

			if ( is_wp_error( $url ) ) {
				$errors[] = $url->get_error_message();
				continue;
			}

			$urls[] = $url;
		}

		if ( empty( $urls ) ) {
			return new WP_Error(
				'bpr_ig_no_usable_image',
				implode( ' ', $errors )
			);
		}

		if ( 1 === count( $urls ) ) {
			$container = self::request(
				$ig_id . '/media',
				array(
					'image_url' => $urls[0],
					'caption'   => $caption,
				)
			);

			if ( is_wp_error( $container ) ) {
				return $container;
			}

			$creation_id = isset( $container['id'] ) ? (string) $container['id'] : '';
		} else {
			// One container per image, flagged as carousel children.
			$children = array();

			foreach ( $urls as $url ) {
				$child = self::request(
					$ig_id . '/media',
					array(
						'image_url'        => $url,
						'is_carousel_item' => 'true',
					)
				);

				if ( is_wp_error( $child ) || empty( $child['id'] ) ) {
					continue;
				}

				$children[] = (string) $child['id'];
			}

			if ( count( $children ) < 2 ) {
				return new WP_Error(
					'bpr_ig_carousel_failed',
					__( 'Instagram would not accept enough of the photos to build a carousel.', 'bubbles-dogs' )
				);
			}

			$container = self::request(
				$ig_id . '/media',
				array(
					'media_type' => 'CAROUSEL',
					'children'   => implode( ',', $children ),
					'caption'    => $caption,
				)
			);

			if ( is_wp_error( $container ) ) {
				return $container;
			}

			$creation_id = isset( $container['id'] ) ? (string) $container['id'] : '';
		}

		if ( '' === $creation_id ) {
			return new WP_Error(
				'bpr_ig_no_container',
				__( 'Instagram did not return a media container.', 'bubbles-dogs' )
			);
		}

		$ready = self::wait_for_container( $creation_id );

		if ( is_wp_error( $ready ) ) {
			return $ready;
		}

		$published = self::request(
			$ig_id . '/media_publish',
			array( 'creation_id' => $creation_id )
		);

		if ( is_wp_error( $published ) ) {
			return $published;
		}

		$media_id = isset( $published['id'] ) ? (string) $published['id'] : '';

		return array(
			'id'        => $media_id,
			'permalink' => '' !== $media_id ? self::instagram_permalink( $media_id ) : '',
		);
	}

	/**
	 * Wait for Instagram to finish processing a media container.
	 *
	 * @param string $creation_id Container ID.
	 * @return true|WP_Error
	 */
	private static function wait_for_container( $creation_id ) {
		for ( $attempt = 0; $attempt < self::IG_POLL_ATTEMPTS; $attempt++ ) {
			$status = self::request(
				$creation_id,
				array( 'fields' => 'status_code,status' ),
				'GET'
			);

			if ( is_wp_error( $status ) ) {
				return $status;
			}

			$code = isset( $status['status_code'] ) ? (string) $status['status_code'] : '';

			if ( 'FINISHED' === $code ) {
				return true;
			}

			if ( 'ERROR' === $code ) {
				return new WP_Error(
					'bpr_ig_container_error',
					isset( $status['status'] )
						? (string) $status['status']
						: __( 'Instagram could not process the image.', 'bubbles-dogs' )
				);
			}

			if ( 'EXPIRED' === $code ) {
				return new WP_Error(
					'bpr_ig_container_expired',
					__( 'The upload expired before it could be published. Try again.', 'bubbles-dogs' )
				);
			}

			// IN_PROGRESS — give it a moment. This is a deliberate, bounded
			// wait inside an action the user has just asked for.
			sleep( self::IG_POLL_SECONDS );
		}

		return new WP_Error(
			'bpr_ig_timeout',
			__( 'Instagram is still processing the image. It may yet appear — check the profile before trying again, so you do not post twice.', 'bubbles-dogs' )
		);
	}

	/**
	 * Look up the public permalink for a published Instagram media item.
	 *
	 * @param string $media_id Media ID.
	 * @return string Empty string when it can't be read.
	 */
	private static function instagram_permalink( $media_id ) {
		$response = self::request( $media_id, array( 'fields' => 'permalink' ), 'GET' );

		if ( is_wp_error( $response ) || empty( $response['permalink'] ) ) {
			return '';
		}

		return (string) $response['permalink'];
	}

	// -----------------------------------------------------------------------
	// History
	// -----------------------------------------------------------------------

	/**
	 * Record a successful share.
	 *
	 * @param int                            $post_id  Dog post ID.
	 * @param string                         $platform Platform key.
	 * @param array{id:string,permalink:string} $result Share result.
	 * @param string                         $caption  Caption used.
	 */
	private static function record( $post_id, $platform, $result, $caption ) {
		$history = self::history( $post_id );

		$history[ $platform ] = array(
			'id'        => $result['id'],
			'permalink' => $result['permalink'],
			'time'      => current_time( 'mysql' ),
			'caption'   => $caption,
			'user'      => get_current_user_id(),
		);

		update_post_meta( $post_id, self::HISTORY_KEY, $history );
	}

	/**
	 * Share history for a dog.
	 *
	 * @param int $post_id Dog post ID.
	 * @return array<string,array<string,mixed>>
	 */
	public static function history( $post_id ) {
		$history = get_post_meta( $post_id, self::HISTORY_KEY, true );

		return is_array( $history ) ? $history : array();
	}
}
