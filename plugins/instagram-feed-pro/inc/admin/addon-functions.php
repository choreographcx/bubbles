<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use InstagramFeed\Integrations\WPCode;

/**
 * Deactivate addon.
 *
 * @since 1.0.0
 */
function sbi_deactivate_addon()
{
	// Run a security check.
	check_ajax_referer('sbi-admin', 'nonce');

	// Check for permissions.
	if (!current_user_can('activate_plugins')) {
		wp_send_json_error();
	}

	$type = 'addon';
	if (!empty($_POST['type'])) {
		$type = sanitize_key($_POST['type']);
	}

	if (isset($_POST['plugin'])) {
		deactivate_plugins(preg_replace('/[^a-z-_\/]/', '', wp_unslash(str_replace('.php', '', $_POST['plugin']))) . '.php');

		if ('plugin' === $type) {
			wp_send_json_success(esc_html__('Plugin deactivated.', 'instagram-feed'));
		} else {
			wp_send_json_success(esc_html__('Addon deactivated.', 'instagram-feed'));
		}
	}

	wp_send_json_error(esc_html__('Could not deactivate the addon. Please deactivate from the Plugins page.', 'instagram-feed'));
}

add_action('wp_ajax_sbi_deactivate_addon', 'sbi_deactivate_addon');

/**
 * Activate addon.
 *
 * @since 1.0.0
 */
function sbi_activate_addon()
{
	// Run a security check.
	check_ajax_referer('sbi-admin', 'nonce');

	// Check for permissions.
	if (!current_user_can('activate_plugins')) {
		wp_send_json_error();
	}

	if (isset($_POST['plugin'])) {
		$type = 'addon';
		if (!empty($_POST['type'])) {
			$type = sanitize_key($_POST['type']);
		}

		$activate = activate_plugins(preg_replace('/[^a-z-_\/]/', '', wp_unslash(str_replace('.php', '', $_POST['plugin']))) . '.php');

		if (!is_wp_error($activate)) {
			if ('plugin' === $type) {
				wp_send_json_success(esc_html__('Plugin activated.', 'instagram-feed'));
			} else {
				wp_send_json_success(esc_html__('Addon activated.', 'instagram-feed'));
			}
		}
	}

	wp_send_json_error(esc_html__('Could not activate addon. Please activate from the Plugins page.', 'instagram-feed'));
}

add_action('wp_ajax_sbi_activate_addon', 'sbi_activate_addon');

/**
 * Install addon.
 *
 * @since 1.0.0
 */
function sbi_install_addon()
{
	// Run a security check.
	check_ajax_referer('sbi-admin', 'nonce');

	// Check for permissions.
	if (!current_user_can('install_plugins')) {
		wp_send_json_error();
	}

	$error = esc_html__('Could not install addon. Please download from wpforms.com and install manually.', 'instagram-feed');

	if (empty($_POST['plugin'])) {
		wp_send_json_error($error);
	}

	// Only install plugins from the .org repo.
	if (strpos($_POST['plugin'], 'https://downloads.wordpress.org/plugin/') !== 0) {
		wp_send_json_error($error);
	}

	// Set the current screen to avoid undefined notices.
	set_current_screen('sbi-about-us');

	// Prepare variables.
	$url = esc_url_raw(
		add_query_arg(
			array(
				'page' => 'sbi-about-us',
			),
			admin_url('admin.php')
		)
	);

	$creds = request_filesystem_credentials($url, '', false, false);

	// Check for file system permissions.
	if (false === $creds) {
		wp_send_json_error($error);
	}

	if (!WP_Filesystem($creds)) {
		wp_send_json_error($error);
	}

	/*
	 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
	 */

	require_once SBI_PLUGIN_DIR . 'inc/admin/class-install-skin.php';

	// Do not allow WordPress to search/download translations, as this will break JS output.
	remove_action('upgrader_process_complete', array('Language_Pack_Upgrader', 'async_upgrade'), 20);

	// Create the plugin upgrader with our custom skin.
	$installer = new Sbi\Helpers\PluginSilentUpgrader(new Sbi_Install_Skin());

	// Error check.
	if (!method_exists($installer, 'install') || empty($_POST['plugin'])) {
		wp_send_json_error($error);
	}

	$installer->install(esc_url_raw(wp_unslash($_POST['plugin'])));

	// Flush the cache and return the newly installed plugin basename.
	wp_cache_flush();

	$plugin_basename = $installer->plugin_info();

	if ($plugin_basename) {
		$type = 'addon';
		if (!empty($_POST['type'])) {
			$type = sanitize_key($_POST['type']);
		}

		$referrer = '';
		if (!empty($_POST['referrer'])) {
			$referrer = sanitize_key($_POST['referrer']);
		}

		// Activate the plugin silently.
		$activated = activate_plugin($plugin_basename);

		if (!is_wp_error($activated)) {
			if ($plugin_basename === 'custom-facebook-feed/custom-facebook-feed.php' && $referrer === 'oembeds') {
				delete_option('cff_plugin_do_activation_redirect');
			}
			wp_send_json_success(
				array(
					'msg' => 'plugin' === $type ? esc_html__('Plugin installed & activated.', 'instagram-feed') : esc_html__('Addon installed & activated.', 'instagram-feed'),
					'is_activated' => true,
					'basename' => $plugin_basename,
				)
			);
		} else {
			wp_send_json_success(
				array(
					'msg' => 'plugin' === $type ? esc_html__('Plugin installed.', 'instagram-feed') : esc_html__('Addon installed.', 'instagram-feed'),
					'is_activated' => false,
					'basename' => $plugin_basename,
				)
			);
		}
	}

	wp_send_json_error($error);
}

