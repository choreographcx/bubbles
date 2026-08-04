<?php

if (!defined('ABSPATH')) {
	die('-1');
}

/**
 * Class SB_Instagram_Parse_Pro
 *
 * @since 5.0
 */
class SB_Instagram_Parse_Pro extends SB_Instagram_Parse
{
	/**
	 * Retrieves a specific part of the Instagram URL based on the provided header data and post.
	 *
	 * @param array  $header_data An array containing header data from the Instagram API response.
	 * @param object $post The post object containing Instagram post data.
	 * @return string The specific part of the Instagram URL.
	 */
	public static function get_instagram_url_part($header_data, $post)
	{
		if (isset($post['term']) && strpos($post['term'], '#') !== false) {
			$hashtag = str_replace('#', '', $post['term']);
		} elseif (isset($header_data['hashtags_recent'])) {
			$hashtag = $header_data['hashtags_recent']['hashtag'];
		} elseif (isset($header_data['hashtags_top'])) {
			$hashtag = $header_data['hashtags_top']['hashtag'];
		} else {
			$part = '';
			foreach ($header_data as $key => $data) {
				if (empty($part)) {
					$part = $key;
				}
			}
			return $part;
		}

		return 'explore/tags/' . $hashtag;
	}

	/**
	 * Retrieves a generic term from the provided header data.
	 *
	 * @param array $header_data The header data from which to extract the generic term.
	 * @return mixed The generic term extracted from the header data.
	 */
	public static function get_generic_term($header_data)
	{
		if (isset($header_data['term'])) {
			return $header_data['term'];
		} else {
			return '';
		}
	}

	/**
	 * Get the number of likes for a given Instagram post.
	 *
	 * @param array $post The Instagram post data.
	 * @return int The number of likes for the post.
	 */
	public static function get_likes_count($post)
	{
		if (!empty($post['likes'])) {
			return $post['likes']['count'];
		} elseif (!empty($post['like_count'])) {
			return $post['like_count'];
		}
		return 0;
	}

	/**
	 * Get the number of comments for a given Instagram post.
	 *
	 * @param array $post The Instagram post data.
	 * @return int The number of comments count for the post.
	 */
	public static function get_comments_count($post)
	{
		if (!empty($post['comments']['count'])) {
			return $post['comments']['count'];
		} elseif (!empty($post['comments_count'])) {
			return $post['comments_count'];
		}
		return 0;
	}

	/**
	 * Checks if the comment or like count data exists for a given Instagram post.
	 *
	 * @param array $post The Instagram post data.
	 * @return bool
	 */
	public static function comment_or_like_counts_data_exists($post)
	{
		if (isset($post['comments']['count']) || isset($post['comments_count'])) {
			return true;
		}
		return false;
	}

	/**
	 * If an avatar exists for this username (from a connected account)
	 * the url for it will be returned.
	 *
	 * @param array $post The Instagram post data.
	 * @param array $avatars Array key value pair of user name => avatar url.
	 *
	 * @return string
	 */
	public static function get_item_avatar($post, $avatars)
	{
		if (empty($avatars)) {
			return '';
		} else {
			$username = SB_Instagram_Parse_Pro::get_username($post);
			if (isset($avatars[$username])) {
				return $avatars[$username];
			}
		}
		return '';
	}

	/**
	 * Get the username for a given Instagram post.
	 *
	 * @param array $post The Instagram post data.
	 * @return string
	 */
	public static function get_username($post)
	{
		if (!empty($post['username'])) {
			return $post['username'];
		} elseif (!empty($post['user']['username'])) {
			return $post['user']['username'];
		} elseif (isset($post['data']['username'])) {
			return $post['data']['username'];
		}

		return '';
	}

	/**
	 * Video and carousel post types have additional data that is used
	 * in the lightbox
	 *
	 * @param array $post The Instagram post data.
	 * @return array Key value pair of data type => data used in lightbox.
	 */
	public static function get_lightbox_media_atts($post)
	{
		$return = array(
			'video' => '',
			'carousel' => ''
		);
		if (isset($post['videos'])) {
			$return['video'] = $post['videos']['standard_resolution']['url'];
		} elseif (isset($post['media_type']) && $post['media_type'] === 'VIDEO' && isset($post['media_url'])) {
			$return['video'] = $post['media_url'];
		} elseif (isset($post['media_type']) && $post['media_type'] === 'VIDEO') {
			$return['video'] = 'missing';
		} else {
			$return['video'] = '';
		}

		if (SB_Instagram_Parse_Pro::get_media_type($post) === 'carousel') {
			$carousel_object = SB_Instagram_Parse_Pro::get_carousel_object($post);
			$return['carousel'] = sbi_json_encode($carousel_object);

			if ($carousel_object['vid_first']) {
				$return['video'] = $carousel_object['data'][0]['media'];
			}
		}

		return $return;
	}

