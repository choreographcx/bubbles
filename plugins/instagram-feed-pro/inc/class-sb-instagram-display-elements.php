<?php

use InstagramFeed\Helpers\Util;

if (!defined('ABSPATH')) {
	die('-1');
}

/**
 * Class SB_Instagram_Display_Elements
 *
 * Used to make certain parts of the html in the feed templates
 * abstract.
 *
 * @since 2.0/5.0
 */
class SB_Instagram_Display_Elements
{
	/**
	 * Images are hidden initially with the new/transition classes
	 * except if the js image loading is disabled using the plugin
	 * settings
	 *
	 * @param array      $settings Settings for the feed.
	 * @param array|bool $post The post data.
	 *
	 * @return string
	 *
	 * @since 2.0/5.0
	 */
	public static function get_item_classes($settings, $post = false)
	{
		$classes = '';
		$customizer = sbi_doing_customizer($settings);
		if (!$customizer) {
			if (!$settings['disable_js_image_loading']) {
				$classes .= ' sbi_new sbi_transition';
			} else {
				$classes .= ' sbi_new sbi_no_js sbi_no_resraise sbi_js_load_disabled';
			}
		} else {
			$classes .= ' sbi_new ';
		}

		if ($post && SB_Instagram_Parse::get_media_product_type($post) === 'igtv') {
			$classes .= ' sbi_igtv';
		}

		return $classes;
	}

	/**
	 * Gets inner wrapper styles for the post item.
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The styles for the inner wrapper.
	 */
	public static function get_item_styles_for_post_style($settings)
	{
		$styles = '';

		if (sbi_doing_customizer($settings)) {
			return $styles;
		}

		if (empty($settings['poststyle']) || 'boxed' !== $settings['poststyle']) {
			return $styles;
		}

		if (!empty($settings['postbgcolor'])) {
			$styles .= 'background-color: ' . esc_attr($settings['postbgcolor']) . '; ';
		}

		if (!empty($settings['postcorners'])) {
			$styles .= ' border-radius: ' . esc_attr($settings['postcorners']) . 'px; ';
		}

		if (!empty($settings['boxshadow']) && InstagramFeed\Builder\SBI_Feed_Saver_Manager::cast_boolean($settings['boxshadow'])) {
			$styles .= ' box-shadow: 0 4px 5px 0 rgba(0, 0, 0, 0.05);';
		}

		return sprintf('style="%s"', $styles);
	}

	/**
	 * Overwritten in the Pro version.
	 *
	 * @param string $type Key of the kind of icon needed.
	 * @param string $icon_type Svg or font.
	 *
	 * @return string The complete html for the icon.
	 *
	 * @since 2.0/5.0
	 */
	public static function get_icon($type, $icon_type)
	{
		return self::get_basic_icons($type, $icon_type);
	}

	/**
	 * Returns the html for an icon based on the kind requested
	 *
	 * @param string $type Kind of icon needed (ex "video" is a play button).
	 * @param string $icon_type Svg or font.
	 *
	 * @return string The complete html for the icon.
	 *
	 * @since 2.0/5.0
	 */
	protected static function get_basic_icons($type, $icon_type)
	{
		$icons = [
			'carousel' => [
				'svg' => '<svg class="svg-inline--fa fa-clone fa-w-16 sbi_lightbox_carousel_icon" aria-hidden="true" aria-label="Clone" data-fa-proƒcessed="" data-prefix="far" data-icon="clone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z"></path>
                </svg>',
				'font' => '<i class="fa fa-clone sbi_carousel_icon" aria-hidden="true"></i>'
			],
			'video' => [
				'svg' => '<svg style="color: rgba(255,255,255,1)" class="svg-inline--fa fa-play fa-w-14 sbi_playbtn" aria-label="Play" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="play" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"></path></svg>',
				'font' => '<i class="fa fa-play sbi_playbtn" aria-hidden="true"></i>'
			],
			'instagram' => [
				'svg' => '<svg class="svg-inline--fa fa-instagram fa-w-14" aria-hidden="true" data-fa-processed="" aria-label="Instagram" data-prefix="fab" data-icon="instagram" role="img" viewBox="0 0 448 512">
                    <path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
                </svg>',
				'font' => '<i class="fa fab fa-instagram" aria-hidden="true"></i>'
			],
			'newlogo' => [
				'svg' => '<svg class="sbi_new_logo fa-instagram fa-w-14" aria-hidden="true" data-fa-processed="" aria-label="Instagram" data-prefix="fab" data-icon="instagram" role="img" viewBox="0 0 448 512">
                    <path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
                </svg>',
				'font' => '<i class="sbi_new_logo"></i>'
			]
		];

		if (isset($icons[$type][$icon_type])) {
			return $icons[$type][$icon_type];
		}

		return '';
	}

	/**
	 * Images are normally styles with the imgLiquid plugin
	 * with JavaScript. If this is disabled, the plugin will
	 * attempt to square all images using CSS.
	 *
	 * @param array $post Data for an individual post.
	 * @param array $settings Settings for the feed.
	 * @param array $resized_images Resized images for the feed.
	 *
	 * @return string The style attribute for the image.
	 *
	 * @since 2.0/5.0
	 * @since 2.1.1/5.2.1 added support for resized images
	 */
	public static function get_sbi_photo_style_element($post, $settings, $resized_images = array())
	{
		if (!$settings['disable_js_image_loading']) {
			return '';
		} else {
			$full_res_image = self::get_optimum_media_url($post, $settings, $resized_images);

			/*
			 * By setting the height to "0" the bottom padding can be used
			 * as a percent to square the images. Since it needs to be a percent
			 * this guesses what the percent would be based on static padding.
			 */
			if ($settings['imagepaddingunit'] === '%') {
				$padding_bottom = 100 - ($settings['imagepadding'] * 2) . '%';
			} else {
				$padding_percent = $settings['imagepadding'] > 0 ? 100 - ($settings['cols'] / 2 * $settings['imagepadding'] / 5) : 100;
				$padding_bottom = $padding_percent . '%';
			}
			return ' style="background-image: url(&quot;' . esc_url($full_res_image) . '&quot;); background-size: cover; background-position: center center; background-repeat: no-repeat; opacity: 1;height: 0;padding-bottom: ' . esc_attr($padding_bottom) . ';"';
		}
	}

