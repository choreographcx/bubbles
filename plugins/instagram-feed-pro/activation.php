<?php

if (!function_exists('sbi_on_plugin_activation')) {
	function sbi_on_plugin_activation($plugin)
	{
		if (basename($plugin) !== 'instagram-feed.php') {
			return;
		}

		$plugin_to_deactivate = 'instagram-feed/instagram-feed.php';
		if (strpos($plugin, $plugin_to_deactivate) !== false) {
			$plugin_to_deactivate = 'instagram-feed-pro/instagram-feed.php';
		}

		foreach (sbi_get_active_plugins() as $basename) {
			if ($basename === $plugin_to_deactivate) {
				deactivate_plugins($basename);

				return;
			}
		}
	}

	function sbi_get_active_plugins()
	{
		if (is_multisite()) {
			$active_plugins = array_keys((array)get_site_option('active_sitewide_plugins', array()));
		} else {
			$active_plugins = (array)get_option('active_plugins', array());
		}

		return $active_plugins;
	}
}

add_action('activated_plugin', 'sbi_on_plugin_activation');
