<?php

namespace InstagramFeed\Admin;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use InstagramFeed\Builder\SBI_Feed_Builder;
use InstagramFeed\Helpers\Util;
use InstagramFeed\SBI_View;

/**
 * The About Page
 *
 * @since 6.0
 */
class SBI_About_Us
{
	/**
	 * Admin menu page slug.
	 *
	 * @since 6.0
	 *
	 * @var string
	 */
	private const SLUG = 'sbi-about-us';

	/**
	 * Initializing the class
	 *
	 * @since 6.0
	 */
	public function __construct()
	{
		$this->init();
	}

	/**
	 * Determining if the user is viewing our page, if so, party on.
	 *
	 * @since 6.0
	 */
	public function init()
	{
		if (!is_admin()) {
			return;
		}

		add_action('admin_menu', array($this, 'register_menu'));
	}

	/**
	 * Register Menu.
	 *
	 * @since 6.0
	 */
	public function register_menu()
	{
		$cap = current_user_can('manage_instagram_feed_options') ? 'manage_instagram_feed_options' : 'manage_options';
		$cap = apply_filters('sbi_settings_pages_capability', $cap);

		$about_us = add_submenu_page(
			'sb-instagram-feed',
			__('About Us', 'instagram-feed'),
			__('About Us', 'instagram-feed'),
			$cap,
			self::SLUG,
			array($this, 'about_us'),
			4
		);
		add_action('load-' . $about_us, array($this, 'about_us_enqueue_assets'));
	}

	/**
	 * Enqueue About Us Page CSS & Script.
	 *
	 * Loads only for About Us page
	 *
	 * @since 6.0
	 */
	public function about_us_enqueue_assets()
	{
		if (!Util::isIFPage()) {
			return;
		}

		wp_enqueue_style(
			'about-style',
			SBI_PLUGIN_URL . 'admin/assets/css/about.css',
			false,
			SBIVER
		);

		wp_enqueue_script(
			'sb-vue',
			SBI_PLUGIN_URL . 'js/vue.min.js',
			null,
			'2.6.12',
			true
		);

		wp_register_script('feed-builder-svgs', SBI_PLUGIN_URL . 'assets/svgs/svgs.js');

		wp_enqueue_script(
			'about-app',
			SBI_PLUGIN_URL . 'admin/assets/js/about.js',
			array('feed-builder-svgs'),
			SBIVER,
			true
		);

		$sbi_about = $this->page_data();

		wp_localize_script(
			'about-app',
			'sbi_about',
			$sbi_about
		);
	}

