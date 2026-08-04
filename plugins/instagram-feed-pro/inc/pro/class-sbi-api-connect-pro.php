<?php

if (!defined('ABSPATH')) {
	die('-1');
}

/**
 * Class SB_Instagram_API_Connect_Pro
 *
 * Adds support for additional endpoints:
 *
 * - Personal account comments
 * - Business account top and recent hashtags
 * - Business account stories
 * - Business account comments
 * - Business account hashtag IDs
 *
 * @since 5.0
 */
class SB_Instagram_API_Connect_Pro extends SB_Instagram_API_Connect
{
	/**
	 * The response from the API request
	 *
	 * @var array
	 */
	protected $response;

	/**
	 * Determines if the given type allows actions after paging.
	 *
	 * @param string $type The type to check.
	 * @return bool True if the type allows actions after paging, false otherwise.
	 */
	public function type_allows_after_paging($type)
	{
		return $type === 'tagged';
	}

	/**
	 * Builds the Facebook API URL for the given connected account.
	 *
	 * @param array  $connected_account The connected account identifier.
	 * @param string $endpoint The API endpoint to connect to.
	 * @param array  $params Additional params related to the request.
	 * @param string $access_token The access token for authentication.
	 *
	 * @return string The constructed Facebook API URL.
	 */
	protected function buildFacebookUrl($connected_account, $endpoint, $params, $access_token)
	{
		$num = !empty($params['num']) ? (int)$params['num'] : 33;
		$num = min($num, 200);
		$paging = isset($params['cursor']) ? '&after=' . $params['cursor'] : '';

		$header_fields = 'biography,id,username,website,followers_count,media_count,profile_picture_url,name';

		// Meta's Graph API enforces a different field whitelist per endpoint, so we keep three
		// separate field sets instead of one shared string:
		//
		// - $media_fields_hashtag  — /top_media + /recent_media. Rejects thumbnail_url at the
		//   outer level on both, and additionally rejects children{thumbnail_url} on /top_media.
		//   Keeping thumbnail_url out everywhere is the only set that passes both endpoints.
		// - $media_fields_tagged   — /tags. SMASH-1044 added thumbnail_url so VIDEO posts don't
		//   fall through to fetch_single_media() (oEmbed/Media-API), which errors on reel CDNs
		//   and loads slowly. This endpoint accepts thumbnail_url fine.
		// - $media_fields_default  — owned /media. Adds is_shared_to_feed (SMASH-1105) to
		//   identify Instagram Trial Reels, which only exist on the authenticated user's own
		//   feed and are rejected on hashtag/tagged endpoints.
		$media_fields_hashtag = 'media_url,media_product_type,caption,id,media_type,timestamp,comments_count,like_count,permalink,children%7Bmedia_url,id,media_type,timestamp,permalink%7D';
		$media_fields_tagged  = 'media_url,thumbnail_url,media_product_type,caption,id,media_type,timestamp,comments_count,like_count,permalink,children%7Bmedia_url,id,media_type,timestamp,permalink,thumbnail_url%7D';
		$media_fields_default = 'media_url,media_product_type,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,is_shared_to_feed,children%7Bmedia_url,id,media_type,timestamp,permalink,thumbnail_url%7D';

		$url = 'https://graph.facebook.com/';
		switch ($endpoint) {
			case 'header':
				$url .= $connected_account['user_id'] . '?fields=' . $header_fields . '&access_token=' . $access_token;
				break;
			case 'stories':
				$url .= $connected_account['user_id'] . '/stories?fields=media_url,caption,id,media_type,permalink,children%7Bmedia_url,id,media_type,permalink%7D&limit=100&access_token=' . $access_token;
				break;
			case 'recent_hashtag_refetch':
				$url .= $params['hashtag_id'] . '/top_media?user_id=' . $connected_account['user_id'] . '&fields=media_url,media_product_type,id,media_type,permalink&limit=50&access_token=' . $access_token;
				break;
			case 'hashtags_top':
				$url .= $params['hashtag_id'] . '/top_media?user_id=' . $connected_account['user_id'] . '&fields=' . $media_fields_hashtag . '&limit=' . min($num, 50) . '&access_token=' . $access_token;
				break;
			case 'hashtags_recent':
				$url .= $params['hashtag_id'] . '/recent_media?user_id=' . $connected_account['user_id'] . '&fields=' . $media_fields_hashtag . '&limit=' . min($num, 50) . '&access_token=' . $access_token;
				break;
			case 'recently_searched_hashtags':
				$url .= $connected_account['user_id'] . '/recently_searched_hashtags?access_token=' . $access_token . '&limit=40';
				break;
			case 'tagged':
				$url .= $connected_account['user_id'] . '/tags?user_id=' . $connected_account['user_id'] . '&fields=' . $media_fields_tagged . '&limit=' . $num . '&access_token=' . $access_token . $paging;
				break;
			case 'ig_hashtag_search':
				$url .= 'ig_hashtag_search?user_id=' . $connected_account['user_id'] . '&q=' . urlencode($params['hashtag']) . '&access_token=' . $access_token;
				break;
			case 'comments':
				$url .= $params['post_id'] . '/comments?fields=text,username&access_token=' . $access_token;
				break;
			case 'media':
				// Single media endpoint for fetching individual post details
				$media_id = !empty($params['media_id']) ? $params['media_id'] : '';
				$media_fields_single = !empty($params['fields']) ? $params['fields'] : 'thumbnail_url,media_url,media_type';
				$url .= $media_id . '?fields=' . $media_fields_single . '&access_token=' . $access_token;
				break;
			default:
				$url .= $connected_account['user_id'] . '/media?fields=' . $media_fields_default . '&limit=' . $num . '&access_token=' . $access_token;
				break;
		}

		return $url;
	}
}