	/**
	 * Returns the best media url for an image based on settings.
	 * By default, a white placeholder image is loaded and replaced
	 * with the most optimal image size based on the actual dimensions
	 * of the image element in the feed.
	 *
	 * @param array $post Data for an individual post.
	 * @param array $settings Settings for the feed.
	 * @param array $resized_images Existing resized images to use in the source.
	 *
	 * @return string The url for the image.
	 *
	 * @since 2.0/5.0
	 */
	public static function get_optimum_media_url($post, $settings, $resized_images = array())
	{
		$media_url = '';
		$optimum_res = $settings['imageres'];
		$account_type = isset($post['images']) ? 'personal' : 'business';

		// only use the placeholder if it will be replaced using JS.
		if (!$settings['disable_js_image_loading']) {
			return trailingslashit(SBI_PLUGIN_URL) . 'img/placeholder.png';
		}

		if ($settings['imageres'] === 'auto') {
			$optimum_res = 'full';
			$settings['imageres'] = 'full';
		} elseif (!empty($resized_images)) {
			$media_url = self::getResizedImageUrl($post, $settings, $resized_images);
			if ($media_url) {
				return $media_url;
			}
		}
		if ($account_type === 'personal') {
			$media_url = self::getPersonalAccountMediaUrl($post, $optimum_res);
		} else {
			$media_url = self::getBusinessAccountMediaUrl($post, $settings, $resized_images, $optimum_res);
		}

		return $media_url ?: trailingslashit(SBI_PLUGIN_URL) . 'img/thumb-placeholder.png';
	}

	/**
	 * Retrieves the URL of a resized image based on the provided post, settings, and resized images.
	 *
	 * @param array $post The post data containing image information.
	 * @param array $settings The settings array that may influence the resizing process.
	 * @param array $resizedImages An array of resized images to choose from.
	 *
	 * @return string The URL of the resized image.
	 */
	private static function getResizedImageUrl($post, $settings, $resizedImages)
	{
		$resolution = $settings['imageres'];
		$postId = SB_Instagram_Parse::get_post_id($post);

		if (
			isset($resizedImages[$postId])
			&& !in_array($resizedImages[$postId]['id'], ['error', 'pending', 'video'])
		) {
			$suffix = self::getImageSuffix($resizedImages[$postId]['sizes'], $resolution);

			if ($suffix) {
				$extension = $resizedImages[$postId]['extension'] ?? '.jpg';
				return sbi_get_resized_uploads_url() . $resizedImages[$postId]['id'] . $suffix . $extension;
			}
		}

		return '';
	}

	/**
	 * Get the image suffix based on the provided sizes and resolution.
	 *
	 * @param array  $sizes An array of available image sizes.
	 * @param string $resolution The desired image resolution.
	 * @return string The image suffix corresponding to the given resolution.
	 */
	private static function getImageSuffix($sizes, $resolution)
	{
		$suffix = '';
		switch ($resolution) {
			case 'thumb':
			case 'medium':
				$suffix = isset($sizes['low']) ? 'low' : (isset($sizes['full']) ? 'full' : '');
				break;
			case 'full':
				$suffix = isset($sizes['full']) ? 'full' : (isset($sizes['low']) ? 'low' : '');
				break;
			case 'lightbox':
				$suffix = isset($sizes['full']) ? 'full' : '';
				break;
			default:
				$suffix = '';
				break;
		}
		return $suffix;
	}

	/**
	 * Retrieves the media URL for a personal Instagram account.
	 *
	 * @param array  $post The Instagram post data.
	 * @param string $optimumRes The desired resolution for the media URL.
	 * @return string The media URL for the specified post and resolution.
	 */
	private static function getPersonalAccountMediaUrl($post, $optimumRes)
	{
		switch ($optimumRes) {
			case 'thumb':
				return $post['images']['thumbnail']['url'];
			case 'medium':
				return $post['images']['low_resolution']['url'];
			default:
				return $post['images']['standard_resolution']['url'];
		}
	}

	/**
	 * Retrieves the media URL for a business account post.
	 *
	 * @param array  $post The Instagram post data.
	 * @param array  $settings The settings for the Instagram feed.
	 * @param array  $resizedImages The resized images data.
	 * @param string $optimumRes The optimum resolution for the media.
	 * @return string The URL of the media.
	 */
	private static function getBusinessAccountMediaUrl($post, $settings, $resizedImages, $optimumRes)
	{
		$postId = SB_Instagram_Parse::get_post_id($post);

		if (
			$optimumRes === 'full' && isset($resizedImages[$postId]['id'])
			&& !in_array($resizedImages[$postId]['id'], ['error', 'pending', 'video'])
		) {
			$extension = isset($resizedImages[$postId]['extension']) ? $resizedImages[$postId]['extension'] : '.jpg';
			return sbi_get_resized_uploads_url() . $resizedImages[$postId]['id'] . 'full' . $extension;
		}

		if (SB_Instagram_GDPR_Integrations::doing_gdpr($settings)) {
			return trailingslashit(SBI_PLUGIN_URL) . 'img/thumb-placeholder.png';
		}

		$mediaType = $post['media_type'];
		if ($mediaType === 'CAROUSEL_ALBUM' || $mediaType === 'VIDEO' || $mediaType === 'OEMBED') {
			return self::getCarouselOrVideoMediaUrl($post, $mediaType);
		}

		return $post['media_url'] ?? '';
	}

	/**
	 * Retrieves the URL for carousel or video media from a given post.
	 *
	 * @param array  $post The post data containing media information.
	 * @param string $mediaType The type of media to retrieve the URL for ('carousel' or 'video').
	 *
	 * @return string The URL of the specified media type.
	 */
	private static function getCarouselOrVideoMediaUrl($post, $mediaType)
	{
		if (isset($post['thumbnail_url'])) {
			return $post['thumbnail_url'];
		}

		if ($mediaType === 'CAROUSEL_ALBUM' && isset($post['media_url'])) {
			return $post['media_url'];
		}

		if (isset($post['children'])) {
			foreach ($post['children']['data'] as $carousel_item) {
				if ($carousel_item['media_type'] === 'IMAGE' && isset($carousel_item['media_url'])) {
					return $carousel_item['media_url'];
				}

				if ($carousel_item['media_type'] === 'VIDEO' && isset($carousel_item['thumbnail_url'])) {
					return $carousel_item['thumbnail_url'];
				}
			}
		}

		// Use parse class helper method (handles context passing automatically)
		return SB_Instagram_Parse::fetch_single_media($post);
	}

