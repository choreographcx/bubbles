<?php

use InstagramFeed\Builder\SBI_Db;
use InstagramFeed\Builder\SBI_Feed_Builder;

class LicenseNotification
{
	/**
	 * Database connection or handler.
	 *
	 * @var mixed
	 */
	protected $db;

	/**
	 * Class constructor.
	 * Initializes the database connection and registers necessary hooks.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->db = new SBI_Db();
		$this->register();
	}

	/**
	 * Registers actions for license notifications.
	 *
	 * @return void
	 */
	public function register()
	{
		add_action('wp_footer', [$this, 'sbi_frontend_license_error'], 300);
		add_action('wp_ajax_sbi_hide_frontend_license_error', [$this, 'hide_frontend_license_error']);
	}

	/**
	 * Hide the frontend license error message for a day
	 *
	 * @since 2.0.3
	 */
	public function hide_frontend_license_error()
	{
		check_ajax_referer('sbi_nonce', 'sbi_nonce');
		if (!current_user_can(sbi_builder_pro()->license_service->capability_check)) {
			return;
		}
		set_transient('sbi_license_error_notice', true, DAY_IN_SECONDS);

		wp_die();
	}

	/**
	 * Display frontend license error notification.
	 *
	 * Shows an error notification on the frontend if the license is inactive,
	 * expired, or in the grace period. Only visible to logged-in users with
	 * the appropriate capability.
	 *
	 * @return void
	 */
	public function sbi_frontend_license_error()
	{
		// Don't do anything for guests.
		if (!is_user_logged_in()) {
			return;
		}
		if (!current_user_can(sbi_builder_pro()->license_service->capability_check)) {
			return;
		}
		// Check that the license exists and the user hasn't already clicked to ignore the message.
		if (empty(sbi_builder_pro()->license_service->get_license_key)) {
			$this->sbi_frontend_license_error_content('inactive');
			return;
		}
		// If license not expired then return.
		if (!sbi_builder_pro()->license_service->is_license_expired) {
			return;
		}
		if (sbi_builder_pro()->license_service->is_license_grace_period_ended(true)) {
			$this->sbi_frontend_license_error_content();
		}
	}

	/**
	 * Output frontend license error HTML content
	 *
	 * @param string $license_state License state.
	 * @return void
	 * @since 6.2.0
	 */
	public function sbi_frontend_license_error_content($license_state = 'expired')
	{
		$feeds_count = $this->db->feeds_count();
		if ($feeds_count <= 0) {
			return;
		}
		$should_display_license_error_notice = get_transient('sbi_license_error_notice');
		if ($should_display_license_error_notice) {
			return;
		}
		?>
		<div id="sbi-fr-ce-license-error"
			 class="sbi-critical-error sbi-frontend-license-notice sbi-ce-license-<?php echo $license_state; ?>">
			<div class="sbi-fln-header">
					<span class="sb-left">
						<?php echo SBI_Feed_Builder::builder_svg_icons('eye2'); ?>
						<span class="sb-text">Only Visible to WordPress Admins</span>
					</span>
				<span id="sbi-frce-hide-license-error"
					  class="sb-close"><?php echo SBI_Feed_Builder::builder_svg_icons('times2SVG'); ?></span>
			</div>
			<div class="sbi-fln-body">
				<?php echo SBI_Feed_Builder::builder_svg_icons('instagram'); ?>
				<div class="sbi-fln-expired-text">
					<p>
						<?php
						printf(
						/* translators: status of the license key - is active or expired */
							esc_html__('Your Instagram Feed Pro license key %s', 'instagram-feed'),
							$license_state == 'expired' ? 'has ' . $license_state : 'is ' . $license_state
						);
						?>
						<a href="<?php echo $this->get_renew_url($license_state); ?>">Resolve
							Now <?php echo SBI_Feed_Builder::builder_svg_icons('chevronRight'); ?></a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * SBY Get Renew License URL
	 *
	 * @param string $license_state License State.
	 * @return string $url Renew License URL
	 * @since 2.0
	 */
	public function get_renew_url($license_state = 'expired')
	{
		global $sbi_download_id;
		if ($license_state == 'inactive') {
			return admin_url('admin.php?page=sbi-settings&focus=license');
		}
		$license_key = get_option('sbi_license_key') ? get_option('sbi_license_key') : null;

		return sprintf(
			'https://smashballoon.com/checkout/?license_key=%s&download_id=%s&utm_campaign=instagram-pro&utm_source=expired-notice&utm_medium=renew-license',
			esc_attr($license_key),
			$sbi_download_id
		);
	}
}
