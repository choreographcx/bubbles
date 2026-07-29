<?php
/**
 * Settings for the dog listings.
 *
 * Deliberately tiny: just the things that differ between sites and shouldn't
 * be hardcoded — where the adoption form lives and what to call the button.
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Options screen under Dogs → Settings.
 */
class BPR_Dogs_Settings {

	const OPTION_GROUP = 'bpr_dogs_settings';
	const OPTION_NAME  = 'bpr_dogs_options';

	/**
	 * Default option values.
	 *
	 * @return array<string,string>
	 */
	public static function defaults() {
		return array(
			'apply_url'           => '',
			'apply_param'         => 'dog',
			'apply_label'         => __( 'Apply to adopt %s', 'bubbles-dogs' ),
			'reserved_notice'     => __( '%s already has an application in progress, but do get in touch — we can suggest other dogs looking for a home.', 'bubbles-dogs' ),

			// Social posting.
			'graph_version'       => 'v23.0',
			'fb_page_id'          => '',
			'ig_user_id'          => '',
			'access_token'        => '',
			'hashtags'            => '#AdoptDontShop #DubaiDogs #BubblesPetRescue #RescueDog #UAE',
			'caption_template_fb' => "Meet {name}! {sex_age}\n\n{bio}\n\nCould you give {name} a home? Read more and apply here: {url}\n\n{hashtags}",
			'caption_template_ig' => "Meet {name}! 🐾 {sex_age}\n\n{bio}\n\n{health}\n\nApply to adopt {name} through the link in our bio.\n\n{hashtags}",
		);
	}

	/**
	 * The Meta access token.
	 *
	 * A `BPR_DOGS_ACCESS_TOKEN` constant in wp-config.php wins over the stored
	 * value. That is the better place for it: wp-config.php is not in git and
	 * not in the database, so the token doesn't travel in site backups or
	 * database exports.
	 *
	 * @return string
	 */
	public static function get_token() {
		if ( defined( 'BPR_DOGS_ACCESS_TOKEN' ) && '' !== (string) BPR_DOGS_ACCESS_TOKEN ) {
			return (string) BPR_DOGS_ACCESS_TOKEN;
		}

		return self::get( 'access_token' );
	}

	/**
	 * Is the token coming from wp-config.php rather than the database?
	 *
	 * @return bool
	 */
	public static function token_is_constant() {
		return defined( 'BPR_DOGS_ACCESS_TOKEN' ) && '' !== (string) BPR_DOGS_ACCESS_TOKEN;
	}

	/**
	 * Read one option.
	 *
	 * @param string $key Option key.
	 * @return string
	 */
	public static function get( $key ) {
		$options  = get_option( self::OPTION_NAME, array() );
		$options  = is_array( $options ) ? $options : array();
		$defaults = self::defaults();

		if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
			return (string) $options[ $key ];
		}

