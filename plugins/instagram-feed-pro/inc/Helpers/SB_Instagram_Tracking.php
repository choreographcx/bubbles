<?php

namespace InstagramFeed\Helpers;

use InstagramFeed\Builder\SBI_Db;
use InstagramFeed\Builder\SBI_Feed_Saver;
use InstagramFeed\Builder\SBI_Source;
use SB_Instagram_API_Connect_Pro;
use SB_Instagram_Cache;
use SB_Instagram_Settings;
use SB_Instagram_Settings_Pro;
use InstagramFeed\Vendor\Smashballoon\Framework\Utilities\UsageTracking;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Usage tracking
 *
 * @access public
 * @return void
 * @since  5.6
 */
class SB_Instagram_Tracking
{
	/**
	 * Class constructor.
	 *
	 * Hooks into WordPress actions and filters to schedule and send usage tracking data.
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action('init', array($this, 'schedule_send'));
		add_filter('cron_schedules', array($this, 'add_schedules'));
		add_action('sbi_usage_tracking_cron', array($this, 'send_checkin'));
		add_filter('sb_usage_tracking_data', array($this, 'filter_usage_tracking_data'), 10, 2);
	}

	/**
	 * Sends a check-in request to the usage tracking server.
	 *
	 * @param bool $override Optional. Override tracking check. Default false.
	 * @param bool $ignore_last_checkin Optional. Ignore last check-in time. Default false.
	 * @return bool True if the update was sent, false otherwise.
	 */
	public function send_checkin($override = false, $ignore_last_checkin = false)
	{

		$home_url = trailingslashit(home_url());
		if (strpos($home_url, 'smashballoon.com') !== false) {
			return false;
		}

		if (!$this->tracking_allowed() && !$override) {
			return false;
		}

		return UsageTracking::send_usage_update($this->get_data(), 'sbi');
	}

	/**
	 * Checks if tracking is allowed based on the 'sbi_usage_tracking' option.
	 *
	 * @return bool True if tracking is allowed, false otherwise.
	 */
	private function tracking_allowed()
	{
		$usage_tracking = get_option(
			'sbi_usage_tracking',
			array(
				'last_send' => 0,
				'enabled' => sbi_is_pro_version(),
			)
		);
		return isset($usage_tracking['enabled']) ? $usage_tracking['enabled'] : sbi_is_pro_version();
	}