add_action('wp_ajax_sbi_install_addon', 'sbi_install_addon');

/**
 * Smash Balloon Encrypt or decrypt
 *
 * @param string $action A decrypt or encrypt action.
 * @param string $string A string to encrypt or decrypt.
 *
 * @return string $output
 */
function sbi_encrypt_decrypt($action, $string)
{
	$output = false;

	$encrypt_method = "AES-256-CBC";
	$secret_key = 'SMA$H.BA[[OON#23121';
	$secret_iv = '1231394873342102221';

	// hash.
	$key = hash('sha256', $secret_key);

	// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning.
	$iv = substr(hash('sha256', $secret_iv), 0, 16);

	if ($action === 'encrypt') {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
	} elseif ($action === 'decrypt') {
		$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	}

	return $output;
}

/**
 * Ajax function to migrate Custom CSS and JS to WPCode Snippets
 *
 * @since 6.4.1
 */
function sbi_migrate_snippets()
{
	// Run a security check.
	check_ajax_referer('sbi-admin', 'nonce');

	if (!sbi_current_user_can('manage_instagram_feed_options')) {
		wp_send_json_error();
	}

	// Check if the WP Code plugin is installed.
	if (!function_exists('wpcode')) {
		wp_send_json_error(esc_html__('WPCode plugin is not installed.', 'instagram-feed'));
	}

	$settings = sbi_get_database_settings();
	isset($settings['sb_instagram_custom_css']) ? $sb_instagram_custom_css = trim($settings['sb_instagram_custom_css']) : $sb_instagram_custom_css = '';
	isset($settings['sb_instagram_custom_js']) ? $sb_instagram_custom_js = trim($settings['sb_instagram_custom_js']) : $sb_instagram_custom_js = '';

	// Check if the Custom CSS and JS are empty.
	if (empty($sb_instagram_custom_css) && empty($sb_instagram_custom_js)) {
		wp_send_json_error(esc_html__('No Custom CSS or JS to migrate.', 'instagram-feed'));
	}

	$snippets = array();

	// Migration of Custom CSS.
	if (!empty($sb_instagram_custom_css)) {
		$snippets[] = array(
			'code_type' => 'css',
			'code' => wp_strip_all_tags(stripslashes($sb_instagram_custom_css)),
			'location' => 'site_wide_header',
			'auto_insert' => 1,
			'title' => 'Instagram Feed Custom CSS',
			'note' => 'Custom CSS from Instagram Feed settings',
			'tags' => array('instagram-feed', 'custom-css'),
			'active' => true,
		);
	}

	// Migration of Custom JS.
	if (!empty($sb_instagram_custom_js)) {
		$sb_instagram_custom_js = "var sbi_custom_js = function() {\n" . stripslashes($sb_instagram_custom_js) . "\n};";
		$snippets[] = array(
			'code_type' => 'js',
			'code' => $sb_instagram_custom_js,
			'location' => 'site_wide_footer',
			'auto_insert' => 1,
			'title' => 'Instagram Feed Custom JS',
			'note' => 'Custom JS from Instagram Feed settings',
			'tags' => array('instagram-feed', 'custom-js'),
			'active' => true,
		);
	}

	$success = WPCode::create_snippets($snippets);

	if ($success) {
		// Clear the Custom CSS and JS.
		$settings = get_option('sb_instagram_settings', []);
		$settings['sb_instagram_custom_css'] = '';
		$settings['sb_instagram_custom_js'] = '';

		update_option('sb_instagram_settings', $settings);

		wp_send_json_success($snippets);
	} else {
		wp_send_json_error(__('Failed to migrate snippets.', 'instagram-feed'));
	}
}

add_action('wp_ajax_sbi_migrate_snippets', 'sbi_migrate_snippets');