	/**
	 * Carousel post data is parsed and arranged for use in the lightbox
	 * here
	 *
	 * @param array $post The Instagram post data.
	 * @return array
	 */
	public static function get_carousel_object($post)
	{
		$car_obj = array(
			'data' => array(),
			'vid_first' => false
		);

		if (isset($post['carousel_media'])) {
			$i = 0;
			foreach ($post['carousel_media'] as $carousel_item) {
				if (isset($carousel_item['images'])) {
					$car_obj['data'][$i] = array(
						'type' => 'image',
						'media' => $carousel_item['images']['standard_resolution']['url']
					);
				} elseif (isset($carousel_item['videos'])) {
					$car_obj['data'][$i] = array(
						'type' => 'video',
						'media' => $carousel_item['videos']['standard_resolution']['url']
					);

					if ($i === 0) {
						$car_obj['vid_first'] = true;
					}
				}

				$i++;
			}
		} elseif (isset($post['children'])) {
			$i = 0;
			foreach ($post['children']['data'] as $carousel_item) {
				if ($carousel_item['media_type'] === 'IMAGE') {
					if (isset($carousel_item['media_url'])) {
						$car_obj['data'][$i] = array(
							'type' => 'image',
							'media' => $carousel_item['media_url']
						);
					} else {
						// Fetch via parent class helper with inherited context
						$media = self::fetch_carousel_item_media($carousel_item, $post);

						$car_obj['data'][$i] = array(
							'type' => 'image',
							'media' => $media
						);
					}
				} elseif ($carousel_item['media_type'] === 'VIDEO') {
					if (isset($carousel_item['media_url'])) {
						$car_obj['data'][$i] = array(
							'type' => 'video',
							'media' => $carousel_item['media_url']
						);

						if ($i === 0) {
							$car_obj['vid_first'] = true;
						}
					} else {
						// Fetch via parent class helper with inherited context
						$media = self::fetch_carousel_item_media($carousel_item, $post);

						$car_obj['data'][$i] = array(
							'type' => 'image',
							'media' => $media
						);
					}
				}

				$i++;
			}
		}

		return $car_obj;
	}

	/**
	 * Will only return something if using the old API
	 *
	 * @param array $post The Instagram post data.
	 * @return array Data used by the hover element for locations.
	 */
	public static function get_location_info($post)
	{
		$return = array();
		if (isset($post['location'])) {
			$name = !empty($post['location']) && !empty($post['location']['name']) ? $post['location']['name'] : '';
			$return = array(
				'name' => $name,
				'id' => '',
				'longitude' => '',
				'lattitude' => ''
			);
			if (isset($post['location']['id'])) {
				$return['id'] = $post['location']['id'];
			}
			if (isset($post['location']['longitude'])) {
				$return['longitude'] = $post['location']['longitude'];
			}
			if (isset($post['location']['lattitude'])) {
				$return['lattitude'] = $post['location']['lattitude'];
			}
		}

		return $return;
	}

	/**
	 * Only available for the old API. Returns list of hashtags
	 * used in the feed.
	 *
	 * @param array $post The Instagram post data.
	 * @return array|bool
	 */
	public static function get_tags($post)
	{
		if (isset($post['tags'])) {
			return $post['tags'];
		}

		return false;
	}

	/**
	 * Not directly parsed from the API response but story data
	 * is always included as part of header data in the feed so
	 * this function will return it if it was set along with header
	 * data
	 *
	 * @param array $header_data The Instagram header data.
	 * @return mixed
	 */
	public static function get_story_data($header_data)
	{
		if (isset($header_data['stories']) && isset($header_data['stories'][0])) {
			return $header_data['stories'];
		}
		return '';
	}

	/**
	 * Number of posts made by account
	 *
	 * @param array $header_data The Instagram header data.
	 * @return int
	 */
	public static function get_post_count($header_data)
	{
		if (isset($header_data['data']['counts'])) {
			return $header_data['data']['counts']['media'];
		} elseif (isset($header_data['counts'])) {
			return $header_data['counts']['media'];
		} elseif (isset($header_data['media_count'])) {
			return $header_data['media_count'];
		}
		return 0;
	}

	/**
	 * Number of followers for account
	 *
	 * @param array $header_data The Instagram header data.
	 * @return int
	 */
	public static function get_follower_count($header_data)
	{
		if (isset($header_data['data']['counts'])) {
			return $header_data['data']['counts']['followed_by'];
		} elseif (isset($header_data['counts'])) {
			return $header_data['counts']['followed_by'];
		} elseif (isset($header_data['followers_count'])) {
			return $header_data['followers_count'];
		}
		return '';
	}
}
