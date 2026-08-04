<?php

if (!defined('ABSPATH')) {
	die('-1');
}

use InstagramFeed\SB_Instagram_Data_Encryption;

/**
 * Class SB_Instagram_Single
 *
 * Uses oEmbeds to get data about single Instagram posts
 *
 * @since 2.5.3/5.8.3
 */
class SB_Instagram_Single
{
	/**
	 * Object for encrypting and decrypting data.
	 *
	 * @var object|SB_Instagram_Data_Encryption
	 * @since 5.14.5
	 */
	protected $encryption;

	/**
	 * Permalink of the post.
	 *
	 * @var string
	 */
	private $permalink;

	/**
	 * Permalink ID of the post.
	 *
	 * @var string
	 */
	private $permalink_id;

	/**
	 * Post data.
	 *
	 * @var array
	 */
	private $post;

	/**
	 * Error message.
	 *
	 * @var array
	 */
	private $error;

	/**
	 * Media ID for direct Graph API access.
	 *
	 * @var string
	 */
	private $media_id;

	/**
	 * Post data from original source (contains username for account matching).
	 *
	 * @var array
	 */
	private $post_data;

	/**
	 * Cached connected accounts mapped by username for efficient lookup.
	 *
	 * @var array|null
	 */
	private static $connected_accounts_cache = null;

	/**
	 * SB_Instagram_Single constructor.
	 *
	 * @param string $permalink_or_permalink_id Permalink or permalink ID of the post.
	 * @param array  $post_data Optional. Original post data containing media_id and username.
	 */
	public function __construct($permalink_or_permalink_id, $post_data = array())
	{
		if (strpos($permalink_or_permalink_id, 'http') !== false) {
			$this->permalink = $permalink_or_permalink_id;
			$exploded_permalink = explode('/', $permalink_or_permalink_id);
			$permalink_id = $exploded_permalink[4];

			$this->permalink_id = $permalink_id;
		} else {
			$this->permalink_id = $permalink_or_permalink_id;
			$this->permalink = 'https://www.instagram.com/p/' . $this->permalink_id;
		}
		$this->error = false;
		$this->post_data = $post_data;

		// Extract media_id from post_data if available
		$this->media_id = !empty($post_data['id']) ? $post_data['id'] : '';

		$this->encryption = new SB_Instagram_Data_Encryption();
	}