	/**
	 * Creates a style attribute that contains all the styles for
	 * the main feed div.
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The style attribute for the feed.
	 *
	 * @since 6.0
	 */
	public static function get_feed_style($settings)
	{
		$styles = [];

		if (!empty($settings['imagepadding'])) {
			$styles[] = 'padding-bottom: ' . ((int)$settings['imagepadding'] * 2) . esc_attr($settings['imagepaddingunit']) . ';';
		}
		if (!empty($settings['background'])) {
			$styles[] = 'background-color: rgb(' . esc_attr(sbi_hextorgb($settings['background'])) . ');';
		}
		if (!empty($settings['width'])) {
			$styles[] = 'width: ' . (int)$settings['width'] . esc_attr($settings['widthunit']) . ';';
		}
		if (!empty($settings['height'])) {
			$styles[] = 'height: ' . (int)$settings['height'] . esc_attr($settings['heightunit']) . ';';
		}

		return !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';
	}

	/**
	 * Creates a style attribute for the sbi_images div
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The style attribute for the sbi_images div.
	 *
	 * @since 6.0
	 */
	public static function get_sbi_images_style($settings)
	{
		if (!empty($settings['imagepadding'])) {
			if (!is_admin() && Util::sbi_legacy_css_enabled()) {
				return ' style="padding: ' . (int)$settings['imagepadding'] . esc_attr($settings['imagepaddingunit']) . ';"';
			} else {
				return ' style="gap: ' . (int)$settings['imagepadding'] * 2 . esc_attr($settings['imagepaddingunit']) . ';"';
			}
		}
		return '';
	}

	/**
	 * Creates a style attribute for the header. Can be used in
	 * several places based on the header style
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The style attribute for the header.
	 *
	 * @since 6.0
	 */
	public static function get_header_text_color_styles($settings)
	{
		if (!empty($settings['headercolor'])) {
			return ' style="color: rgb(' . esc_attr(sbi_hextorgb($settings['headercolor'])) . ');"';
		}
		return '';
	}

	/**
	 * Creates a style attribute for the follow button. Can be in
	 * the feed footer or in a boxed header.
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The style attribute for the follow button.
	 *
	 * @since 6.0
	 */
	public static function get_follow_styles($settings)
	{
		$styles = '';

		if (!self::doing_custom_palettes_for_button($settings) && (!empty($settings['followcolor']) || !empty($settings['followtextcolor']))) {
			$styles = ' style="';
			if (!empty($settings['followcolor'])) {
				$styles .= 'background: rgb(' . esc_attr(sbi_hextorgb($settings['followcolor'])) . ');';
			}
			if (!empty($settings['followtextcolor'])) {
				$styles .= 'color: rgb(' . esc_attr(sbi_hextorgb($settings['followtextcolor'])) . ');';
			}
			$styles .= '"';
		}
		return $styles;
	}

	/**
	 * Determines if custom palettes are being used for a button.
	 *
	 * @param array $settings The settings array containing color palette information.
	 * @return bool True if custom palettes are being used for the button, false otherwise.
	 */
	public static function doing_custom_palettes_for_button($settings)
	{
		if ((empty($settings['colorpalette']) || $settings['colorpalette'] === 'inherit')) {
			return false;
		}
		if ($settings['colorpalette'] === 'custom' && !empty($settings['custombuttoncolor2'])) {
			return true;
		}

		return false;
	}

	/**
	 * Creates a style attribute for styling the load more button.
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The style attribute for the load more button.
	 *
	 * @since 6.0
	 */
	public static function get_load_button_styles($settings)
	{
		$styles = '';
		if (!empty($settings['buttoncolor']) || !empty($settings['buttontextcolor'])) {
			$styles = ' style="';
			if (!empty($settings['buttoncolor'])) {
				$styles .= 'background: rgb(' . esc_attr(sbi_hextorgb($settings['buttoncolor'])) . ');';
			}
			if (!empty($settings['buttontextcolor'])) {
				$styles .= 'color: rgb(' . esc_attr(sbi_hextorgb($settings['buttontextcolor'])) . ');';
			}
			$styles .= '"';
		}
		if (!empty($settings['buttonhovercolor'])) {
			$styles .= ' data-button-hover="' . esc_attr($settings['buttonhovercolor']) . '"';
		}
		return $styles;
	}

	/**
	 * Returns the list of CSS classes
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $additional_classes Additional classes to add to the feed container.
	 * @return string The list of CSS classes.
	 *
	 * @since 6.0
	 */
	public static function get_feed_container_css_classes($settings, $additional_classes)
	{
		$customizer = sbi_doing_customizer($settings);
		$mobilecols_class = SB_Instagram_Display_Elements_Pro::get_mobilecols_class($settings);
		$tabletcols_class = SB_Instagram_Display_Elements_Pro::get_tabletcols_class($settings);
		$cols_setting = ($customizer) ? ' $parent.getColsPreviewScreen() ' : $settings['cols'];
		$additional_customizer_classes = ($customizer) ? ' $parent.getAdditionalCustomizerClasses() ' : '';
		$palette_class = self::get_palette_class($settings);
		$feedstyle_class = !empty($settings['poststyle']) ? ' sbi-style-' . $settings['poststyle'] : '';
		$feedtheme_class = '';
		if (!$customizer) {
			$feedtheme = isset($settings['feedtheme']) ? $settings['feedtheme'] : 'default_theme';
			$feedtheme_class = ' sbi-theme sbi-' . $feedtheme;
		}

		if ($customizer) {
			return ' :class="\'sbi \' + ' . esc_attr($mobilecols_class) . ' + \' \' + ' . esc_attr($tabletcols_class) . ' + \' sbi_col_\' + ' . esc_attr($cols_setting) . ' + \' \' + ' . esc_attr($palette_class) . ' + \' \' + ' . esc_attr($additional_customizer_classes) . '" ';
		} else {
			$classes = 'sbi' . esc_attr($mobilecols_class) . esc_attr($tabletcols_class) . ' sbi_col_' . esc_attr($cols_setting) . esc_attr($additional_classes) . esc_attr($palette_class) . esc_attr($feedtheme_class) . esc_attr($feedstyle_class);
			$classes = ' class="' . $classes . '"';
		}
		return $classes;
	}