		return isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '';
	}

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Add the settings submenu under Dogs.
	 */
	public static function add_page() {
		add_submenu_page(
			'edit.php?post_type=' . BPR_Dogs_Post_Type::POST_TYPE,
			__( 'Dog listing settings', 'bubbles-dogs' ),
			__( 'Settings', 'bubbles-dogs' ),
			'manage_options',
			'bpr-dogs-settings',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the option and its sanitiser.
	 */
	public static function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitise submitted options.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array<string,string>
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$existing = get_option( self::OPTION_NAME, array() );
		$existing = is_array( $existing ) ? $existing : array();
		$out      = array();

		$out['apply_url']   = isset( $input['apply_url'] ) ? esc_url_raw( trim( (string) $input['apply_url'] ) ) : '';
		$out['apply_param'] = isset( $input['apply_param'] ) ? sanitize_key( $input['apply_param'] ) : 'dog';
		$out['apply_label'] = isset( $input['apply_label'] ) ? sanitize_text_field( $input['apply_label'] ) : '';

		$out['reserved_notice'] = isset( $input['reserved_notice'] )
			? sanitize_text_field( $input['reserved_notice'] )
			: '';

		if ( '' === $out['apply_param'] ) {
			$out['apply_param'] = 'dog';
		}

		// --- Social ---

		// Graph API versions look like "v23.0" — reject anything else so a typo
		// can't turn into a request to an arbitrary URL path.
		$version = isset( $input['graph_version'] ) ? trim( (string) $input['graph_version'] ) : '';
		$out['graph_version'] = preg_match( '/^v\d{1,3}\.\d{1,3}$/', $version ) ? $version : 'v23.0';

		// Account IDs are numeric strings; keep them as strings because they
		// exceed PHP's integer range on some platforms.
		$out['fb_page_id'] = isset( $input['fb_page_id'] )
			? preg_replace( '/\D/', '', (string) $input['fb_page_id'] )
			: '';
		$out['ig_user_id'] = isset( $input['ig_user_id'] )
			? preg_replace( '/\D/', '', (string) $input['ig_user_id'] )
			: '';

		// The token field renders empty, so an empty submission means "leave it
		// alone" rather than "delete it". Clearing is an explicit checkbox.
		if ( ! empty( $input['remove_token'] ) ) {
			$out['access_token'] = '';
		} elseif ( isset( $input['access_token'] ) && '' !== trim( (string) $input['access_token'] ) ) {
			// Tokens are opaque; strip whitespace but nothing else.
			$out['access_token'] = preg_replace( '/\s+/', '', (string) $input['access_token'] );
		} else {
			$out['access_token'] = isset( $existing['access_token'] ) ? (string) $existing['access_token'] : '';
		}

		$out['hashtags'] = isset( $input['hashtags'] ) ? sanitize_text_field( $input['hashtags'] ) : '';

		foreach ( array( 'caption_template_fb', 'caption_template_ig' ) as $key ) {
			$out[ $key ] = isset( $input[ $key ] )
				? sanitize_textarea_field( $input[ $key ] )
				: '';
		}

		return $out;
	}

	/**
	 * Render the settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Dog listing settings', 'bubbles-dogs' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( self::OPTION_GROUP ); ?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="bpr_apply_url"><?php esc_html_e( 'Adoption form page', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<input type="url" class="large-text code" id="bpr_apply_url"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[apply_url]"
									value="<?php echo esc_attr( self::get( 'apply_url' ) ); ?>"
									placeholder="https://www.bubblespetrescue.ae/adoption-application/" />
								<p class="description">
									<?php esc_html_e( 'The page holding your Forminator adoption application. Leave blank to hide the apply button.', 'bubbles-dogs' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_apply_param"><?php esc_html_e( 'Query parameter', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text code" id="bpr_apply_param"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[apply_param]"
									value="<?php echo esc_attr( self::get( 'apply_param' ) ); ?>" />
								<p class="description">
									<?php
									esc_html_e(
										'The apply button sends the dog\'s name in this parameter, e.g. ?dog=Bubbles. In Forminator, add a Hidden field to your adoption form, set its default value to "Query parameter" and enter this name — every application then records which dog it is for.',
										'bubbles-dogs'
									);
									?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_apply_label"><?php esc_html_e( 'Button text', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text" id="bpr_apply_label"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[apply_label]"
									value="<?php echo esc_attr( self::get( 'apply_label' ) ); ?>" />
								<p class="description">
									<?php esc_html_e( '%s is replaced with the dog\'s name.', 'bubbles-dogs' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_reserved_notice"><?php esc_html_e( 'Reserved message', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<textarea class="large-text" rows="3" id="bpr_reserved_notice"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[reserved_notice]"><?php echo esc_textarea( self::get( 'reserved_notice' ) ); ?></textarea>
								<p class="description">
									<?php esc_html_e( 'Shown instead of the apply button when a dog is reserved. %s is replaced with the dog\'s name.', 'bubbles-dogs' ); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>

				<h2><?php esc_html_e( 'Sharing to Facebook and Instagram', 'bubbles-dogs' ); ?></h2>

				<p>
					<?php
					esc_html_e(
						'Once these are filled in, a "Share this dog" box appears on every dog, where you choose which accounts to post to. Nothing is ever posted automatically — someone always has to press the button.',
						'bubbles-dogs'
					);
					?>
				</p>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="bpr_fb_page_id"><?php esc_html_e( 'Facebook Page ID', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text code" id="bpr_fb_page_id"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[fb_page_id]"
									value="<?php echo esc_attr( self::get( 'fb_page_id' ) ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Find it in Meta Business Suite under your Page settings, or on the Page\'s About tab. Numbers only. Leave blank to turn Facebook sharing off.', 'bubbles-dogs' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_ig_user_id"><?php esc_html_e( 'Instagram account ID', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text code" id="bpr_ig_user_id"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[ig_user_id]"
									value="<?php echo esc_attr( self::get( 'ig_user_id' ) ); ?>" />
								<p class="description">
									<?php esc_html_e( 'This is the Instagram Business account ID linked to the Page — not the @handle. Leave blank to turn Instagram sharing off.', 'bubbles-dogs' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_access_token"><?php esc_html_e( 'Access token', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<?php if ( self::token_is_constant() ) : ?>
									<p>
										<strong><?php esc_html_e( 'Set in wp-config.php.', 'bubbles-dogs' ); ?></strong>
										<?php esc_html_e( 'That takes priority over anything saved here, which is the safer place for it.', 'bubbles-dogs' ); ?>
									</p>
								<?php else : ?>
									<input type="password" class="large-text code" id="bpr_access_token"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[access_token]"
										value="" autocomplete="new-password"
										placeholder="<?php echo '' !== self::get( 'access_token' ) ? esc_attr__( 'A token is saved — leave blank to keep it', 'bubbles-dogs' ) : esc_attr__( 'Paste the Page access token', 'bubbles-dogs' ); ?>" />

									<?php if ( '' !== self::get( 'access_token' ) ) : ?>
										<p>
											<label>
												<input type="checkbox" value="1"
													name="<?php echo esc_attr( self::OPTION_NAME ); ?>[remove_token]" />
												<?php esc_html_e( 'Delete the saved token', 'bubbles-dogs' ); ?>
											</label>
										</p>
									<?php endif; ?>

									<p class="description">
										<?php
										esc_html_e(
											'A long-lived Page access token, with the pages_manage_posts, pages_read_engagement, instagram_basic and instagram_content_publish permissions.',
											'bubbles-dogs'
										);
										?>
									</p>
									<p class="description">
										<?php
										printf(
											/* translators: %s: a line of PHP to add to wp-config.php. */
											esc_html__( 'Better still, keep it out of the database entirely by adding this to wp-config.php: %s', 'bubbles-dogs' ),
											'<code>define( \'BPR_DOGS_ACCESS_TOKEN\', \'your-token\' );</code>'
										);
										?>
									</p>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_graph_version"><?php esc_html_e( 'Graph API version', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<input type="text" class="small-text code" id="bpr_graph_version"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[graph_version]"
									value="<?php echo esc_attr( self::get( 'graph_version' ) ); ?>" />
								<p class="description">
									<?php
									esc_html_e(
										'Meta retires each version after about two years. If sharing starts failing with a message about an unsupported version, put the current one here — you can check which that is in the Meta developer dashboard.',
										'bubbles-dogs'
									);
									?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_hashtags"><?php esc_html_e( 'Hashtags', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<input type="text" class="large-text" id="bpr_hashtags"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hashtags]"
									value="<?php echo esc_attr( self::get( 'hashtags' ) ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Inserted wherever a caption template uses {hashtags}.', 'bubbles-dogs' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_caption_fb"><?php esc_html_e( 'Facebook caption', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<textarea class="large-text code" rows="7" id="bpr_caption_fb"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[caption_template_fb]"><?php echo esc_textarea( self::get( 'caption_template_fb' ) ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bpr_caption_ig"><?php esc_html_e( 'Instagram caption', 'bubbles-dogs' ); ?></label>
							</th>
							<td>
								<textarea class="large-text code" rows="7" id="bpr_caption_ig"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[caption_template_ig]"><?php echo esc_textarea( self::get( 'caption_template_ig' ) ); ?></textarea>
								<p class="description">
									<?php esc_html_e( 'Kept separate from the Facebook one because Instagram captions cannot contain clickable links — hence "link in our bio" rather than a URL.', 'bubbles-dogs' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Placeholders', 'bubbles-dogs' ); ?></th>
							<td>
								<ul class="bpr-dogs-placeholders">
									<?php foreach ( BPR_Dogs_Social::placeholders() as $tag => $meaning ) : ?>
										<li><code><?php echo esc_html( $tag ); ?></code> — <?php echo esc_html( $meaning ); ?></li>
									<?php endforeach; ?>
								</ul>
								<p class="description">
									<?php esc_html_e( 'A placeholder with nothing behind it simply disappears, and the blank line it leaves is tidied up.', 'bubbles-dogs' ); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Shortcodes', 'bubbles-dogs' ); ?></h2>
			<p><?php esc_html_e( 'Drop these into any page or post:', 'bubbles-dogs' ); ?></p>
			<ul class="ul-disc">
				<li><code>[bubbles_dogs]</code> — <?php esc_html_e( 'a grid of all dogs currently looking for a home.', 'bubbles-dogs' ); ?></li>
				<li><code>[bubbles_dogs status="adopted"]</code> — <?php esc_html_e( 'a Happy endings grid.', 'bubbles-dogs' ); ?></li>
				<li><code>[bubbles_dogs size="small" limit="6"]</code> — <?php esc_html_e( 'filter by size, age group or breed, and cap how many show.', 'bubbles-dogs' ); ?></li>
				<li><code>[bubbles_dogs filters="yes"]</code> — <?php esc_html_e( 'include the size and age filter bar.', 'bubbles-dogs' ); ?></li>
			</ul>
		</div>
		<?php
	}
}