	/**
	 * Page Data to use in front end
	 *
	 * @return array
	 * @since 6.0
	 */
	public function page_data()
	{
		// Get the WordPress's core list of installed plugins.
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();
		$active_sb_plugins = Util::get_sb_active_plugins_info();
		$license_key = get_option('sbi_license_key') ?: null;
		$images_url = SBI_PLUGIN_URL . 'admin/assets/img/about/';

		return array(
			'admin_url' => admin_url(),
			'supportPageUrl' => admin_url('admin.php?page=sbi-support'),
			'ajax_handler' => admin_url('admin-ajax.php'),
			'licenseKey' => $license_key,
			'links' => SBI_Feed_Builder::get_links_with_utm(),
			'nonce' => wp_create_nonce('sbi-admin'),
			'socialWallLinks' => SBI_Feed_Builder::get_social_wall_links(),
			'socialWallActivated' => is_plugin_active('social-wall/social-wall.php'),
			'sbiLicenseNoticeActive' => sbi_license_notices_active(),
			'sbiLicenseInactiveState' => sbi_license_inactive_state(),
			'genericText' => $this->getGenericText(),
			'aboutBox' => array(
				'atSmashBalloon' => __('At Smash Balloon, we build software that helps you create beautiful responsive social media feeds for your website in minutes.', 'instagram-feed'),
				'weAreOn' => __('We\'re on a mission to make it super simple to add social media feeds in WordPress. No more complicated setup steps, ugly iframe widgets, or negative page speed scores.', 'instagram-feed'),
				'ourPlugins' => __('Our plugins aren\'t just easy to use, but completely customizable, reliable, and fast! Which is why over 1.6 million awesome users, just like you, choose to use them on their site.', 'instagram-feed'),
				'teamAvatar' => SBI_PLUGIN_URL . 'admin/assets/img/team-avatar.png',
				'teamImgAlt' => __('Smash Balloon Team', 'instagram-feed'),
			),
			'pluginsInfo' => $this->getPluginsInfo($active_sb_plugins),
			'proPluginsInfo' => $this->getProPluginsInfo($active_sb_plugins, $license_key),
			'recommendedPlugins' => $this->getRecommendedPluginsInfo($installed_plugins, $images_url),
			'buttons' => array(
				'add' => __('Add', 'instagram-feed'),
				'viewDemo' => __('View Demo', 'instagram-feed'),
				'install' => __('Install', 'instagram-feed'),
				'installed' => __('Installed', 'instagram-feed'),
				'activate' => __('Activate', 'instagram-feed'),
				'deactivate' => __('Deactivate', 'instagram-feed'),
				'open' => __('Open', 'instagram-feed'),
			),
			'icons' => array(
				'plusIcon' => '<svg width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.0832 6.83317H7.08317V11.8332H5.4165V6.83317H0.416504V5.1665H5.4165V0.166504H7.08317V5.1665H12.0832V6.83317Z" fill="white"/></svg>',
				'loaderSVG' => '<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve"><path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"/></path></svg>',
				'checkmarkSVG' => '<svg width="13" height="10" viewBox="0 0 13 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.13112 6.88917L11.4951 0.525204L12.9093 1.93942L5.13112 9.71759L0.888482 5.47495L2.3027 4.06074L5.13112 6.88917Z" fill="#8C8F9A"/></svg>',
				'link' => '<svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.333374 9.22668L7.39338 2.16668H3.00004V0.833344H9.66671V7.50001H8.33338V3.10668L1.27337 10.1667L0.333374 9.22668Z" fill="#141B38"/></svg>',
				'installIcon' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.213 2.84015C11.2019 2.01003 9.96799 1.49743 8.66634 1.36682V2.71349C9.63967 2.83349 10.5263 3.22015 11.2663 3.79349L12.213 2.84015ZM13.2863 7.33349H14.633C14.4997 5.99349 13.9663 4.77349 13.1597 3.78682L12.2063 4.73349C12.7944 5.48679 13.1676 6.38523 13.2863 7.33349ZM12.2063 11.2668L13.1597 12.2202C13.9887 11.2084 14.5012 9.97482 14.633 8.67349H13.2863C13.1663 9.61938 12.7932 10.5153 12.2063 11.2668ZM8.66634 13.2868V14.6335C10.0063 14.5002 11.2263 13.9668 12.213 13.1602L11.2597 12.2068C10.5263 12.7802 9.63967 13.1668 8.66634 13.2868ZM10.393 7.06015L8.66634 8.78015V4.66682H7.33301V8.78015L5.60634 7.05349L4.66634 8.00015L7.99967 11.3335L11.333 8.00015L10.393 7.06015ZM7.33301 13.2868V14.6335C3.96634 14.3002 1.33301 11.4602 1.33301 8.00015C1.33301 4.54015 3.96634 1.70015 7.33301 1.36682V2.71349C4.69967 3.04015 2.66634 5.28015 2.66634 8.00015C2.66634 10.7202 4.69967 12.9602 7.33301 13.2868Z" fill="#141B38"/></svg>',
			),
		);
	}

	/**
	 * Retrieves the generic text for the About Us section.
	 *
	 * This function is used to get the generic text content that is displayed
	 * in the About Us section of the Instagram Feed Pro plugin's admin area.
	 *
	 * @return array The generic text content.
	 */
	private function getGenericText()
	{
		return [
			'recheckLicense' => __('Recheck License', 'instagram-feed'),
			'licenseValid' => __('License Valid', 'instagram-feed'),
			'licenseExpired' => __('License Expired', 'instagram-feed'),
			'help' => __('Help', 'instagram-feed'),
			'title' => __('About Us', 'instagram-feed'),
			'title2' => __('Our Other Social Media Feed Plugins', 'instagram-feed'),
			'title3' => __('Plugins we recommend', 'instagram-feed'),
			/* translators: %s is a line break HTML tag */
			'description2' => sprintf(__('We’re more than just an Instagram plugin! %s Check out our other plugins and add more content to your site.', 'instagram-feed'), '<br>'),
			'notification' => [
				'licenseActivated' => [
					'type' => 'success',
					'text' => __('License Successfully Activated', 'instagram-feed'),
				],
				'licenseError' => [
					'type' => 'error',
					'text' => __('Couldn\'t Activate License', 'instagram-feed'),
				],
			],
		];
	}