	/**
	 * Retrieves data related to the current WordPress environment, theme, plugins, and Instagram feed settings.
	 *
	 * @return array An associative array containing various environment and plugin settings.
	 */
	public function get_data()
	{
		$data = array();

		// Retrieve current theme info.
		$theme_data = wp_get_theme();

		$count_b = 1;
		if (is_multisite()) {
			if (function_exists('get_blog_count')) {
				$count_b = get_blog_count();
			} else {
				$count_b = 'Not Set';
			}
		}

		$php_version = rtrim(ltrim(sanitize_text_field(phpversion())));
		$php_version = !empty($php_version) ? substr($php_version, 0, strpos($php_version, '.', strpos($php_version, '.') + 1)) : phpversion();

		global $wp_version;
		$data['this_plugin'] = 'if';
		$data['php_version'] = $php_version;
		$data['mi_version'] = SBIVER;
		$data['wp_version'] = $wp_version;
		$data['server'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
		$data['multisite'] = is_multisite();
		$data['url'] = home_url();
		$data['themename'] = $theme_data->Name;
		$data['themeversion'] = $theme_data->Version;
		$data['settings'] = array();
		$data['pro'] = (int)sbi_is_pro_version();
		$data['sites'] = $count_b;
		$data['usagetracking'] = get_option('sbi_usage_tracking_config', false);
		$num_users = function_exists('count_users') ? count_users() : 'Not Set';
		$data['usercount'] = is_array($num_users) ? $num_users['total_users'] : 1;
		$data['timezoneoffset'] = date('P');

		$settings_to_send = array();
		$raw_settings = get_option('sb_instagram_settings', array());
		$feeds = SBI_Db::feeds_query();
		$feed_settings = [];

		if (!empty($feeds)) {
			// recursive json decode.
			$feed_settings = json_decode($feeds[0]['settings'], true);
			// map array values to key => value pairs in the $feed_settings array.
			array_walk($feed_settings, static function ($value, $key) use (&$feed_settings) {
				if (is_array($value)) {
					unset($feed_settings[$key]);
					foreach ($value as $value_key => $value_item) {
						$feed_settings[$key . '_' . $value_key] = $value_item;
					}
				}
			}, []);
		}

		$settings_to_send = array_merge($settings_to_send, $feed_settings);
		$con_bus_accounts = 0;
		$recently_searched_hashtags = 0;
		$access_tokens_tried = array();
		$sources = SBI_Db::source_query();
		if (!empty($sources)) {
			$sources = SBI_Source::convert_sources_to_connected_accounts($sources);
			foreach ($sources as $source) {
				if (isset($source['account_type']) && $source['account_type'] === 'business') {
					$con_bus_accounts++;
					$source['type'] = $source['account_type'];
					if (!in_array($source['access_token'], $access_tokens_tried, true) && class_exists('SB_Instagram_API_Connect_Pro')) {
						$access_tokens_tried[] = $source['access_token'];
						$connection = new SB_Instagram_API_Connect_Pro($source, 'recently_searched_hashtags', array('hashtag' => ''));
						$connection->connect();

						$recently_searched_data = !$connection->is_wp_error() ? $connection->get_data() : false;
						$num_hashatags_searched = $recently_searched_data && isset($recently_searched_data) && !isset($recently_searched_data['data']) && is_array($recently_searched_data) ? count($recently_searched_data) : 0;
						$recently_searched_hashtags = $recently_searched_hashtags + $num_hashatags_searched;
					}
				}
			}
		}
		$settings_to_send['business_accounts'] = $con_bus_accounts;
		$settings_to_send['recently_searched_hashtags'] = $recently_searched_hashtags;
		$sbi_cache = new SB_Instagram_Cache('');

		$settings_to_send['num_found_feed_caches'] = (int)$sbi_cache->get_cache_count();
		$settings_to_send['recently_requested_caches'] = (int)$sbi_cache->get_cache_count(true);

		$settings_to_send['custom_header_template'] = '' !== locate_template('sbi/header.php', false, false) ? 1 : 0;
		$settings_to_send['custom_header_boxed_template'] = '' !== locate_template('sbi/header-boxed.php', false, false) ? 1 : 0;
		$settings_to_send['custom_header_generic_template'] = '' !== locate_template('sbi/header-generic.php', false, false) ? 1 : 0;
		$settings_to_send['custom_header_text_template'] = '' !== locate_template('sbi/header-text.php', false, false) ? 1 : 0;
		$settings_to_send['custom_item_template'] = '' !== locate_template('sbi/item.php', false, false) ? 1 : 0;
		$settings_to_send['custom_footer_template'] = '' !== locate_template('sbi/footer.php', false, false) ? 1 : 0;
		$settings_to_send['custom_feed_template'] = '' !== locate_template('sbi/feed.php', false, false) ? 1 : 0;
		$settings_to_send['num_found_feeds'] = count($feeds);

		// Track og:image fallback usage (for oEmbed thumbnail_url deprecation)
		$settings_to_send['using_oembed_fallback'] = get_option('sbi_using_oembed_fallback') ? 1 : 0;

		$sbi_current_white_names = get_option('sb_instagram_white_list_names', array());
		if (empty($sbi_current_white_names)) {
			$settings_to_send['num_white_lists'] = 0;
		} else {
			$settings_to_send['num_white_lists'] = count($sbi_current_white_names);
		}

		$data['settings'] = $settings_to_send;

		// Retrieve current plugin information.
		if (!function_exists('get_plugins')) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		$active_plugins = get_option('active_plugins', array());
		$plugins_to_send = array();

		foreach ($plugins as $plugin_path => $plugin) {
			// If the plugin isn't active, don't show it.
			if (!in_array($plugin_path, $active_plugins)) {
				continue;
			}

			$plugins_to_send[] = $plugin['Name'];
		}

		$data['active_plugins'] = $plugins_to_send;
		$data['locale'] = get_locale();

		return $data;
	}

	/**
	 * Schedules the usage tracking data sending if not already scheduled.
	 *
	 * @return void
	 */
	public function schedule_send()
	{
		if (!wp_next_scheduled('sbi_usage_tracking_cron')) {
			$tracking = array();
			$tracking['day'] = rand(0, 6);
			$tracking['hour'] = rand(0, 23);
			$tracking['minute'] = rand(0, 59);
			$tracking['second'] = rand(0, 59);
			$tracking['offset'] = ($tracking['day'] * DAY_IN_SECONDS) +
				($tracking['hour'] * HOUR_IN_SECONDS) +
				($tracking['minute'] * MINUTE_IN_SECONDS) +
				$tracking['second'];
			$last_sunday = strtotime('next sunday') - (7 * DAY_IN_SECONDS);
			if (($last_sunday + $tracking['offset']) > time() + 6 * HOUR_IN_SECONDS) {
				$tracking['initsend'] = $last_sunday + $tracking['offset'];
			} else {
				$tracking['initsend'] = strtotime('next sunday') + $tracking['offset'];
			}

			wp_schedule_event($tracking['initsend'], 'weekly', 'sbi_usage_tracking_cron');
			update_option('sbi_usage_tracking_config', $tracking);
		}
	}

	/**
	 * Adds a custom schedule for weekly events to the existing schedules.
	 *
	 * @param array $schedules An array of existing schedules.
	 * @return array Modified array of schedules with the added weekly schedule.
	 */
	public function add_schedules($schedules = array())
	{
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display' => __('Once Weekly', 'instagram-feed'),
		);
		return $schedules;
	}

