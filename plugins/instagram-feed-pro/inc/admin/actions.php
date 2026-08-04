<?php

use InstagramFeed\Builder\SBI_Feed_Builder;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use InstagramFeed\Helpers\Util;

/**
 * Registers the Instagram Feed Pro menu in the WordPress admin dashboard.
 *
 * @return void
 */
function sb_instagram_menu()
{
	$cap = current_user_can('manage_instagram_feed_options') ? 'manage_instagram_feed_options' : 'manage_options';

	$cap = apply_filters('sbi_settings_pages_capability', $cap);

	$notice_bubble = sb_menu_notice_bubble();

	add_menu_page(
		__('Instagram Feed', 'instagram-feed'),
		__('Instagram Feed ', 'instagram-feed') . $notice_bubble,
		$cap,
		'sb-instagram-feed',
		'sb_instagram_settings_page'
	);

	if (sbi_builder_pro()->license_service->should_disable_pro_features) {
		add_submenu_page(
			'sb-instagram-feed',
			__('Upgrade to Pro', 'instagram-feed'),
			'<span class="sbi_get_pro">' . __('Try the Pro Demo', 'instagram-feed') . '</span>',
			$cap,
			'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=menu-link&utm_medium=upgrade-link'
		);
	}

	if (sbi_should_add_free_plugin_submenu('facebook')) {
		add_submenu_page(
			'sb-instagram-feed',
			__('Facebook Feed', 'instagram-feed'),
			'<span class="sbi_get_cff">' . __('Facebook Feed', 'instagram-feed') . '</span>',
			$cap,
			'admin.php?page=cff-builder'
		);
	}

	if (sbi_should_add_free_plugin_submenu('twitter')) {
		add_submenu_page(
			'sb-instagram-feed',
			__('Twitter Feed', 'instagram-feed'),
			'<span class="sbi_get_ctf">' . __('Twitter Feed', 'instagram-feed') . '</span>',
			$cap,
			'admin.php?page=sb-instagram-feed&tab=more'
		);
	}

	if (sbi_should_add_free_plugin_submenu('youtube')) {
		add_submenu_page(
			'sb-instagram-feed',
			__('YouTube Feed', 'instagram-feed'),
			'<span class="sbi_get_yt">' . __('YouTube Feed', 'instagram-feed') . '</span>',
			$cap,
			'admin.php?page=sb-instagram-feed&tab=more'
		);
	}
}

add_action('admin_menu', 'sb_instagram_menu');

/**
 * Displays a notification bubble in the WordPress admin menu.
 *
 * @return string|null
 */
function sb_menu_notice_bubble()
{
	$has_admin_errors = Util::sbi_has_admin_errors();
	if ($has_admin_errors) {
		return '';
	}

	global $sbi_notices;
	$sbi_statuses = get_option('sbi_statuses', array());

	$template_notice = $sbi_notices->get_notice('custom_feed_templates');
	$api_notice = $sbi_notices->get_notice('personal_api_deprecation');

	$displayNotice = (isset($sbi_statuses['custom_templates_notice']) && !empty($template_notice)) || !empty($api_notice);

	if ($displayNotice) {
		return '<span class="sbi-notice-alert"><span>1</span></span>';
	}

	return '';
}

/**
 * Enqueues the admin styles for the Instagram Feed Pro plugin.
 *
 * @return void
 */
function sb_instagram_admin_style()
{
	wp_register_style('sb_instagram_admin_css', SBI_PLUGIN_URL . 'css/sb-instagram-admin.css', array(), SBIVER);
	wp_enqueue_style('sb_instagram_admin_css');
	wp_enqueue_style('wp-color-picker');
}

add_action('admin_enqueue_scripts', 'sb_instagram_admin_style');

/**
 * Enqueues the necessary admin scripts for the Instagram Feed Pro plugin.
 *
 * @return void
 */