	/**
	 * Layout for mobile feeds altered with the class added here based on settings.
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The class for the mobile feed.
	 *
	 * @since 5.0
	 */
	public static function get_mobilecols_class($settings)
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return ' $parent.getMobileColsClass() ';
		} else {
			$disable_mobile = $settings['disablemobile'];
			($disable_mobile === 'on' || $disable_mobile === 'true' || $disable_mobile === true) ? $disable_mobile = true : $disable_mobile = false;
			if ($settings['disablemobile'] === 'false') {
				$disable_mobile = '';
			}

			if ($disable_mobile !== ' sbi_disable_mobile' && $settings['colsmobile'] !== 'same') {
				$colsmobile = (int)($settings['colsmobile']) > 0 ? (int)$settings['colsmobile'] : 'auto';
				return ' sbi_mob_col_' . $colsmobile;
			} else {
				$colsmobile = (int)($settings['cols']) > 0 ? (int)$settings['cols'] : 4;
				return ' sbi_disable_mobile sbi_mob_col_' . $colsmobile;
			}
		}
	}

	/**
	 * Layout for tablet feeds altered with the class added here based on settings.
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The class for the tablet feed.
	 *
	 * @since 6.0
	 */
	public static function get_tabletcols_class($settings)
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return ' $parent.getTabletColsClass() ';
		} else {
			$colstablet = (int)($settings['colstablet']) > 0 ? (int)$settings['colstablet'] : 3;

			return ' sbi_tab_col_' . $colstablet;
		}
	}

	/**
	 * Palette class
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $context Context for the palette.
	 *
	 * @return string The class for the palette.
	 *
	 * @since 6.0
	 */
	public static function get_palette_class($settings, $context = '')
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return ' $parent.getPaletteClass() ';
		} else {
			$feed_id_addition = !empty($settings['colorpalette']) && $settings['colorpalette'] === 'custom' ? '_' . $settings['feed'] : '';
			$palette_class = !empty($settings['colorpalette']) && $settings['colorpalette'] !== 'inherit' ? ' sbi' . $context . '_palette_' . $settings['colorpalette'] . $feed_id_addition : '';
			return esc_attr($palette_class);
		}
	}

	/**
	 * Palette type
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The type of palette.
	 *
	 * @since 6.0
	 */
	public static function palette_type($settings)
	{
		return !empty($settings['colorpalette']) ? esc_attr($settings['colorpalette']) : 'inherit';
	}

	/**
	 * Returns the attribute for the feed container data attributes.
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The data attributes for the feed container.
	 *
	 * @since 6.0
	 */
	public static function get_feed_container_data_attributes($settings)
	{
		$customizer = sbi_doing_customizer($settings);

		$atts = SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-res',
				'vue_content' => '$parent.customizerFeedData.settings.imageres',
				'php_content' => $settings['imageres'],
			)
		);

		$atts .= SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-cols',
				'vue_content' => '$parent.getColsPreviewScreen()',
				'php_content' => $settings['cols'],
			)
		);

		$atts .= SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-colsmobile',
				'vue_content' => '$parent.customizerFeedData.settings.colsmobile',
				'php_content' => $settings['colsmobile'],
			)
		);

		$atts .= SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-colstablet',
				'vue_content' => '$parent.customizerFeedData.settings.colstablet',
				'php_content' => $settings['colstablet'],
			)
		);

		$atts .= SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-num',
				'vue_content' => '$parent.getModerationShoppableMode ? 20 : $parent.customizerFeedData.settings.num',
				'php_content' => $settings['num'],
			)
		);

		$atts .= SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-nummobile',
				'vue_content' => '$parent.customizerFeedData.settings.nummobile',
				'php_content' => $settings['nummobile'],
			)
		);

		$atts .= SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-header-size',
				'vue_content' => '$parent.customizerFeedData.settings.headerstyle == \'text\' ? $parent.customizerFeedData.settings.headertextsize : $parent.customizerFeedData.settings.headersize',
				'php_content' => $settings['headerstyle'] == 'text' ? $settings['headertextsize'] : $settings['headersize'],
			)
		);

		$atts .= SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-item-padding',
				'vue_content' => '$parent.customizerFeedData.settings.imagepadding',
				'php_content' => $settings['imagepadding'],
			)
		);

		return $atts;
	}

	/**
	 * Print Element HTML Attribute
	 *
	 * @param bool  $customizer Whether the feed is being displayed in the customizer.
	 * @param array $args Array of arguments for the element.
	 *
	 * @return string The HTML attribute for the element.
	 *
	 * @since 6.0
	 */
	public static function print_element_attribute($customizer, $args)
	{
		$print_element = ' ' . sanitize_key($args['attr']) . '="' . esc_attr($args['php_content']) . '"';
		if ($customizer) {
			$print_element = ' :' . sanitize_key($args['attr']) . '="' . esc_attr($args['vue_content']) . '"';
		}
		return $print_element;
	}

	/**
	 * Global header classes
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $avatar Avatar for the feed.
	 * @param string $type Type of header.
	 *
	 * @return string The class for the header.
	 *
	 * @since 5.0
	 */
	public static function get_header_class($settings, $avatar, $type = 'normal')
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return ' :class="$parent.getHeaderClass(\'' . $type . '\')" ';
		} else {
			$size_class = self::get_header_size_class($settings);
			$avatar_class = $avatar !== '' ? '' : ' sbi_no_avatar';
			$palette_class = self::get_palette_class($settings, '_header');
			$outside_class = $settings['headeroutside'] ? ' sbi_header_outside' : '';
			return ' class="sb_instagram_header ' . esc_attr($size_class) . esc_attr($avatar_class) . esc_attr($outside_class) . esc_attr($palette_class) . '" ';
		}
	}

	/**
	 * Header icon and text size is styled using the class added here.
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The class for the header size.
	 *
	 * @since 2.0.1/5.0
	 */
	public static function get_header_size_class($settings)
	{
		return in_array(strtolower($settings['headersize']), array('medium', 'large')) ? ' sbi_' . strtolower($settings['headersize']) : '';
	}

	/**
	 * Header Link
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $username Username for the feed.
	 *
	 * @return string The link for the header.
	 *
	 * @since 6.0
	 */
	public static function get_header_link($settings, $username)
	{
		if (sbi_doing_customizer($settings)) {
			return ' :href="\'https://www.instagram.com/\' + $parent.getHeaderUserName() "';
		} else {
			return ' href="' . esc_url('https://www.instagram.com/' . $username . '/') . '"';
		}
	}

	/**
	 * Header Link Title
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $username Username for the feed.
	 *
	 * @return string The title for the header link.
	 *
	 * @since 6.0
	 */
	public static function get_header_link_title($settings, $username)
	{
		return SB_Instagram_Display_Elements_Pro::print_element_attribute(
			sbi_doing_customizer($settings),
			array(
				'attr' => 'title',
				'vue_content' => '$parent.getHeaderUserNameTitle()',
				'php_content' => '@' . esc_attr($username),
			)
		);
	}

	/**
	 * Follow button attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The attribute for the follow button.
	 *
	 * @since 6.0
	 */
	public static function get_follow_attribute($settings)
	{
		return SB_Instagram_Display_Elements_Pro::should_print_element_vue(sbi_doing_customizer($settings), '$parent.customizerFeedData.settings.followtext');
	}

	/**
	 * Should Print HTML
	 *
	 * @param bool   $customizer Whether the feed is being displayed in the customizer.
	 * @param string $content Content to be printed.
	 *
	 * @return string The HTML attribute for the element.
	 *
	 * @since 6.0
	 */
	public static function should_print_element_vue($customizer, $content)
	{
		if ($customizer) {
			return ' v-html="' . esc_html($content) . '" ';
		}
		return '';
	}

	/**
	 * Load more button attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The attribute for the load more button.
	 *
	 * @since 6.0
	 */
	public static function get_button_attribute($settings)
	{
		return SB_Instagram_Display_Elements_Pro::should_print_element_vue(sbi_doing_customizer($settings), '$parent.customizerFeedData.settings.buttontext');
	}

	/**
	 * Photo wrap prepended HTML
	 *
	 * @param array $post Data for an individual post.
	 * @param array $settings Settings for the feed.
	 *
	 * @return string The HTML for the photo wrap.
	 *
	 * @since 6.0
	 */
	public static function get_photo_wrap_content($post, $settings)
	{
		return '';
	}

	/**
	 * Header data attributes
	 *
	 * @param array $settings Settings for the feed.
	 * @param array $header_data Data for the header.
	 *
	 * @return string The data attributes for the header.
	 *
	 * @since 6.0
	 */
	public static function get_header_data_attributesss($settings, $header_data)
	{
		$atts = '';

		if (sbi_doing_customizer($settings)) {
			if (isset($settings['generic_header'])) {
				return self::vue_check_header_enabled($settings, 'header-generic', $settings['vue_args']);
			}
			$header_vue = $settings['vue_args'];
			if ($settings['headerstyle'] !== 'boxed') {
				$header_vue['condition'] = $settings['vue_args']['condition'] . ' && $parent.customizerFeedData.settings.headerstyle !== \'boxed\'';
			} else {
				$header_vue['condition'] = $settings['vue_args']['condition'] . ' && $parent.customizerFeedData.settings.headerstyle === \'boxed\'';
			}

			if ($settings['headerstyle'] !== 'text') {
				$header_vue['condition'] = $settings['vue_args']['condition'] . ' && $parent.customizerFeedData.settings.headerstyle !== \'text\'';
			}

			$header_enabeld_vue = self::vue_check_header_enabled($settings, 'header', $header_vue);
			$atts .= ' ' . $header_enabeld_vue;
		}
		$avatar = SB_Instagram_Parse_Pro::get_avatar($header_data, $settings);

		$story_data_att = SB_Instagram_Display_Elements_Pro::get_story_attributes(sbi_doing_customizer($settings), $settings, $header_data, $avatar);

		$atts .= ' ' . $story_data_att;
		return wp_kses_post($atts);
	}

	/**
	 * Returns & Checks if Header is Enabled
	 * Shows & Hides
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $header_type Type of header.
	 * @param array  $vue_args Vue arguments.
	 *
	 * @return string The HTML for the header.
	 *
	 * @since 6.0
	 */
	public static function vue_check_header_enabled($settings, $header_type, $vue_args)
	{
		$customizer = sbi_doing_customizer($settings);

		if ($customizer) {
			$vue_args = !empty($vue_args['condition']) ? $vue_args['condition'] : false;
			$generic = isset($settings['generic_header']);
			$generic_check = ($header_type === 'header-generic') ? ' && ' . $generic . ' ==  true' : '';

			$result_vue = '$parent.valueIsEnabled($parent.customizerFeedData.settings.showheader)' . esc_attr($generic_check) . ' ' . esc_attr($vue_args);

			return ' v-if=" ' . esc_attr($result_vue) . '" ';
		}

		return '';
	}

	/**
	 * Header data attributes
	 *
	 * @param string $header_style Style of the header.
	 * @param array  $settings Settings for the feed.
	 * @param array  $header_data Data for the header.
	 *
	 * @return string The data attributes for the header.
	 *
	 * @since 6.1
	 */
	public static function get_header_data_attributes($header_style, $settings, $header_data)
	{
		$atts = '';
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			switch ($header_style) {
				case 'standard-centered':
					$atts .= SB_Instagram_Display_Elements_Pro::create_condition_show_vue($customizer, '$parent.valueIsEnabled($parent.customizerFeedData.settings.showheader) && ($parent.customizerFeedData.settings.headerstyle === \'standard\' || $parent.customizerFeedData.settings.headerstyle === \'centered\') && $parent.customizerFeedData.settings.type !== \'hashtag\' ' . $settings['vue_args']['condition']);
					break;
				case 'boxed':
					$atts .= SB_Instagram_Display_Elements_Pro::create_condition_show_vue($customizer, '$parent.valueIsEnabled($parent.customizerFeedData.settings.showheader) && $parent.customizerFeedData.settings.headerstyle === \'boxed\'  && $parent.customizerFeedData.settings.type !== \'hashtag\' ' . $settings['vue_args']['condition']);
					break;
				case 'text':
					$atts .= SB_Instagram_Display_Elements_Pro::create_condition_show_vue($customizer, '$parent.valueIsEnabled($parent.customizerFeedData.settings.showheader) && $parent.customizerFeedData.settings.headerstyle === \'text\' ' . $settings['vue_args']['condition']) . ' ' . SB_Instagram_Display_Elements_Pro::should_print_element_vue(sbi_doing_customizer($settings), '$parent.customizerFeedData.settings.headertext');
					break;
				case 'generic':
				default:
					$atts .= SB_Instagram_Display_Elements_Pro::create_condition_show_vue($customizer, '$parent.valueIsEnabled($parent.customizerFeedData.settings.showheader) && $parent.customizerFeedData.settings.type === \'hashtag\'&& $parent.customizerFeedData.settings.headerstyle !== \'text\' ' . $settings['vue_args']['condition']);
					break;
			}
		}
		$avatar = SB_Instagram_Parse_Pro::get_avatar($header_data, $settings);

		$story_data_att = SB_Instagram_Display_Elements_Pro::get_story_attributes(sbi_doing_customizer($settings), $settings, $header_data, $avatar);

		$atts .= ' ' . $story_data_att;
		return wp_kses_post($atts);
	}

	/**
	 * Header image data attributes
	 *
	 * @param array  $settings Settings for the feed.
	 * @param array  $header_data Data for the header.
	 * @param string $location Location of the header.
	 *
	 * @return string The data attributes for the header image.
	 *
	 * @since 6.0
	 */
	public static function get_header_img_data_attributes($settings, $header_data = array(), $location = 'default')
	{
		$instagram_cdn_avatar = SB_Instagram_Parse_Pro::get_avatar($header_data, $settings, false);
		$doing_customizer = sbi_doing_customizer($settings);
		$return = '';

		// For legacy templates support. To be removed later.
		if (!is_admin() && Util::sbi_legacy_css_enabled()) {
			if ($settings['headerstyle'] === 'boxed') {
				if (!empty($instagram_cdn_avatar)) {
					$return = self::print_element_attribute(
						$doing_customizer,
						array(
							'attr' => 'data-avatar-url',
							'vue_content' => '$parent.getHeaderAvatar()',
							'php_content' => $instagram_cdn_avatar,
						)
					);
				} else {
					$return = self::create_condition_vue($doing_customizer, '$parent.getHeaderAvatar() === false');
				}
			} elseif ($location !== 'centered') {
				if (!empty($instagram_cdn_avatar) || $doing_customizer) {
					$return = self::print_element_attribute(
						$doing_customizer,
						array(
								'attr' => 'data-avatar-url',
								'vue_content' => '$parent.getHeaderAvatar()',
								'php_content' => $instagram_cdn_avatar,
							)
					) .
						self::create_condition_vue($doing_customizer, ' $parent.customizerFeedData.settings.headerstyle !== \'centered\'');
				}
			} elseif (!empty($instagram_cdn_avatar) || $doing_customizer) {
				$return = self::print_element_attribute(
					$doing_customizer,
					array(
							'attr' => 'data-avatar-url',
							'vue_content' => '$parent.getHeaderAvatar()',
							'php_content' => $instagram_cdn_avatar,
						)
				) .
					self::create_condition_vue($doing_customizer, ' $parent.customizerFeedData.settings.headerstyle === \'centered\'');
			}
			if (empty($return)) {
				return $return;
			}

			return ' ' . $return;
		}

		if ($settings['headerstyle'] === 'boxed') {
			if (!empty($instagram_cdn_avatar) || $doing_customizer) {
				$return = self::print_element_attribute(
					$doing_customizer,
					array(
						'attr' => 'data-avatar-url',
						'vue_content' => '$parent.getHeaderAvatar()',
						'php_content' => $instagram_cdn_avatar,
					)
				);
			} else {
				$return = self::create_condition_vue($doing_customizer, '$parent.getHeaderAvatar() === false');
			}
		} elseif (!empty($instagram_cdn_avatar) || $doing_customizer) {
			$return = self::print_element_attribute(
				$doing_customizer,
				array(
					'attr' => 'data-avatar-url',
					'vue_content' => '$parent.getHeaderAvatar()',
					'php_content' => $instagram_cdn_avatar,
				)
			);
		}

		if (empty($return)) {
			return $return;
		}

		return ' ' . $return;
	}

	/**
	 * Should Print HTML
	 *
	 * @param bool   $customizer Whether the feed is being displayed in the customizer.
	 * @param string $condition Condition for the element.
	 *
	 * @return string The HTML attribute for the element.
	 *
	 * @since 6.0
	 */
	public static function create_condition_vue($customizer, $condition)
	{
		if ($customizer) {
			return ' v-if="' . esc_attr($condition) . '" ';
		}
		return '';
	}

	/**
	 * Header text classes
	 *
	 * @param array $header_data Data for the header.
	 * @param array $settings Settings for the feed.
	 *
	 * @return string The class for the header text.
	 *
	 * @since 6.0
	 */
	public static function get_header_text_class($header_data, $settings)
	{
		$bio = SB_Instagram_Parse_Pro::get_bio($header_data, $settings);
		$should_show_bio = $bio !== '' && SB_Instagram_Display_Elements_Pro::should_show_element('headerbio', $settings);
		$bio_class = !$should_show_bio ? ' sbi_no_bio' : '';
		$has_info = $should_show_bio || SB_Instagram_Display_Elements_Pro::should_show_element('headerfollowers', $settings);
		$info_class = !$has_info ? ' sbi_no_info' : '';

		return SB_Instagram_Display_Elements_Pro::print_element_attribute(
			sbi_doing_customizer($settings),
			array(
				'attr' => 'class',
				'vue_content' => '$parent.getTextHeaderClass()',
				'php_content' => 'sbi_header_text' . esc_attr($info_class) . esc_attr($bio_class),
			)
		);
	}

	/**
	 * Header bio info classes
	 *
	 * @param array $header_data Data for the header.
	 * @param array $settings Settings for the feed.
	 *
	 * @return string The class for the header bio info.
	 *
	 * @since 6.3
	 */
	public static function get_feedtheme_info_bio_class($header_data, $settings)
	{
		$bio = SB_Instagram_Parse_Pro::get_bio($header_data, $settings);
		$should_show_bio = $bio !== '' && SB_Instagram_Display_Elements_Pro::should_show_element('headerbio', $settings);
		$bio_class = !$should_show_bio ? ' sbi_no_bio' : '';
		$has_info = $should_show_bio || SB_Instagram_Display_Elements_Pro::should_show_element('headerfollowers', $settings);
		$info_class = !$has_info ? ' sbi_no_info' : '';

		return SB_Instagram_Display_Elements_Pro::print_element_attribute(
			sbi_doing_customizer($settings),
			array(
				'attr' => 'class',
				'vue_content' => '$parent.getHeaderBioInfoClass()',
				'php_content' => 'sbi_bio_info sbi_feedtheme_bio' . esc_attr($info_class) . esc_attr($bio_class),
			)
		);
	}

	/**
	 * Avatar header image element data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @param array $header_data Data for the header.
	 *
	 * @return string The data attributes for the header image.
	 *
	 * @since 6.0
	 */
	public static function get_avatar_element_data_attributes($settings, $header_data = array())
	{
		$avatar = SB_Instagram_Parse_Pro::get_avatar($header_data, $settings);
		$name = SB_Instagram_Parse_Pro::get_name($header_data);

		return ' ' . SB_Instagram_Display_Elements_Pro::print_element_attribute(
			sbi_doing_customizer($settings),
			array(
					'attr' => 'src',
					'vue_content' => '$parent.getHeaderAvatar()',
					'php_content' => $avatar,
				)
		) . SB_Instagram_Display_Elements_Pro::print_element_attribute(
			sbi_doing_customizer($settings),
			array(
					'attr' => 'alt',
					'vue_content' => '$parent.getHeaderName()',
					'php_content' => '', // Alt text for avatar is set to empty due to the same alt text being present in the enclosing a link for improved accessibility.
				)
		);
	}

	/**
	 * Post count in header data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The data attributes for the post count.
	 *
	 * @since 6.0
	 */
	public static function get_post_count_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::should_show_element_vue($settings, 'showfollowers') . SB_Instagram_Display_Elements_Pro::should_print_element_vue(sbi_doing_customizer($settings), ' $parent.svgIcons[\'headerPhoto\']+ \' \' + $parent.getHeaderMediaCount()');
	}

	/**
	 * Should Show Element
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $setting_name Name of the setting.
	 * @param bool   $custom_condition Custom condition for the element.
	 *
	 * @return string The HTML attribute for the element.
	 */
	public static function should_show_element_vue($settings, $setting_name, $custom_condition = false)
	{
		$customizer = sbi_doing_customizer($settings);
		$setting_name = !empty($setting_name) ? $setting_name : false;
		$custom_condition = $custom_condition ? $custom_condition : '';

		if ($customizer) {
			return ' v-if="$parent.valueIsEnabled($parent.customizerFeedData.settings.' . esc_attr($setting_name) . ')' . (esc_attr($custom_condition)) . '" ';
		}
		return '';
	}

	/**
	 * Follower count in header data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The data attributes for the follower count.
	 *
	 * @since 6.0
	 */
	public static function get_follower_count_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::should_show_header_followers($settings, 'showfollowers') . SB_Instagram_Display_Elements_Pro::should_print_element_vue(sbi_doing_customizer($settings), ' $parent.svgIcons[\'headerUser\'] + \' \' + $parent.getHeaderFollowersCount()');
	}

	/**
	 * Should Show Element
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $setting_name Name of the setting.
	 * @param bool   $custom_condition Custom condition for the element.
	 *
	 * @return string The HTML attribute for the element.
	 */
	public static function should_show_header_followers($settings, $setting_name, $custom_condition = false)
	{
		$customizer = sbi_doing_customizer($settings);
		$setting_name = !empty($setting_name) ? $setting_name : false;
		$custom_condition = $custom_condition ? $custom_condition : '';

		if ($customizer) {
			return ' v-if="$parent.checkBusinessAccount() && $parent.valueIsEnabled($parent.customizerFeedData.settings.' . esc_attr($setting_name) . ')' . (esc_attr($custom_condition)) . '" ';
		}
		return '';
	}

	/**
	 * Heading in header data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The data attributes for the heading.
	 *
	 * @since 6.0
	 */
	public static function get_header_heading_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::should_print_element_vue(sbi_doing_customizer($settings), '$parent.customizerFeedData.headerData.username');
	}

	/**
	 * Bio in header data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The data attributes for the bio.
	 *
	 * @since 6.0
	 */
	public static function get_bio_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::create_condition_vue(sbi_doing_customizer($settings), '$parent.checkNotEmpty( $parent.getHeaderBio() ) ? $parent.valueIsEnabled( $parent.customizerFeedData.settings[\'showbio\'] ) : false');
	}

	/**
	 * Hover username data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The data attributes for the hover username.
	 *
	 * @since 6.0
	 */
	public static function get_hoverusername_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::hoverdisplay_vue_condition('username');
	}

	/**
	 * Hover display vue condiition
	 *
	 * @param string $setting_name Name of the setting.
	 * @return string The condition for the hover display.
	 *
	 * @since 6.0
	 */
	public static function hoverdisplay_vue_condition($setting_name)
	{
		return SB_Instagram_Display_Elements_Pro::create_condition_vue(true, '$parent.customizerFeedData.settings.hoverdisplay.includes(\'' . $setting_name . '\')');
	}

	/**
	 * Caption data attribute
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $caption Caption for the post.
	 * @param int    $post_id ID of the post.
	 *
	 * @return string The data attributes for the caption.
	 *
	 * @since 6.0
	 */
	public static function get_caption_data_attributes($settings, $caption, $post_id)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}

		$caption = self::sanitize_caption($caption);

		return ' ' . SB_Instagram_Display_Elements_Pro::display_vue_condition('showcaption') . ' v-html="$parent.getPostCaption(\'' . htmlspecialchars($caption) . '\', ' . $post_id . ')"';
	}

	/**
	 * Some characters in captions are breaking the customizer.
	 *
	 * @param string $caption Caption to be sanitized.
	 * @return string Sanitized caption.
	 */
	public static function sanitize_caption($caption)
	{
		$caption = str_replace(array("'"), '`', $caption);
		$caption = str_replace('&amp;', '&', $caption);
		$caption = str_replace('&lt;', '<', $caption);
		$caption = str_replace('&gt;', '>', $caption);
		$caption = str_replace('&quot;', '"', $caption);
		$caption = str_replace('&#039;', '/', $caption);
		$caption = str_replace('&#92;', '\/', $caption);

		$caption = str_replace(array("\r", "\n"), '<br>', $caption);
		return str_replace('&lt;br /&gt;', '<br>', nl2br($caption));
	}

	/**
	 * Display vue condition
	 *
	 * @param string $setting_name Name of the setting.
	 * @param string $custom_condition Custom condition for the element.
	 *
	 * @return string The condition for the display.
	 *
	 * @since 6.0
	 */
	public static function display_vue_condition($setting_name, $custom_condition = '')
	{
		return SB_Instagram_Display_Elements_Pro::create_condition_vue(true, '$parent.valueIsEnabled( $parent.customizerFeedData.settings.' . $setting_name . ' ) ' . $custom_condition);
	}

	/**
	 * Hover caption data attribute
	 *
	 * @param array  $settings Settings for the feed.
	 * @param string $caption Caption for the post.
	 *
	 * @return string The data attributes for the hover caption.
	 *
	 * @since 6.0
	 */
	public static function get_hovercaption_data_attributes($settings, $caption)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}

		$caption = self::sanitize_caption($caption);

		return ' ' . SB_Instagram_Display_Elements_Pro::hoverdisplay_vue_condition('caption') . ' v-html="$parent.getPostHoverCaption(\'' . htmlspecialchars($caption) . '\')"';
	}

	/**
	 * Hover instagram data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The data attributes for the hover instagram.
	 *
	 * @since 6.0
	 */
	public static function get_hoverinstagram_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::hoverdisplay_vue_condition('instagram');
	}

	/**
	 * Hover Avatar Attributes
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The hover avatar attributes.
	 *
	 * @since 6.0
	 */
	public static function get_avatar_hover_data_attributes($settings)
	{
		return ' ' . self::create_condition_vue(sbi_doing_customizer($settings), '$parent.getHeaderAvatar() !== false');
	}

	/**
	 * HEader Avatar SVG Icon Attributes
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The header avatar SVG icon attributes.
	 *
	 * @since 6.0
	 */
	public static function get_avatar_svg_data_attributes($settings)
	{
		return ' ' . SB_Instagram_Display_Elements::create_condition_vue(sbi_doing_customizer($settings), '$parent.getHeaderAvatar() === false');
	}

	/**
	 * Hover date data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The hover date data attributes.
	 *
	 * @since 6.0
	 */
	public static function get_hoverdate_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::hoverdisplay_vue_condition('date');
	}

	/**
	 * Get the follow button hover color from the settings.
	 *
	 * @param array $settings The settings array containing the 'followhovercolor' key.
	 * @return string The sanitized follow button hover color or an empty string if not set or default.
	 */
	public static function get_follow_hover_color($settings)
	{
		if (!empty($settings['followhovercolor']) && $settings['followhovercolor'] !== '#359dff') {
			return esc_attr($settings['followhovercolor']);
		}
		return '';
	}

	/**
	 * Hover likes data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The hover likes data attributes.
	 *
	 * @since 6.0
	 */
	public static function get_hoverlikes_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::hoverdisplay_vue_condition('likes');
	}

	/**
	 * Get the hover color for the load button from the settings.
	 *
	 * @param array $settings The settings array containing the button hover color.
	 * @return string The sanitized button hover color or an empty string if not set or default.
	 */
	public static function get_load_button_hover_color($settings)
	{
		if (!empty($settings['buttonhovercolor']) && $settings['buttonhovercolor'] !== '#000') {
			return esc_attr($settings['buttonhovercolor']);
		}
		return '';
	}

	/**
	 * Hover meta data attribute
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The hover meta data attributes.
	 *
	 * @since 6.0
	 */
	public static function get_meta_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::display_vue_condition('showlikes');
	}

	/**
	 * Load button data attributes
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The load button data attributes.
	 *
	 * @since 6.0
	 */
	public static function get_button_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::display_vue_condition('showbutton');
	}

	/**
	 * Follow data attributes
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The follow data attributes.
	 *
	 * @since 6.0
	 */
	public static function get_follow_data_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return '';
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::display_vue_condition('showfollow', ' && $parent.customizerFeedData.settings.type !== \'hashtag\'');
	}

	/**
	 * Post image data attributes
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The post image data attributes.
	 */
	public static function get_post_image_style_attributes($settings)
	{
		if (!sbi_doing_customizer($settings)) {
			return !$settings['showcaption'] && !$settings['showlikes'] ? ' style="border-radius: inherit;"' : '';
		}
		return ' :style="[( ! $parent.customizerFeedData.settings.showcaption && ! $parent.customizerFeedData.settings.showlikes  ) && {\'border-radius\': \'inherit\'}]"';
	}

	/**
	 * Show header section
	 *
	 * @param string $section Section to show.
	 * @param array  $settings Settings for the feed.
	 *
	 * @return bool Whether to show the header section.
	 *
	 * @since 6.0
	 */
	public static function should_show_header_section($section, $settings)
	{
		if (sbi_doing_customizer($settings)) {
			return true;
		}

		if ($section === 'image-top') {
			return $settings['headerstyle'] === 'centered';
		} elseif ($section === 'image-bottom') {
			return $settings['headerstyle'] !== 'centered';
		}

		return true;
	}

	/**
	 * Retrieves the header text for the Instagram feed.
	 *
	 * @param array $settings An associative array of settings.
	 * @return string The sanitized header text.
	 */
	public static function get_header_text($settings)
	{
		if (!empty($settings['headertext'])) {
			return esc_attr($settings['headertext']);
		}

		return esc_attr__("We're On Instagram", 'instagram-feed');
	}

	/**
	 * Determines the theme condition for displaying elements.
	 *
	 * @param array  $settings The settings array to check if the customizer is active.
	 * @param string $condition The condition to be used in the `v-if` directive.
	 *
	 * @return string The `v-if` directive with the condition if the customizer is active, otherwise an empty string.
	 */
	public static function theme_condition($settings, $condition)
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return ' v-if="' . esc_attr($condition) . '" ';
		}
		return '';
	}

	/**
	 * Get Footer Attributes
	 *
	 * @param array $settings Settings for the feed.
	 * @return string The footer attributes.
	 *
	 * @since 6.0
	 */
	public static function get_footer_attributes($settings)
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return SB_Instagram_Display_Elements_Pro::create_condition_vue($customizer, '!$parent.getModerationShoppableMode');
		}
		return '';
	}
}