	/**
	 * Filter the usage tracking data
	 *
	 * @param array  $data The data to be sent to the usage tracking server.
	 * @param string $plugin_slug The plugin slug.
	 *
	 * @handles sb_usage_tracking_data
	 *
	 * @return array|mixed
	 */
	public function filter_usage_tracking_data($data, $plugin_slug)
	{
		if ('sbi' !== $plugin_slug) {
			return $data;
		}

		if (!is_array($data)) {
			return $data;
		}

		if (!isset($data['settings'])) {
			$data['settings'] = [];
		}

		$tracked_boolean_settings = explode(
			',',
			'width,widthunit,widthresp,height,heightunit,disablelightbox,captionlinks,offset,apinum,
			lightboxcomments,numcomments,hovereffect,hovercolor,hovertextcolor,hoverdisplay,hovercaptionlength,
			background,imageres,showcaption,captionlength,captioncolor,captionsize,showlikes,likescolor,likessize,
			hidephotos,poststyle,postbgcolor,postcorners,boxshadow,showbutton,buttoncolor,buttonhovercolor,
			buttontextcolor,buttontext,showfollow,followcolor,followhovercolor,followtextcolor,followtext,
			showheader,headercolor,headerstyle,showfollowers,showbio,custombio,customavatar,headerprimarycolor,
			headersecondarycolor,headersize,stories,storiestime,headeroutside,headertext,headertextsize,
			headertextcolor,class,ajaxtheme,excludewords,includewords,maxrequests,carouselrows,carouselarrows,
			carouselpag,carouselautoplay,carouseltime,highlightoffset,highlightpattern,highlighthashtag,
			highlightids,whitelist,autoscroll,autoscrolldistance,permanent,mediavine,customtemplates,
			colorpalette,custombgcolor1,customtextcolor1,customtextcolor2,customlinkcolor1,custombuttoncolor1,
			custombuttoncolor2,photosposts,videosposts,reelsposts,shoppablefeed,shoppablelist,moderationlist,
			customBlockModerationlist,enablemoderationmode'
		);

		$tracked_string_settings = explode(
			',',
			'type,order,sortby,num,nummobile,cols,colstablet,colsmobile,layout,media,videotypes,carouselloop,highlighttype,gdpr,feedtheme'
		);

		$feeds = SBI_Db::feeds_query();
		$settings_defaults = SBI_Feed_Saver::settings_defaults();

		// Track settings of the first feed.
		if (!empty($feeds)) {
			$feed = $feeds[0];
			$feed_settings = (new SBI_Feed_Saver($feed['id']))->get_feed_settings();

			if (!is_array($feed_settings)) {
				return $data;
			}

			$booleans = UsageTracking::tracked_settings_to_booleans($tracked_boolean_settings, $settings_defaults, $feed_settings);
			$strings = UsageTracking::tracked_settings_to_strings($tracked_string_settings, $feed_settings);

			if (is_array($booleans) && is_array($strings)) {
				$data['settings'] = array_merge($data['settings'], $booleans, $strings);
			}
		}

		return $data;
	}