function sb_instagram_admin_scripts()
{
	wp_enqueue_script('sb_instagram_admin_js', SBI_PLUGIN_URL . 'js/sb-instagram-admin.js', array(), SBIVER, true);
	wp_localize_script(
		'sb_instagram_admin_js',
		'sbiA',
		array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'sbi_nonce' => wp_create_nonce('sbi_nonce'),
		)
	);
	$strings = array(
		'addon_activate' => esc_html__('Activate', 'instagram-feed'),
		'addon_activated' => esc_html__('Activated', 'instagram-feed'),
		'addon_active' => esc_html__('Active', 'instagram-feed'),
		'addon_deactivate' => esc_html__('Deactivate', 'instagram-feed'),
		'addon_inactive' => esc_html__('Inactive', 'instagram-feed'),
		'addon_install' => esc_html__('Install Addon', 'instagram-feed'),
		'addon_error' => esc_html__('Could not install addon. Please download from wpforms.com and install manually.', 'instagram-feed'),
		'plugin_error' => esc_html__('Could not install a plugin. Please download from WordPress.org and install manually.', 'instagram-feed'),
		'addon_search' => esc_html__('Searching Addons', 'instagram-feed'),
		'ajax_url' => admin_url('admin-ajax.php'),
		'cancel' => esc_html__('Cancel', 'instagram-feed'),
		'close' => esc_html__('Close', 'instagram-feed'),
		'nonce' => wp_create_nonce('sbi-admin'),
		'almost_done' => esc_html__('Almost Done', 'instagram-feed'),
		'oops' => esc_html__('Oops!', 'instagram-feed'),
		'ok' => esc_html__('OK', 'instagram-feed'),
		'plugin_install_activate_btn' => esc_html__('Install and Activate', 'instagram-feed'),
		'plugin_install_activate_confirm' => esc_html__('needs to be installed and activated to import its forms. Would you like us to install and activate it for you?', 'instagram-feed'),
		'plugin_activate_btn' => esc_html__('Activate', 'instagram-feed'),
		'oembed_connectionURL' => sbi_get_oembed_connection_url(),
		'smashPlugins'	=> SBI_Feed_Builder::get_smashballoon_plugins_info()
	);
	$strings = apply_filters('sbi_admin_strings', $strings);
	wp_localize_script(
		'sb_instagram_admin_js',
		'sbi_admin',
		$strings
	);
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('wp-color-picker');
}

add_action('admin_enqueue_scripts', 'sb_instagram_admin_scripts');

/**
 * Retrieves the oEmbed connection URL for Instagram Feed Pro.
 *
 * @return array The oEmbed connection URL.
 */
function sbi_get_oembed_connection_url()
{
	$admin_url_state = admin_url('admin.php?page=sbi-oembeds-manager');
	$nonce = wp_create_nonce('sbi_con');
	// If the admin_url isn't returned correctly then use a fallback.
	if ($admin_url_state == '/wp-admin/admin.php?page=sbi-oembeds-manager') {
		$admin_url_state = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}

	return array(
		'connect' => SBI_OEMBED_CONNECT_URL,
		'sbi_con' => $nonce,
		'stateURL' => $admin_url_state
	);
}

// Add a Settings link to the plugin on the Plugins page.
$sbi_plugin_file = 'instagram-feed-pro/instagram-feed.php';
add_filter("plugin_action_links_$sbi_plugin_file", 'sbi_add_settings_link', 10, 2);

/**
 * Adds a settings link to the plugin's action links.
 *
 * @param array  $links An array of the plugin's action links.
 * @param string $file The plugin file path.
 * @return array Modified array of action links with the settings link added.
 */
function sbi_add_settings_link($links, $file)
{
	$sbi_settings_link = '<a href="' . admin_url('admin.php?page=sbi-feed-builder') . '">' . __('Settings', 'instagram-feed') . '</a>';
	array_unshift($links, $sbi_settings_link);

	return $links;
}

/**
 * Formats the error response for the Instagram Feed Pro plugin.
 *
 * @param array $response The response array containing error details.
 * @return string The formatted error message.
 */
function sbi_formatted_error($response)
{
	if (isset($response['error'])) {
		/* translators: API Error code and message */
		$error = '<p>' . sprintf(__('API error %s:', 'instagram-feed'), esc_html($response['error']['code'])) . ' ' . esc_html($response['error']['message']) . '</p>';
		$error .= '<div class="license-action-btns"><p class="sbi-error-directions"><a href="https://smashballoon.com/instagram-feed/docs/errors/?utm_campaign=instagram-pro&utm_source=admin-notice&utm_medium=docs" target="_blank" rel="noopener">' . __('Directions on how to resolve this issue', 'instagram-feed') . '</a></p></div>';

		return $error;
	} else {
		/* translators: API url */
		$message = '<p>' . sprintf(__('Error connecting to %s.', 'instagram-feed'), $response['url']) . '</p>';
		if (isset($response['response']->errors)) {
			foreach ($response['response']->errors as $key => $item) {
				$message .= '<p>' . esc_html($key) . ' - ' . esc_html($item[0]) . '</p>';
			}
		}
		$message .= '<div class="license-action-btns"><p class="sbi-error-directions"><a href="https://smashballoon.com/instagram-feed/docs/errors/?utm_campaign=instagram-pro&utm_source=admin-notice&utm_medium=docs" target="_blank" rel="noopener">' . __('Directions on how to resolve this issue', 'instagram-feed') . '</a></p></div>';

		return $message;
	}
}

