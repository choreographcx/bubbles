<?php

/**
 * Instagram oEmbed Metadata Extractor
 *
 * Extracts thumbnail URLs from Instagram post HTML metadata as recommended by Instagram
 * when they deprecated the thumbnail_url field in the oEmbed API.
 *
 * Official Instagram guidance:
 * "Since Instagram will no longer return thumbnails through the oEmbed API,
 * we recommend that developers generate their own thumbnails by accessing
 * the HTML metadata directly from the Instagram post used for the oEmbed request."
 *
 * @since 6.8.2
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class SB_Instagram_OEmbed_Metadata
{
	/**
	 * Cache group for metadata
	 */
	const CACHE_GROUP = 'sbi_oembed_metadata';

	/**
	 * Cache expiration (14 days - matches sbi_single_cache duration)
	 */
	const CACHE_EXPIRATION = 1209600; // 14 days in seconds

	/**
	 * Extract thumbnail URL from Instagram post URL by parsing HTML metadata
	 *
	 * This follows Instagram's official recommendation to parse og:image
	 * from post HTML after oEmbed thumbnail_url deprecation.
	 *
	 * @param string $post_url Instagram post URL (e.g., https://www.instagram.com/p/ABC123/)
	 * @return array|false Array with metadata on success, false on failure
	 * @since 6.8.2
	 */
	public static function extract_metadata($post_url)
	{
		// Validate URL
		if (!self::is_valid_instagram_url($post_url)) {
			return false;
		}

		// Check cache first
		$cache_key = self::get_cache_key($post_url);
		$cached = get_transient($cache_key);

		if ($cached !== false) {
			return $cached;
		}

		// Fetch HTML from Instagram
		$html = self::fetch_instagram_html($post_url);

		if (!$html) {
			return false;
		}

		// Parse metadata from HTML
		$metadata = self::parse_metadata($html, $post_url);

		if ($metadata) {
			// Cache the result
			set_transient($cache_key, $metadata, self::CACHE_EXPIRATION);
		}

		return $metadata;
	}

	/**
	 * Validate Instagram post URL
	 *
	 * @param string $url URL to validate
	 * @return bool True if valid Instagram post URL
	 * @since 6.8.2
	 */
	private static function is_valid_instagram_url($url)
	{
		if (empty($url) || !is_string($url)) {
			return false;
		}

		// Match Instagram post URLs: /p/XXXX or /reel/XXXX
		return preg_match('#^https?://(www\.)?instagram\.com/(p|reel)/[A-Za-z0-9_-]+/?#', $url);
	}

	/**
	 * Generate cache key for a post URL
	 *
	 * @param string $post_url Post URL
	 * @return string Cache key
	 * @since 6.8.2
	 */
	private static function get_cache_key($post_url)
	{
		// Hash is used purely for data identification and not for security purposes.
		return self::CACHE_GROUP . '_' . md5($post_url);
	}

	/**
	 * Fetch HTML content from Instagram post URL
	 *
	 * @param string $post_url Post URL
	 * @return string|false HTML content or false on failure
	 * @since 6.8.2
	 */
	private static function fetch_instagram_html($post_url)
	{
		// Use WordPress HTTP API
		$response = wp_remote_get($post_url, array(
			'timeout' => 15,
			'redirection' => 5,
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			'headers' => array(
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
				'Accept-Language' => 'en-US,en;q=0.5',
				'Accept-Encoding' => 'gzip, deflate, br',
				'Connection' => 'keep-alive',
				'Upgrade-Insecure-Requests' => '1',
				'Sec-Fetch-Dest' => 'document',
				'Sec-Fetch-Mode' => 'navigate',
				'Sec-Fetch-Site' => 'none',
			),
		));

		if (is_wp_error($response)) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code($response);
		if ($status_code !== 200) {
			return false;
		}

		$html = wp_remote_retrieve_body($response);

		if (empty($html)) {
			return false;
		}

		return $html;
	}

	/**
	 * Parse metadata from HTML content
	 *
	 * Extracts Open Graph metadata and other relevant information
	 *
	 * @param string $html HTML content
	 * @param string $post_url Original post URL for reference
	 * @return array|false Metadata array or false on failure
	 * @since 6.8.2
	 */
	private static function parse_metadata($html, $post_url)
	{
		// Store previous libxml error state and suppress warnings from malformed HTML
		$previous_libxml_state = libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		@$dom->loadHTML($html);

		libxml_clear_errors();

		// Restore previous libxml error state
		libxml_use_internal_errors($previous_libxml_state);

		$xpath = new DOMXPath($dom);

		// Extract Open Graph metadata
		$metadata = array(
			'thumbnail_url' => null,
			'thumbnail_width' => null,
			'thumbnail_height' => null,
			'title' => null,
			'description' => null,
			'author_name' => null,
			'video_url' => null,
			'is_video' => false,
			'extracted_at' => time(),
			'source_url' => $post_url,
		);

		// Extract og:image (thumbnail)
		$og_image = $xpath->query('//meta[@property="og:image"]/@content');
		if ($og_image->length > 0) {
			$metadata['thumbnail_url'] = $og_image->item(0)->nodeValue;
		}

		// Extract og:image:width
		$og_image_width = $xpath->query('//meta[@property="og:image:width"]/@content');
		if ($og_image_width->length > 0) {
			$metadata['thumbnail_width'] = intval($og_image_width->item(0)->nodeValue);
		}

		// Extract og:image:height
		$og_image_height = $xpath->query('//meta[@property="og:image:height"]/@content');
		if ($og_image_height->length > 0) {
			$metadata['thumbnail_height'] = intval($og_image_height->item(0)->nodeValue);
		}

		// Extract og:title
		$og_title = $xpath->query('//meta[@property="og:title"]/@content');
		if ($og_title->length > 0) {
			$metadata['title'] = $og_title->item(0)->nodeValue;
		}

		// Extract og:description
		$og_description = $xpath->query('//meta[@property="og:description"]/@content');
		if ($og_description->length > 0) {
			$metadata['description'] = $og_description->item(0)->nodeValue;
		}

		// Extract og:video (if it's a video post)
		$og_video = $xpath->query('//meta[@property="og:video"]/@content');
		if ($og_video->length > 0) {
			$metadata['video_url'] = $og_video->item(0)->nodeValue;
			$metadata['is_video'] = true;
		}

		// Extract og:video:secure_url (preferred for HTTPS)
		$og_video_secure = $xpath->query('//meta[@property="og:video:secure_url"]/@content');
		if ($og_video_secure->length > 0) {
			$metadata['video_url'] = $og_video_secure->item(0)->nodeValue;
			$metadata['is_video'] = true;
		}

		// Try to extract author name from various sources
		// 1. Try og:title pattern (often includes username)
		if (!empty($metadata['title'])) {
			if (preg_match('/^(.+?)\s+(?:on Instagram|•)/', $metadata['title'], $matches)) {
				$metadata['author_name'] = trim($matches[1]);
			}
		}

		// 2. Try meta name="author"
		if (empty($metadata['author_name'])) {
			$author_meta = $xpath->query('//meta[@name="author"]/@content');
			if ($author_meta->length > 0) {
				$metadata['author_name'] = $author_meta->item(0)->nodeValue;
			}
		}

		// Must have at least a thumbnail URL to be valid
		if (empty($metadata['thumbnail_url'])) {
			return false;
		}

		return $metadata;
	}

	/**
	 * Extract thumbnail URL for a specific post
	 *
	 * Convenience method that returns just the thumbnail URL
	 *
	 * @param string $post_url Instagram post URL
	 * @return string|false Thumbnail URL or false on failure
	 * @since 6.8.2
	 */
	public static function get_thumbnail_url($post_url)
	{
		$metadata = self::extract_metadata($post_url);

		if ($metadata && !empty($metadata['thumbnail_url'])) {
			return $metadata['thumbnail_url'];
		}

		return false;
	}

	/**
	 * Clear cached metadata for a post URL
	 *
	 * @param string $post_url Post URL
	 * @return bool True on success, false on failure
	 * @since 6.8.2
	 */
	public static function clear_cache($post_url)
	{
		$cache_key = self::get_cache_key($post_url);
		return delete_transient($cache_key);
	}

	/**
	 * Clear all cached metadata
	 *
	 * @return void
	 * @since 6.8.2
	 */
	public static function clear_all_cache()
	{
		global $wpdb;

		// Delete all transients starting with our cache group
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				'%' . $wpdb->esc_like('_transient_' . self::CACHE_GROUP) . '%'
			)
		);
	}

	/**
	 * Get cache statistics
	 *
	 * @return array Cache statistics
	 * @since 6.8.2
	 */
	public static function get_cache_stats()
	{
		global $wpdb;

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				'%' . $wpdb->esc_like('_transient_' . self::CACHE_GROUP) . '%'
			)
		);

		return array(
			'total_cached' => intval($total),
			'cache_group' => self::CACHE_GROUP,
			'expiration' => self::CACHE_EXPIRATION,
			'expiration_human' => human_time_diff(0, self::CACHE_EXPIRATION),
		);
	}
}