	/**
	 * Retrieves information about the smash free plugins.
	 *
	 * @param array $active_sb_plugins An array of active Smash Balloon plugins.
	 * @return array An array containing information about the active pro plugins.
	 */
	private function getPluginsInfo($active_sb_plugins)
	{
		$plugins = array(
			'instagram' => array(
				'plugin' => $active_sb_plugins['instagram_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/instagram-feed.zip',
				'title' => __('Instagram Feed', 'instagram-feed'),
				'description' => __('An elegant way to add your Instagram posts to your website. ', 'instagram-feed'),
				'icon' => 'insta-icon.svg',
				'installed' => $active_sb_plugins['is_instagram_installed'],
			),
			'facebook' => array(
				'plugin' => $active_sb_plugins['facebook_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
				'title' => __('Custom Facebook Feed', 'instagram-feed'),
				'description' => __('Add Facebook posts from your timeline, albums and much more.', 'instagram-feed'),
				'icon' => 'fb-icon.svg',
				'installed' => $active_sb_plugins['is_facebook_installed'],
			),
			'twitter' => array(
				'plugin' => $active_sb_plugins['twitter_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/custom-twitter-feeds.zip',
				'title' => __('Custom Twitter Feeds', 'instagram-feed'),
				'description' => __('A customizable way to display tweets from your Twitter account. ', 'instagram-feed'),
				'icon' => 'twitter-icon.svg',
				'installed' => $active_sb_plugins['is_twitter_installed'],
			),
			'youtube' => array(
				'plugin' => $active_sb_plugins['youtube_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
				'title' => __('Feeds for YouTube', 'instagram-feed'),
				'description' => __('A simple yet powerful way to display videos from YouTube. ', 'instagram-feed'),
				'icon' => 'youtube-icon.svg',
				'installed' => $active_sb_plugins['is_youtube_installed'],
			),
			'tiktok' => array(
				'plugin' => $active_sb_plugins['tiktok_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/feeds-for-tiktok.zip',
				'title' => __('TikTok Feeds', 'instagram-feed'),
				'description' => __('Display customizable TikTok feeds in WordPress', 'instagram-feed'),
				'icon' => 'tiktok-icon.svg',
				'installed' => $active_sb_plugins['is_tiktok_installed'],
			),
			'reviews' => array(
				'plugin' => $active_sb_plugins['reviews_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/reviews-feed.zip',
				'title' => __('Reviews Feed', 'instagram-feed'),
				'description' => __('Display reviews from Google, Facebook, Yelp, and more', 'instagram-feed'),
				'icon' => 'reviews-icon.svg',
				'installed' => $active_sb_plugins['is_reviews_installed'],
			),
		);

		return array_map([$this, 'getPluginInfo'], $plugins);
	}

	/**
	 * Retrieves information about the active pro plugins.
	 *
	 * @param array  $active_sb_plugins An array of active Smash Balloon plugins.
	 * @param string $license_key The license key for the pro plugins.
	 * @return array An array containing information about the active pro plugins.
	 */
	private function getProPluginsInfo($active_sb_plugins, $license_key)
	{
		$plugins = array(
			'social_wall' => array(
				'plugin' => $active_sb_plugins['social_wall_plugin'],
				'title' => __('Social Wall', 'instagram-feed'),
				'description' => __('Combine feeds from all of our plugins into a single wall', 'instagram-feed'),
				'icon' => 'social-wall-icon.svg',
				'permalink' => sprintf('https://smashballoon.com/social-wall/demo?license_key=%s&upgrade=true&utm_campaign=instagram-pro&utm_source=about&utm_medium=social-wall', $license_key),
				'installed' => $active_sb_plugins['is_social_wall_installed'],
			),
			'feed_analytics' => array(
				'plugin' => $active_sb_plugins['feed_analytics_plugin'],
				'title' => __('Feed Analytics', 'instagram-feed'),
				'description' => __('Get in depth analytics on all your social feeds in a single place', 'instagram-feed'),
				'icon' => 'feed-analytics-icon.svg',
				'permalink' => sprintf('https://smashballoon.com/feed-analytics?license_key=%s&upgrade=true&utm_campaign=instagram-pro&utm_source=about&utm_medium=feed-analytics', $license_key),
				'installed' => $active_sb_plugins['is_feed_analytics_installed'],
			),
		);

		return array_map([$this, 'getPluginInfo'], $plugins);
	}

	/**
	 * Retrieves information about recommended plugins.
	 *
	 * @param array  $installed_plugins An array of installed plugins.
	 * @param string $images_url The URL to the images' directory.
	 *
	 * @return array An array containing information about recommended plugins.
	 */
	private function getRecommendedPluginsInfo($installed_plugins, $images_url)
	{
		$plugins = array(
			'wpforms' => array(
				'plugin' => 'wpforms-lite/wpforms.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/wpforms-lite.zip',
				'title' => __('WPForms', 'instagram-feed'),
				'description' => __('The most beginner friendly drag & drop WordPress forms plugin allowing you to create beautiful contact forms, subscription forms, payment forms, and more in minutes, not hours!', 'instagram-feed'),
				'icon' => $images_url . 'plugin-wpforms.png',
			),
			'monsterinsights' => array(
				'plugin' => 'google-analytics-for-wordpress/googleanalytics.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
				'title' => __('MonsterInsights', 'instagram-feed'),
				'description' => __('MonsterInsights makes it “effortless” to properly connect your WordPress site with Google Analytics, so you can start making data-driven decisions to grow your business.', 'instagram-feed'),
				'icon' => $images_url . 'plugin-mi.png',
			),
			'optinmonster' => array(
				'plugin' => 'optinmonster/optin-monster-wp-api.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/optinmonster.zip',
				'title' => __('OptinMonster', 'instagram-feed'),
				'description' => __('Our high-converting optin forms like Exit-Intent® popups, Fullscreen Welcome Mats, and Scroll boxes help you dramatically boost conversions and get more email subscribers.', 'instagram-feed'),
				'icon' => $images_url . 'plugin-om.png',
			),
			'wp_mail_smtp' => array(
				'plugin' => 'wp-mail-smtp/wp_mail_smtp.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
				'title' => __('WP Mail SMTP', 'instagram-feed'),
				'description' => __('Make sure your website\'s emails reach the inbox. Our goal is to make email deliverability easy and reliable. Trusted by over 1 million websites.', 'instagram-feed'),
				'icon' => $images_url . 'plugin-smtp.png',
			),
			'rafflepress' => array(
				'plugin' => 'rafflepress/rafflepress.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/rafflepress.zip',
				'title' => __('RafflePress', 'instagram-feed'),
				'description' => __('Turn your visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with powerful viral giveaways & contests.', 'instagram-feed'),
				'icon' => $images_url . 'plugin-rp.png',
			),
			'aioseo' => array(
				'plugin' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
				'title' => __('All in One SEO Pack', 'instagram-feed'),
				'description' => __('Out-of-the-box SEO for WordPress. Features like XML Sitemaps, SEO for custom post types, SEO for blogs, business sites, or ecommerce sites, and much more.', 'instagram-feed'),
				'icon' => $images_url . 'plugin-seo.png',
			),
		);

		$recommended_plugins = array();

		foreach ($plugins as $key => $plugin) {
			$recommended_plugins[$key] = array(
				'plugin' => $plugin['plugin'],
				'download_plugin' => $plugin['download_plugin'],
				'title' => $plugin['title'],
				'description' => $plugin['description'],
				'icon' => $plugin['icon'],
				'installed' => isset($installed_plugins[$plugin['plugin']]),
				'activated' => is_plugin_active($plugin['plugin']),
			);
		}

		return $recommended_plugins;
	}

	/**
	 * About Us Page View Template
	 *
	 * @since 6.0
	 */
	public function about_us()
	{
		SBI_View::render('about.page');
	}

	/**
	 * Retrieves information about the plugin.
	 *
	 * @param array $plugin_data An array containing plugin data.
	 * @return array An array containing the plugin information.
	 */
	private function getPluginInfo($plugin_data)
	{
		return [
			'plugin' => $plugin_data['plugin'],
			'download_plugin' => isset($plugin_data['download_plugin']) ? $plugin_data['download_plugin'] : null,
			'permalink' => isset($plugin_data['permalink']) ? $plugin_data['permalink'] : null,
			'title' => $plugin_data['title'],
			'description' => $plugin_data['description'],
			'icon' => SBI_PLUGIN_URL . 'admin/assets/img/' . $plugin_data['icon'],
			'installed' => $plugin_data['installed'],
			'activated' => is_plugin_active($plugin_data['plugin']),
		];
	}
}