	/**
	 * Sets post data from cache or fetches new data
	 * if it doesn't exist or hasn't been updated recently
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function init()
	{
		$this->post = $this->maybe_saved_data();

		if ((empty($this->post) || !$this->was_recently_updated()) && !$this->should_delay_fetch_request()) {
			$data = $this->fetch();
			if (!empty($data)) {
				$data = $this->parse_and_restructure($data);
				$this->post = $data;
				$this->update_last_update_timestamp();
				$this->update_single_cache();
			} elseif ($data === false) {
				$this->add_fetch_request_delay();
			}
		}
	}

	/**
	 * Returns whatever data exists or empty array
	 *
	 * @return array
	 *
	 * @since 2.5.3/5.8.3
	 */
	private function maybe_saved_data()
	{
		$stored_option = get_option('sbi_single_cache', array());
		if (!is_array($stored_option)) {
			$stored_option = json_decode($this->encryption->decrypt($stored_option), true);
		}
		$data = array();
		if (!empty($stored_option[$this->permalink_id])) {
			return $stored_option[$this->permalink_id];
		} else {
			$settings = get_option('sb_instagram_settings', array());

			$resize_disabled = isset($settings['sb_instagram_disable_resize']) && $settings['sb_instagram_disable_resize'] === 'on';

			if (!$resize_disabled) {
				global $wpdb;

				$posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;

				$results = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT json_data FROM $posts_table_name
						WHERE instagram_id = %s
						LIMIT 1",
						$this->permalink_id
					)
				);
				if (isset($results[0])) {
					$data = json_decode($this->encryption->decrypt($results[0]), true);
				}
			}
		}

		return $data;
	}

	/**
	 * Image URLs expire so this will compare when the data
	 * was last updated from the API
	 *
	 * @return bool
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function was_recently_updated()
	{
		if (!isset($this->post['last_update'])) {
			return false;
		}

		return (time() - 14 * DAY_IN_SECONDS) < $this->post['last_update'];
	}

	/**
	 * If there was a problem with the last fetch request, the plugin
	 * waits 5 minutes to try again to prevent excessive API calls
	 * and Instagram throttling
	 *
	 * @return bool
	 *
	 * @since 2.5.3/5.8.3
	 * @since 6.8.2 Renamed from should_delay_oembed_request() to be method-agnostic
	 */
	public function should_delay_fetch_request()
	{
		return get_transient('sbi_delay_fetch_' . $this->permalink_id) !== false;
	}

	/**
	 * Makes an HTTP request for fresh data from Media API or og:image extraction.
	 *
	 * Two-approach system for 100% coverage:
	 * 1. Media API - For owned posts (user posts with media_id and username)
	 * 2. og:image extraction - For all other posts (hashtag, tagged, others)
	 *
	 * This replaces the deprecated oEmbed API which no longer returns thumbnail_url.
	 *
	 * @return bool|mixed|null
	 *
	 * @since 2.5.3/5.8.3
	 * @since 6.8.2 Replaced oEmbed with Media API + og:image extraction
	 */
	public function fetch()
	{
		// APPROACH 1: Media API (for owned posts with media_id and username)
		// This works for user posts from connected accounts - provides best data quality
		if (!empty($this->media_id) && !$this->should_skip_media_api()) {
			$data = $this->fetch_from_media_api();

			// If Media API succeeds, return immediately
			if ($data !== false) {
				return $data;
			}
		}

		// APPROACH 2: og:image extraction from Instagram HTML
		// This is Instagram's officially recommended approach for thumbnail_url deprecation
		// Works for ALL post types: hashtag, tagged, user posts from any account
		if (class_exists('SB_Instagram_OEmbed_Metadata') && !empty($this->permalink)) {
			$data = $this->fetch_from_oembed_metadata();

			// If og:image extraction succeeds, return immediately
			if ($data !== false) {
				return $data;
			}
		}

		// If both approaches fail, return false
		// This triggers the delay mechanism to prevent excessive retries
		return false;
	}

	/**
	 * Checks if Media API should be skipped for this post.
	 *
	 * Media API only works for posts owned by the connected account.
	 * Hashtag and tagged posts belong to other users.
	 *
	 * @return bool True if Media API should be skipped
	 *
	 * @since 6.8.2
	 */
	private function should_skip_media_api()
	{
		// Skip for hashtag posts (have 'term' field)
		if (!empty($this->post_data['term'])) {
			return true;
		}

		// Skip for tagged posts (no 'username' field)
		if (empty($this->post_data['username'])) {
			return true;
		}

		return false;
	}

	/**
	 * Fetches data from Instagram Media API using media ID.
	 *
	 * Uses SB_Instagram_API_Connect class to handle proper authentication,
	 * token validation, and host selection (graph.instagram.com vs graph.facebook.com).
	 * This is an alternative to oEmbed which is deprecating thumbnail_url support.
	 *
	 * Note: Media API only works for posts owned by the connected account.
	 * Hashtag posts or posts from other users will fail and fallback to oEmbed.
	 *
	 * @return bool|array False on failure, array of data on success
	 *
	 * @since 6.8.2
	 */
	private function fetch_from_media_api()
	{
		// Get connected account for this post
		$connected_account = $this->get_connected_account_for_post();

		if (empty($connected_account)) {
			return false;
		}

		// Verify this post belongs to the connected account
		if (!empty($this->post_data['username']) && !empty($connected_account['username'])) {
			if ($this->post_data['username'] !== $connected_account['username']) {
				return false;
			}
		}

		// Use API Connect class to build URL with proper authentication
		$params = array(
			'media_id' => $this->media_id,
			'fields' => 'thumbnail_url,media_url,media_type'
		);

		$api_connect = new SB_Instagram_API_Connect($connected_account, 'media', $params);

		// Check for encryption/token errors before connecting
		if ($api_connect->has_encryption_error()) {
			return false;
		}

		// Make the API request
		$api_connect->connect();

		// Handle errors
		if ($api_connect->is_wp_error()) {
			return false;
		}

		$data = $api_connect->get_data();

		if ($api_connect->is_instagram_error($data)) {
			// Media API errors are expected for hashtag/tagged posts
			// Will fallback to og:image extraction
			return false;
		}

		return $data;
	}

	/**
	 * Gets the connected account for the current post.
	 *
	 * Uses username from post_data to match with connected accounts.
	 * Implements static caching to avoid repeated database queries.
	 *
	 * @return array|bool Connected account array or false if not found
	 *
	 * @since 6.8.2
	 */
	private function get_connected_account_for_post()
	{
		// Load and cache all connected accounts once per request
		if (self::$connected_accounts_cache === null) {
			self::$connected_accounts_cache = array();

			if (class_exists('SB_Instagram_Connected_Account')) {
				$all_accounts = SB_Instagram_Connected_Account::get_all_connected_accounts();

				// Build username => account map for O(1) lookup
				foreach ($all_accounts as $account) {
					if (!empty($account['username'])) {
						self::$connected_accounts_cache[$account['username']] = $account;
					}
				}
			}
		}

		// Try to get username from post_data
		$username = '';
		if (!empty($this->post_data['username'])) {
			$username = $this->post_data['username'];
		}

		// Direct lookup by username (O(1))
		if (!empty($username) && isset(self::$connected_accounts_cache[$username])) {
			return self::$connected_accounts_cache[$username];
		}

		// Fallback: return first available account
		if (!empty(self::$connected_accounts_cache)) {
			return reset(self::$connected_accounts_cache);
		}

		return false;
	}


	/**
	 * Fetches metadata from Instagram HTML using og:image extraction.
	 *
	 * This is Instagram's officially recommended approach for the
	 * thumbnail_url deprecation in oEmbed API.
	 *
	 * @return bool|array False on failure, array of data on success
	 *
	 * @since 6.8.2
	 */
	private function fetch_from_oembed_metadata()
	{
		$metadata = SB_Instagram_OEmbed_Metadata::extract_metadata($this->permalink);

		if (!$metadata || empty($metadata['thumbnail_url'])) {
			return false;
		}

		// Track that this site is using og:image fallback
		// It is used for usage tracking to understand how many sites are affected
		if (!get_option('sbi_using_oembed_fallback')) {
			update_option('sbi_using_oembed_fallback', time(), false);
		}

		// Convert metadata to format compatible with parse_and_restructure()
		$data = array(
			'thumbnail_url' => $metadata['thumbnail_url'],
			'media_url' => !empty($metadata['video_url']) ? $metadata['video_url'] : '',
			'media_type' => !empty($metadata['is_video']) ? 'VIDEO' : 'IMAGE',
		);

		// Include dimensions if available
		if (!empty($metadata['thumbnail_width'])) {
			$data['thumbnail_width'] = $metadata['thumbnail_width'];
		}
		if (!empty($metadata['thumbnail_height'])) {
			$data['thumbnail_height'] = $metadata['thumbnail_height'];
		}

		return $data;
	}

	/**
	 * If there's an error, fetch requests are delayed 5 minutes
	 * for the specific permalink/post to prevent excessive retries
	 *
	 * @since 2.5.3/5.8.3
	 * @since 6.8.2 Renamed from add_oembed_request_delay() to be method-agnostic
	 */
	public function add_fetch_request_delay()
	{
		set_transient('sbi_delay_fetch_' . $this->permalink_id, true, 300);
	}

	/**
	 * Data is restructured to look like regular API data
	 * for ease of use with other plugin features
	 *
	 * @param array $data Data to parse.
	 *
	 * @return array
	 * @since 2.5.3/5.8.3
	 * @since 6.8.2 Simplified to use Media API + og:image only
	 */
	private function parse_and_restructure($data)
	{
		$return = array(
			'thumbnail_url' => '',
			'media_url' => '',
			'id' => $this->permalink_id,
			'media_type' => isset($data['media_type']) ? $data['media_type'] : 'IMAGE',
		);

		// Copy thumbnail_url from API response (Media API or og:image)
		if (!empty($data['thumbnail_url'])) {
			$return['thumbnail_url'] = $data['thumbnail_url'];
		}

		// Copy media_url from API response
		if (!empty($data['media_url'])) {
			$return['media_url'] = $data['media_url'];
		}

		/**
		 * Filter the restructured post data from Media API or og:image
		 *
		 * Allows developers to modify post data after extraction
		 *
		 * @param array  $return       The restructured post data
		 * @param array  $data         Raw API/metadata response
		 * @param string $permalink    Post permalink
		 * @param string $permalink_id Post shortcode/ID
		 *
		 * @since 6.8.2
		 */
		$return = apply_filters('sbi_single_parse_and_restructure', $return, $data, $this->permalink, $this->permalink_id);

		return $return;
	}

	/**
	 * Track last API request due to some data expiring and
	 * needing to be refreshed
	 *
	 * @since 2.5.3/5.8.3
	 */
	private function update_last_update_timestamp()
	{
		$this->post['last_update'] = time();
	}

	/**
	 * Data retrieved with this method has it's own cache
	 *
	 * @since 2.5.3/5.8.3
	 */
	private function update_single_cache()
	{
		$stored_option = get_option('sbi_single_cache', array());
		if (!is_array($stored_option)) {
			$stored_option = json_decode($this->encryption->decrypt($stored_option), true);
		}
		$new = array($this->permalink_id => $this->post);
		$stored_option = array_merge($new, (array)$stored_option);
		// only latest 400 posts.
		$stored_option = array_slice((array)$stored_option, 0, 400);

		update_option('sbi_single_cache', $this->encryption->encrypt(sbi_json_encode($stored_option)), false);
	}

	/**
	 * Retrieves the post data
	 *
	 * @return array
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function get_post()
	{
		return $this->post;
	}

	/**
	 * Retrieves the error message
	 *
	 * @return array
	 */
	public function get_error()
	{
		return $this->error;
	}
}