	/**
	 * Normalize and format the given key and value.
	 *
	 * @param string $key The setting key.
	 * @param mixed  $value The value associated with the key.
	 *
	 * @return mixed The normalized and formatted value.
	 */
	private function normalize_and_format($key, $value)
	{
		$normal_bools = array(
			'sb_instagram_preserve_settings',
			'sb_instagram_ajax_theme',
			'enqueue_js_in_head',
			'disable_js_image_loading',
			'sb_instagram_disable_resize',
			'sb_instagram_favor_local',
			'sbi_hover_inc_username',
			'sbi_hover_inc_icon',
			'sbi_hover_inc_date',
			'sbi_hover_inc_instagram',
			'sbi_hover_inc_location',
			'sbi_hover_inc_caption',
			'sbi_hover_inc_likes',
			'sb_instagram_disable_lightbox',
			'sb_instagram_captionlinks',
			'sb_instagram_show_btn',
			'sb_instagram_show_caption',
			'sb_instagram_lightbox_comments',
			'sb_instagram_show_meta',
			'sb_instagram_show_header',
			'sb_instagram_show_followers',
			'sb_instagram_show_bio',
			'sb_instagram_outside_scrollable',
			'sb_instagram_stories',
			'sb_instagram_show_follow_btn',
			'sb_instagram_autoscroll',
			'sb_instagram_disable_font',
			'sb_instagram_backup',
			'sb_instagram_at',
			'sb_ajax_initial',
			'sbi_br_adjust',
			'sb_instagram_feed_width_resp',
			'enqueue_css_in_shortcode',
			'sb_instagram_disable_mob_swipe',
			'sb_instagram_disable_awesome',
			'sb_instagram_media_vine',
			'custom_template',
			'disable_admin_notice',
			'enable_email_report',
			'sb_instagram_carousel',
			'sb_instagram_carousel_arrows',
			'sb_instagram_carousel_pag',
			'sb_instagram_carousel_autoplay',
		);
		$custom_text_settings = array(
			'sb_instagram_btn_text',
			'sb_instagram_follow_btn_text',
			'sb_instagram_custom_bio',
			'sb_instagram_custom_avatar',
			'sb_instagram_custom_css',
			'sb_instagram_custom_js',
			'email_notification_addresses',
		);
		$comma_separate_counts_settings = array(
			'sb_instagram_user_id',
			'sb_instagram_tagged_ids',
			'sb_instagram_hashtag',
			'sb_instagram_highlight_ids',
			'sb_instagram_highlight_hashtag',
			'sb_instagram_hide_photos',
			'sb_instagram_exclude_words',
			'sb_instagram_include_words',
		);
		$defaults = class_exists('SB_Instagram_Settings_Pro') ? SB_Instagram_Settings_Pro::default_settings() : SB_Instagram_Settings::default_settings();

		if (is_array($value)) {
			if (empty($value)) {
				return 0;
			}
			return count($value);
			// 0 for anything that might be false, 1 for everything else.
		} elseif (in_array($key, $normal_bools, true)) {
			if (in_array($value, array(false, 0, '0', 'false', ''), true)) {
				return 0;
			}
			return 1;

			// if a custom text setting, we just want to know if it's different than the default.
		} elseif (in_array($key, $custom_text_settings, true)) {
			if ($defaults[$key] === $value) {
				return 0;
			}
			return 1;
		} elseif (in_array($key, $comma_separate_counts_settings, true)) {
			if (str_replace(' ', '', $value) === '') {
				return 0;
			}
			$split_at_comma = explode(',', $value);
			return count($split_at_comma);
		}

		return $value;
	}
}