/**
 * Connects a new Instagram account using the provided access token and account ID.
 *
 * @param string $access_token The access token for the Instagram account.
 * @param string $account_id The ID of the Instagram account.
 * @return array
 */
function sbi_connect_new_account($access_token, $account_id)
{
	$split_id = explode(' ', trim($account_id));
	$account_id = preg_replace('/[^A-Za-z0-9 ]/', '', $split_id[0]);
	if (!empty($account_id)) {
		$split_token = explode(' ', trim($access_token));
		$access_token = preg_replace('/[^A-Za-z0-9 ]/', '', $split_token[0]);
	}

	$account = array(
		'access_token' => $access_token,
		'user_id' => $account_id,
		'type' => 'business',
	);

	if (sbi_code_check($access_token)) {
		$account['type'] = 'basic';
	}

	$connector = new SBI_Account_Connector();

	$response = $connector->fetch($account);

	if (isset($response['access_token'])) {
		$connector->add_account_data($response);
		$connector->update_stored_account();
		$connector->after_update();
		return $connector->get_account_data();
	} else {
		return $response;
	}
}

add_action('admin_init', 'sbi_admin_error_notices');

/**
 * Display admin error notices for the Instagram Feed Pro plugin.
 *
 * @return void
 */
function sbi_admin_error_notices()
{
	global $sb_instagram_posts_manager;

	if (isset($_GET['page']) && in_array($_GET['page'], array('sbi-settings'), true)) {
		$errors = $sb_instagram_posts_manager->get_errors();

		if (!empty($errors)) {
			if (!empty($errors['database_create']) || !empty($errors['upload_dir'])) {
				$type = !empty($errors['database_create']) ? 'database_create' : 'upload_dir';
				$title = !empty($errors['database_create']) ? __('Instagram Feed was unable to create new database tables.', 'instagram-feed') : '';
				$message = !empty($errors['database_create']) ? $errors['database_create'] : $errors['upload_dir'];

				$buttons = array(
					array(
						'text' => __('Visit our FAQ page for help', 'instagram-feed'),
						'url' => 'https://smashballoon.com/docs/instagram/?utm_campaign=instagram-pro&utm_source=admin-notice&utm_medium=docs',
						'class' => 'sbi-license-btn sbi-btn-blue sbi-notice-btn',
						'target' => '_blank',
						'tag'    => 'a',
					)
				);

				if (!empty($errors['database_create'])) {
					$buttons[] = array(
						'text'  => __('Try creating database tables again', 'instagram-feed'),
						'class' => 'sbi-retry-db sbi-space-left sbi-btn sbi-notice-btn sbi-btn-grey',
						'tag'   => 'button',
					);
				}

				addErrorNotice(
					$type,
					$title,
					$message,
					$buttons
				);
			}

			if (!empty($errors['unused_feed'])) {
				addErrorNotice(
					'unused_feed',
					__('Action Required Within 7 Days:', 'instagram-feed'),
					$errors['unused_feed'] . '<br>' . __('Or you can simply press the "Fix Usage" button to fix this issue.', 'instagram-feed'),
					array(
						array(
							'text'  => __('Fix Usage', 'instagram-feed'),
							'class' => 'sbi-reset-unused-feed-usage sbi-space-left sbi-btn sbi-notice-btn sbi-btn-blue',
							'tag'   => 'button',
						),
					)
				);
			}

			if (!empty($errors['platform_data_deleted'])) {
				addErrorNotice(
					'platform_data_deleted',
					__('All Instagram Data has Been Removed:', 'instagram-feed'),
					$errors['platform_data_deleted'] . '<br>' . __('To fix your feeds, reconnect all accounts that were in use on the Settings page.', 'instagram-feed')
				);
			}

			if (!empty($errors['database_error'])) {
				addErrorNotice(
					'database_error',
					__('Action Required: Unable to save or update feed sources', 'instagram-feed'),
					$errors['database_error'] . '<br>' . __('Please ensure that all database tables are created and the user has the following permissions: SELECT, INSERT, UPDATE, DELETE, ALTER (for updates), CREATE TABLE, DROP TABLE, and INDEX.', 'instagram-feed'),
					array(
						array(
							'text'   => __('Visit our FAQ page for help', 'instagram-feed'),
							'url'    => 'https://smashballoon.com/doc/instagram-api-error-message-reference/?utm_campaign=instagram-pro&utm_source=admin-notice&utm_medium=docs',
							'class'  => 'sbi-license-btn sbi-btn-blue sbi-notice-btn',
							'target' => '_blank',
							'tag'    => 'a',
						),
						array(
							'text'  => __('Try creating database tables again', 'instagram-feed'),
							'class' => 'sbi-retry-db sbi-space-left sbi-btn sbi-notice-btn sbi-btn-grey',
							'tag'   => 'button',
						)
					)
				);
			}
		}

		$critical_errors = $sb_instagram_posts_manager->get_critical_errors();
		if ($sb_instagram_posts_manager->are_critical_errors()) {
			addErrorNotice(
				'critical_error',
				__('Instagram Feed is encountering an error and your feeds may not be updating due to the following reasons:', 'instagram-feed'),
				$critical_errors
			);
		}
	}
}

