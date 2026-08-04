<?php

use InstagramFeed\Admin\SBI_Admin_Notices;
use InstagramFeed\Builder\SBI_Db;
use InstagramFeed\Builder\SBI_Feed_Builder;
use InstagramFeed\Helpers\Util;

/**
 * Instagram Feed block with live preview.
 *
 * @since 2.3/5.4
 */
class SB_Instagram_Blocks
{
	/**
	 * Checking if is Gutenberg REST API call.
	 *
	 * @return bool True if is Gutenberg REST API call.
	 * @since 2.3/5.4
	 */
	public static function is_gb_editor()
	{
		// TODO: Find a better way to check if is GB editor API call.
		return defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['context']) && 'edit' === $_REQUEST['context']; // phpcs:ignore
	}

	/**
	 * Indicates if current integration is allowed to load.
	 *
	 * @return bool
	 * @since 1.8
	 */
	public function allow_load()
	{
		return function_exists('register_block_type');
	}

	/**
	 * Loads an integration.
	 *
	 * @since 2.3/5.4
	 */
	public function load()
	{
		$this->hooks();

		require_once trailingslashit(SBI_PLUGIN_DIR) . 'inc/admin/blocks/SBI_Modern_Feed_Block.php';
		$modern_block = new \InstagramFeed\Admin\Blocks\SBI_Modern_Feed_Block();
		$modern_block->register_hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 2.3/5.4
	 */
	protected function hooks()
	{
		add_action('init', array($this, 'register_block'), 99);
		add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
		add_filter('block_editor_settings_all', array($this, 'inject_iframe_styles'));
	}

	/**
	 * Inject block UI and feed CSS into the WP 7.0+ iframed editor canvas.
	 *
	 * `block_editor_settings_all` exposes a `styles` array that WordPress renders
	 * inline inside the iframe `<head>`. wp_enqueue_style on the outer admin page
	 * does not propagate to the iframe for api_version 3 blocks, so we have to
	 * push the CSS contents through this filter for it to be visible inside the
	 * iframe (e.g. the license-expired notice rendered by get_feed_html()).
	 *
	 * @param array $settings Block editor settings.
	 * @return array
	 */
	public function inject_iframe_styles($settings)
	{
		// css/sb-blocks.css is effectively empty; the block UI rules (license
		// notice etc.) actually live in css/sb-instagram-admin.css, which is
		// only enqueued on the outer admin page and never reaches the iframe.
		// css/sbi-styles.min.css carries the feed grid styles for the preview.
		$files = array(
			trailingslashit( SBI_PLUGIN_DIR ) . 'css/sb-instagram-admin.css',
			trailingslashit( SBI_PLUGIN_DIR ) . 'css/sbi-styles.min.css',
		);

		if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
			$settings['styles'] = array();
		}

		foreach ( $files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}
			$css = file_get_contents( $file );
			if ( $css === false ) {
				continue;
			}
			$settings['styles'][] = array( 'css' => $css );
		}

		return $settings;
	}

	/**
	 * Register Instagram Feed Gutenberg block on the backend.
	 *
	 * @since 2.3/5.4
	 */
	public function register_block()
	{
		wp_register_style(
			'sbi-blocks-styles',
			trailingslashit(SBI_PLUGIN_URL) . 'css/sb-blocks.css',
			array('wp-edit-blocks'),
			SBIVER
		);

		$attributes = array(
			'shortcodeSettings' => array(
				'type' => 'string',
			),
			'noNewChanges' => array(
				'type' => 'boolean',
			),
			'executed' => array(
				'type' => 'boolean',
			),
		);

		register_block_type(
			'sbi/sbi-feed-block',
			array(
				'api_version'     => 3,
				'attributes'      => $attributes,
				'render_callback' => array($this, 'get_feed_html'),
				'supports'        => array( 'inserter' => false ),
			)
		);
	}

	/**
	 * Load Instagram Feed Gutenberg block scripts.
	 *
	 * @since 2.3/5.4
	 */
	public function enqueue_block_editor_assets()
	{
		sb_instagram_scripts_enqueue(true);

		wp_enqueue_style('sbi-blocks-styles');
		wp_enqueue_script(
			'sbi-feed-block',
			trailingslashit(SBI_PLUGIN_URL) . 'js/sb-blocks.js',
			array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor'),
			SBIVER,
			true
		);

		$shortcode_settings = '';

		$i18n = array(
			'addSettings' => esc_html__('Add Settings', 'instagram-feed'),
			'shortcodeSettings' => esc_html__('Shortcode Settings', 'instagram-feed'),
			'example' => esc_html__('Example', 'instagram-feed'),
			'preview' => esc_html__('Apply Changes', 'instagram-feed'),

		);

		if (!empty($_GET['sbi_wizard'])) {
			$shortcode_settings = 'feed="' . (int)$_GET['sbi_wizard'] . '"';
		}

		// Mirror the data that sb_instagram_scripts_enqueue() localizes onto
		// the sbi_scripts handle (inc/if-functions.php:2088-2103). The editor
		// JS injects sbi-scripts.min.js into the WP 7.0 iframe head and needs
		// these globals defined inside the iframe before that script runs.
		$sb_instagram_settings = sbi_get_database_settings();
		$br_adjust = !( isset( $sb_instagram_settings['sbi_br_adjust'] ) && ( $sb_instagram_settings['sbi_br_adjust'] == 'false' || $sb_instagram_settings['sbi_br_adjust'] == '0' || ! $sb_instagram_settings['sbi_br_adjust'] ) );
		$sb_instagram_js_options = array(
			'font_method' => 'svg',
			'resized_url' => sbi_get_resized_uploads_url(),
			'placeholder' => trailingslashit( SBI_PLUGIN_URL ) . 'img/placeholder.png',
			'br_adjust'   => $br_adjust,
		);
		if ( isset( $sb_instagram_settings['sb_instagram_disable_mob_swipe'] ) && $sb_instagram_settings['sb_instagram_disable_mob_swipe'] ) {
			$sb_instagram_js_options['no_mob_swipe'] = true;
		}

		$sbi_js_file = ( Util::isDebugging() || Util::is_script_debug() )
			? 'js/sbi-scripts.js'
			: 'js/sbi-scripts.min.js';

		wp_localize_script(
			'sbi-feed-block',
			'sbi_block_editor',
			array(
				'wpnonce' => wp_create_nonce('sb-instagram-blocks'),
				'configureLink' => get_admin_url() . '?page=sb-instagram-feed',
				'shortcodeSettings' => $shortcode_settings,
				'i18n' => $i18n,
				'iframeScriptUrl' => trailingslashit( SBI_PLUGIN_URL ) . $sbi_js_file,
				'jqueryUrl' => includes_url( 'js/jquery/jquery.min.js' ),
				'sbInstagramJsOptions' => $sb_instagram_js_options,
				'sbiTranslations' => array( 'share' => __( 'Share', 'instagram-feed' ) ),
			)
		);
	}

	/**
	 * Get form HTML to display in a Instagram Feed Gutenberg block.
	 *
	 * @param array $attr Attributes passed by Instagram Feed Gutenberg block.
	 *
	 * @return string
	 * @since 2.3/5.4
	 */
	public function get_feed_html($attr)
	{
		$feeds_count = SBI_Db::feeds_count();

		if ($feeds_count <= 0) {
			$sbi_license_status = get_option('sbi_license_status');
			$license_expired = sbi_builder_pro()->license_service->is_license_expired;
			$license_status = $license_expired ? 'expired' : ($sbi_license_status ?: 'inactive');
			return $this->plain_block_design($license_status);
		}

		$return = '';
		$return .= $this->get_license_expired_notice();

		$shortcode_settings = $attr['shortcodeSettings'] ?? '';
		$sbi_statuses = get_option('sbi_statuses', array());

		if (empty($sbi_statuses['support_legacy_shortcode']) && (empty($shortcode_settings) || strpos($shortcode_settings, 'feed=') === false)) {
			$feeds = SBI_Feed_Builder::get_feed_list();
			if (!empty($feeds[0]['id'])) {
				$shortcode_settings = 'feed="' . (int)$feeds[0]['id'] . '"';
			}
		}
		$shortcode_settings = str_replace(array('[instagram-feed', ']'), '', $shortcode_settings);

		$return .= do_shortcode('[instagram-feed ' . $shortcode_settings . ']');

		return $return;
	}

	/**
	 * Plain block design when theres no feeds.
	 *
	 * @param string $license_state License State.
	 * @since 6.2.0
	 */
	public function plain_block_design($license_state = 'expired')
	{
		if (!is_admin() && !defined('REST_REQUEST')) {
			return;
		}
		$other_plugins = $this->get_others_plugins();
		$output = '<div class="sbi-license-expired-plain-block-wrapper ' . $license_state . '">';

		if ($license_state == 'expired' || $license_state == 'inactive') {
			$output .= '<div class="sbi-lepb-header">
					<div class="sb-left">';
			$output .= SBI_Feed_Builder::builder_svg_icons('info');

			if ($license_state == 'expired') {
				$output .= sprintf('<p>%s</p>', __('Your license has expired! Renew it to reactivate Pro features.', 'instagram-feed'));
			} elseif ($license_state == 'inactive') {
				$output .= sprintf('<p>%s</p>', __('Your license key is inactive. Activate it to enable Pro features.', 'instagram-feed'));
			}

			$output .= '</div>
				<div class="sb-right">
					<a href="' . SBI_Admin_Notices::get_renew_url($license_state) . '">
						Resolve Now
						' . SBI_Feed_Builder::builder_svg_icons('chevronRight') . '
					</a>
				</div>
			</div>';
		}

		$output .= '<div class="sbi-lepb-body">
				' . SBI_Feed_Builder::builder_svg_icons('blockEditorSBILogo') . '
				<p class="sbi-block-body-title">Get started with your first feed from <br/> your Instagram profile</p>';

		$output .= sprintf(
			'<a href="%s" class="sbi-btn sbi-btn-blue">%s ' . SBI_Feed_Builder::builder_svg_icons('chevronRight') . '</a>',
			admin_url('admin.php?page=sbi-feed-builder'),
			__('Create an Instagram Feed', 'instagram-feed')
		);
		$output .= '</div>
			<div class="sbi-lepd-footer">
				<p class="sbi-lepd-footer-title">Did you know? </p>
				<p>You can add posts from ' . $other_plugins . ' using our free plugins</p>
			</div>
		</div>';

		return $output;
	}

	/**
	 * Get other Smash Balloon plugins list
	 *
	 * @since 6.2.0
	 */
	public function get_others_plugins()
	{
		$active_plugins = Util::get_sb_active_plugins_info();

		$other_plugins = array(
			'is_instagram_installed' => array(
				'title' => 'Instagram',
				'url' => 'https://smashballoon.com/instagram-feed/?utm_campaign=youtube-pro&utm_source=block-feed-embed&utm_medium=did-you-know',
			),
			'is_facebook_installed' => array(
				'title' => 'Facebook',
				'url' => 'https://smashballoon.com/custom-facebook-feed/?utm_campaign=youtube-pro&utm_source=block-feed-embed&utm_medium=did-you-know',
			),
			'is_twitter_installed' => array(
				'title' => 'Twitter',
				'url' => 'https://smashballoon.com/custom-twitter-feeds/?utm_campaign=youtube-pro&utm_source=block-feed-embed&utm_medium=did-you-know',
			),
			'is_youtube_installed' => array(
				'title' => 'YouTube',
				'url' => 'https://smashballoon.com/youtube-feed/?utm_campaign=youtube-pro&utm_source=block-feed-embed&utm_medium=did-you-know',
			),
		);

		if (!empty($active_plugins)) {
			foreach ($active_plugins as $name => $plugin) {
				if ($plugin) {
					unset($other_plugins[$name]);
				}
			}
		}

		$other_plugins_html = array();
		foreach ($other_plugins as $plugin) {
			$other_plugins_html[] = '<a href="' . $plugin['url'] . '">' . $plugin['title'] . '</a>';
		}

		return implode(", ", $other_plugins_html);
	}

	/**
	 * Get the notice message for an expired license.
	 *
	 * @return string The HTML content for the expired license notice.
	 */
	public function get_license_expired_notice()
	{
		// Check that the license exists and the user hasn't already clicked to ignore the message.
		if (empty(sbi_builder_pro()->license_service->get_license_key)) {
			return $this->get_license_expired_notice_content('inactive');
		}
		// If license not expired then return.
		if (!sbi_builder_pro()->license_service->is_license_expired) {
			return;
		}
		// Grace period ended?
		if (!sbi_builder_pro()->license_service->is_license_grace_period_ended(true)) {
			return;
		}

		return $this->get_license_expired_notice_content();
	}

	/**
	 * Output the license expired notice content on top of the embed block
	 *
	 * @param string $license_state License State.
	 * @since 6.2.0
	 */
	public function get_license_expired_notice_content($license_state = 'expired')
	{
		if (!is_admin() && !defined('REST_REQUEST')) {
			return;
		}

		$output = '<div class="sbi-block-license-expired-notice-ctn sbi-bln-license-state-' . $license_state . '">';
		$output .= '<div class="sbi-blen-header">';
		$output .= SBI_Feed_Builder::builder_svg_icons('eye2');
		$output .= '<span>' . __('Only Visible to WordPress Admins', 'instagram-feed') . '</span>';
		$output .= '</div>';
		$output .= '<div class="sbi-blen-resolve">';
		$output .= '<div class="sbi-left">';
		$output .= SBI_Feed_Builder::builder_svg_icons('info');
		if ($license_state == 'inactive') {
			$output .= '<span>' . __('Your license key is inactive. Activate it to enable Pro features.', 'instagram-feed') . '</span>';
		} else {
			$output .= '<span>' . __('Your license has expired! Renew it to reactivate Pro features.', 'instagram-feed') . '</span>';
		}
		$output .= '</div>';
		$output .= '<div class="sbi-right">';
		$output .= '<a href="' . SBI_Admin_Notices::get_renew_url($license_state) . '" target="_blank">' . __('Resolve Now', 'instagram-feed') . '</a>';
		$output .= SBI_Feed_Builder::builder_svg_icons('chevronRight');
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}
}
