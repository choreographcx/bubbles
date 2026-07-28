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
			'apply_url'       => '',
			'apply_param'     => 'dog',
			'apply_label'     => __( 'Apply to adopt %s', 'bubbles-dogs' ),
			'reserved_notice' => __( '%s already has an application in progress, but do get in touch — we can suggest other dogs looking for a home.', 'bubbles-dogs' ),
		);
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
		$input = is_array( $input ) ? $input : array();
		$out   = array();

		$out['apply_url']   = isset( $input['apply_url'] ) ? esc_url_raw( trim( (string) $input['apply_url'] ) ) : '';
		$out['apply_param'] = isset( $input['apply_param'] ) ? sanitize_key( $input['apply_param'] ) : 'dog';
		$out['apply_label'] = isset( $input['apply_label'] ) ? sanitize_text_field( $input['apply_label'] ) : '';

		$out['reserved_notice'] = isset( $input['reserved_notice'] )
			? sanitize_text_field( $input['reserved_notice'] )
			: '';

		if ( '' === $out['apply_param'] ) {
			$out['apply_param'] = 'dog';
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