/**
 * Adds an error notice to the admin panel.
 *
 * @param string $id      The unique identifier for the error notice.
 * @param string $title   The title of the error notice.
 * @param string $message The message content of the error notice.
 * @param array  $buttons Optional. An array of buttons to display with the error notice. Default is an empty array.
 */
function addErrorNotice($id, $title, $message, $buttons = array())
{
	global $sbi_notices;

	$error_args = array(
		'class'              => 'sbi-admin-notices sbi-critical-error-notice',
		'title'              => array(
			'text'  => $title,
			'class' => 'sb-notice-title',
			'tag'   => 'h3',
		),
		'message'            => '<p>' . $message . '</p><br>',
		'dismissible'        => false,
		'priority'           => 1,
		'page'               => 'sbi-settings',
		'buttons'            => $buttons,
		'buttons_wrap_start' => '<p class="sbi-error-directions">',
		'buttons_wrap_end'   => '</p>',
		'icon'               => array(
			'src'  => SBI_PLUGIN_URL . 'admin/assets/img/sbi-error.svg',
			'wrap' => '<span class="sb-notice-icon sb-error-icon"><img {src}></span>',
		),
		'wrap_schema'        => '<div {id} {class}>{icon}<div class="sbi-notice-body">{title}{message}{buttons}</div></div>',
	);

	$sbi_notices->add_notice($id, 'error', $error_args);
}

function sbi_reset_log()
{
	check_ajax_referer('sbi_nonce', 'sbi_nonce');

	if (!sbi_current_user_can('manage_instagram_feed_options')) {
		wp_send_json_error();
	}

	global $sb_instagram_posts_manager;

	$sb_instagram_posts_manager->remove_all_errors();

	global $sbi_notices;
	$sbi_notices->remove_notice('critical_error');

	wp_send_json_success('1');
}

add_action('wp_ajax_sbi_reset_log', 'sbi_reset_log');

/**
 * Displays the settings page for the Instagram Feed Pro plugin.
 *
 * @return void
 */
function sb_instagram_settings_page()
{
	$link = admin_url('admin.php?page=sbi-settings');
	?>
	<div id="sbi_admin">
		<div class="sbi_notice">
			<strong><?php esc_html_e('The Instagram Feed Settings page has moved!', 'instagram-feed'); ?></strong>
			<a href="<?php echo esc_url($link); ?>"><?php esc_html_e('Click here to go to the new page.', 'instagram-feed'); ?></a>
		</div>
	</div>
	<?php
}

/**
 * Generates the HTML for the "Connect Account" button.
 *
 * @param string $page The URL of the page where the button will redirect. Default is 'admin.php?page=sb-instagram-feed'.
 *
 * @return void
 */
function sbi_get_connect_account_button($page = 'admin.php?page=sb-instagram-feed')
{
	$state_url = wp_nonce_url(admin_url('admin.php?page=sbi-settings'), 'sbi-connect', 'sbi_con');
	$connect_url = 'https://connect.smashballoon.com/auth/ig/?state=' . $state_url;
	?>
	<a data-new-api="<?php echo esc_attr($connect_url); ?>" href="<?php echo esc_attr($connect_url); ?>"
	   class="sbi_admin_btn"><i class="fa fa-user-plus" aria-hidden="true"
								style="font-size: 20px;"></i>&nbsp; <?php esc_html_e('Connect an Instagram Account', 'instagram-feed'); ?>
	</a>
	<?php
}
