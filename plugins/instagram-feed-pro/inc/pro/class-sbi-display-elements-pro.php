<?php

if (!defined('ABSPATH')) {
	die('-1');
}

/**
 * Class SB_Instagram_Display_Elements_Pro
 *
 * @since 5.0
 */
class SB_Instagram_Display_Elements_Pro extends SB_Instagram_Display_Elements
{
	/**
	 * The sbi_link element for each item has different styles applied if
	 * the lightbox is disabled.
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_sbi_link_classes($settings)
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return ' :class="\'sbi_link_customizer sbi_link\' + ($parent.valueIsEnabled( $parent.customizerFeedData.settings.disablelightbox ) || $parent.valueIsEnabled( $parent.customizerFeedData.settings.shoppablefeed ) ? \' sbi_disable_lightbox\' : \'\')" ';
		}
		$lightbox_class = (!empty($settings['disablelightbox']) && ($settings['disablelightbox'] === 'on' || $settings['disablelightbox'] === 'true' || $settings['disablelightbox'] === true)) ? ' sbi_disable_lightbox' : '';
		return ' class="sbi_link ' . $lightbox_class . '" ';
	}

	/**
	 * Custom background color for the hover element. Slightly opaque.
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_sbi_link_styles($settings)
	{
		if (!empty($settings['hovercolor']) && $settings['hovercolor'] !== '#000') {
			return ' style="background: rgba(' . esc_attr(sbi_hextorgb($settings['hovercolor'])) . ',0.85)"';
		}
		return '';
	}

	/**
	 * Text color for the hover element.
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_hover_styles($settings)
	{
		if (!empty($settings['hovertextcolor']) && $settings['hovertextcolor'] !== '#000') {
			return ' style="color: rgba(' . esc_attr(sbi_hextorgb($settings['hovertextcolor'])) . ',1)"';
		}
		return '';
	}

	/**
	 * Inline styles applied to the caption/like count/comment count information appearing
	 * underneath each post by default.
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_sbi_info_styles($settings)
	{
		$styles = '';
		if ((!empty($settings['captionsize']) && $settings['captionsize'] !== 'inherit') || !empty($settings['captioncolor'])) {
			$styles = ' style="';
			if (!empty($settings['captionsize']) && $settings['captionsize'] !== 'inherit') {
				$styles .= 'font-size: ' . esc_attr($settings['captionsize']) . 'px;';
			}
			if (!empty($settings['captioncolor'])) {
				$styles .= 'color: rgb(' . esc_attr(sbi_hextorgb($settings['captioncolor'])) . ');';
			}
			$styles .= '"';
		}
		return $styles;
	}

	/**
	 * Color of the likes heart icon and the comment voice box icon in the
	 * sbi_info area.
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_sbi_meta_color_styles($settings)
	{
		if (!empty($settings['likescolor'])) {
			return ' style="color: rgb(' . esc_attr(sbi_hextorgb($settings['likescolor'])) . ');"';
		}
		return '';
	}

	/**
	 * Size of the likes heart icon and the comment voice box icon in the
	 * sbi_info area.
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_sbi_meta_size_color_styles($settings)
	{
		$styles = '';
		if ((!empty($settings['likessize']) && $settings['likessize'] !== 'inherit') || !empty($settings['likescolor'])) {
			$styles = ' style="';
			if (!empty($settings['likessize']) && $settings['likessize'] !== 'inherit') {
				$styles .= 'font-size: ' . esc_attr($settings['likessize']) . 'px;';
			}
			if (!empty($settings['likescolor'])) {
				$styles .= 'color: rgb(' . esc_attr(sbi_hextorgb($settings['likescolor'])) . ');';
			}
			$styles .= '"';
		}

		return $styles;
	}

	/**
	 * Size of the likes heart icon, the comment voice box icon and hover textcolor
	 * in the sbi_info area.
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 6.3
	 */
	public static function get_sbi_meta_hover_styles($settings)
	{
		$styles = '';
		if ((!empty($settings['likessize']) && $settings['likessize'] !== 'inherit') || !empty($settings['hovertextcolor'])) {
			$styles = ' style="';
			if (!empty($settings['likessize']) && $settings['likessize'] !== 'inherit') {
				$styles .= 'font-size: ' . esc_attr($settings['likessize']) . 'px;';
			}
			if (!empty($settings['hovertextcolor'])) {
				$styles .= 'color: rgb(' . esc_attr(sbi_hextorgb($settings['hovertextcolor'])) . ');';
			}
			$styles .= '"';
		}
		return $styles;
	}

	/**
	 * Boxed style headers have more color options - primary color
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_boxed_header_styles($settings)
	{
		if (!empty($settings['headerprimarycolor'])) {
			return ' style="background: rgb(' . esc_attr(sbi_hextorgb($settings['headerprimarycolor'])) . ');"';
		}
		return '';
	}

	/**
	 * Boxed style headers have more color options - secondary color
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_header_bar_styles($settings)
	{
		if (!empty($settings['headersecondarycolor'])) {
			return ' style="background: rgb(' . esc_attr(sbi_hextorgb($settings['headersecondarycolor'])) . ');"';
		}
		return '';
	}

	/**
	 * For text, likes counts, post counts
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_header_info_styles($settings)
	{
		if (!empty($settings['headerprimarycolor'])) {
			return ' style="color: rgb(' . esc_attr(sbi_hextorgb($settings['headerprimarycolor'])) . ');"';
		}
		return '';
	}

	/**
	 * Global header classes
	 *
	 * @param array  $settings The settings array for the feed.
	 * @param string $avatar The avatar URL.
	 * @param string $type The type of header.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_header_class($settings, $avatar, $type = 'normal')
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return ' :class="$parent.getHeaderClass(\'' . $type . '\')" ';
		} else {
			$type_class = SB_Instagram_Display_Elements_Pro::get_feed_type_class($settings);
			$centered_class = $settings['headerstyle'] === 'centered' && $type === 'normal' ? ' sbi_centered' : '';
			$size_class = SB_Instagram_Display_Elements_Pro::get_header_size_class($settings);
			$avatar_class = $avatar !== '' ? '' : ' sbi_no_avatar';
			$boxed_class = $type === 'boxed' ? ' sbi_header_style_boxed' : '';
			$palette_class = SB_Instagram_Display_Elements::get_palette_class($settings, '_header');
			$outside_class = $settings['headeroutside'] ? ' sbi_header_outside' : '';
			$feedtheme_class = isset($settings['feedtheme']) ? ' sbi-theme sbi-' . $settings['feedtheme'] : '';

			return ' class="sb_instagram_header ' . esc_attr($type_class) . esc_attr($centered_class) . esc_attr($size_class) . esc_attr($outside_class) . esc_attr($avatar_class) . esc_attr($boxed_class) . esc_attr($palette_class) . esc_attr($feedtheme_class) . '" ';
		}
	}

	/**
	 * Not used with the core feed but can be used for customizations.
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_feed_type_class($settings)
	{
		return 'sbi_feed_type_' . esc_attr($settings['type']);
	}

	/**
	 * Header Story Attributes
	 *
	 * @param bool   $customizer Check if we are in the customizer.
	 * @param array  $settings The settings array for the feed.
	 * @param array  $header_data The header data array.
	 * @param string $avatar The avatar URL.
	 *
	 * @return string
	 * @since 6.0
	 */
	public static function get_story_attributes($customizer, $settings, $header_data, $avatar)
	{
		if ($customizer) {
			return ' :data-story-wait="$parent.getStoryData() ? $parent.getStoryDelays() : false" :data-story-data="$parent.getStoryData()" :data-story-avatar="$parent.getStoryData() ? $parent.getHeaderAvatar() : false" ';
		} else {
			$stories_delay = SB_Instagram_Display_Elements_Pro::get_stories_delay($settings);
			$story_data = SB_Instagram_Parse_Pro::get_story_data($header_data);
			$should_show_story = !empty($story_data) && SB_Instagram_Display_Elements_Pro::should_show_element('headerstory', $settings);

			return $should_show_story ? ' data-story-wait="' . (int)$stories_delay . '" data-story-data="' . esc_attr(sbi_json_encode($story_data)) . '" data-story-avatar="' . esc_attr($avatar) . '"' : '';
		}
	}

	/**
	 * Used for attribute that determines how long a slide will appear in a "story".
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return int|mixed
	 * @since 5.0
	 */
	public static function get_stories_delay($settings)
	{
		return !empty($settings['storiestime']) ? max(500, (int)$settings['storiestime']) : 5000;
	}

	/**
	 * A not very elegant but useful method to abstract out how the settings
	 * work for displaying certain elements in the feed.
	 *
	 * @param string $element The element to check if it is enabled.
	 * @param array  $settings The settings array for the feed.
	 *
	 * @return bool
	 * @since 5.0
	 */
	public static function should_show_element($element, $settings)
	{
		$customizer = sbi_doing_customizer($settings);
		$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;
		$shouldShow = false;

		if ($customizer) {
			return true;
		}

		switch ($element) {
			case 'user_info':
				$shouldShow = in_array($feedtheme, ['modern', 'social_wall', 'outline'], true);
				break;
			case 'user_info_lower':
				$shouldShow = $feedtheme === 'overlap';
				break;
			case 'date_wrap':
				$shouldShow = $feedtheme === 'outline';
				break;
			case 'posted_on_date_str':
				$shouldShow = in_array($feedtheme, ['social_wall', 'modern'], true);
				break;
			case 'user_brand':
				$shouldShow = $feedtheme === 'social_wall';
				break;
			case 'bottom_logo':
				$shouldShow = $feedtheme === 'modern';
				break;
			case 'hover_top_inner':
			case 'inner_username_span':
				$shouldShow = !empty($feedtheme) && $feedtheme !== 'default_theme';
				break;
			case 'view_button':
				$shouldShow = in_array($feedtheme, ['overlap', 'outline'], true);
				break;
			default:
				$shouldShow = self::isHoverElement($element, $settings) || self::isStandardElement($element, $settings);
				break;
		}

		return $shouldShow;
	}

	/**
	 * Determines if the given element is a hover element based on the provided settings.
	 *
	 * @param mixed $element The element to check.
	 * @param array $settings The settings to use for the check.
	 * @return bool True if the element is a hover element, false otherwise.
	 */
	private static function isHoverElement($element, $settings)
	{
		$hover_elements = [
			'hoverusername',
			'hoverdate',
			'hoverinstagram',
			'hoverlocation',
			'hovercaption',
			'hoverlikes'
		];

		if (in_array($element, $hover_elements, true)) {
			$hover_items = explode(',', str_replace(' ', '', $settings['hoverdisplay']));
			return in_array(str_replace('hover', '', $element), (array)$hover_items, true);
		}

		return false;
	}

	/**
	 * Checks if the given element is a standard element based on the provided settings.
	 *
	 * @param mixed $element The element to check.
	 * @param array $settings The settings to use for the check.
	 * @return bool True if the element is a standard element, false otherwise.
	 */
	private static function isStandardElement($element, $settings)
	{
		$default_true_options = [
			'caption',
			'likes',
			'headerfollowers',
			'headerbio',
			'headerstory'
		];

		$element_settings_pairs = [
			'caption' => 'showcaption',
			'likes' => 'showlikes',
			'headerfollowers' => 'showfollowers',
			'headerbio' => 'showbio',
			'headerstory' => 'stories',
		];

		if (in_array($element, $default_true_options, true)) {
			return $settings[$element_settings_pairs[$element]] === 'true'
				|| $settings[$element_settings_pairs[$element]] === 'on'
				|| $settings[$element_settings_pairs[$element]] === true
				|| !isset($settings[$element_settings_pairs[$element]]);
		}

		return false;
	}

	/**
	 * Photo wrap content
	 *
	 * @param array $post The post data.
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 6.0
	 */
	public static function get_photo_wrap_content($post, $settings)
	{
		$post_id = SB_Instagram_Parse_Pro::get_post_id($post);
		$media_full_res = SB_Instagram_Parse_Pro::get_media_url($post);
		$caption = SB_Instagram_Parse_Pro::get_caption($post, '');
		$caption = SB_Instagram_Display_Elements::sanitize_caption($caption);

		if (sbi_doing_customizer($settings)) {
			$moderation_mode = SB_Instagram_Display_Elements_Pro::print_moderation_toggle_button($post_id);
			$shoppable_button = SB_Instagram_Display_Elements_Pro::print_shoppable_edit_button($post_id, esc_url($media_full_res), $caption);
			return $moderation_mode . $shoppable_button;
		}

		return '';
	}

	/**
	 * Print Moderation Toggle Button
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string
	 */
	public static function print_moderation_toggle_button($post_id)
	{
		ob_start();
		?>
		<div class="sbi-moderation-overlay-ctn"
			 v-if="$parent.customizerScreens.activeSection === 'settings_filters_moderation' && $parent.viewsActive.moderationMode && $parent.valueIsEnabled($parent.customizerFeedData.settings.enablemoderationmode) "
			 @click.prevent.default="$parent.addPostToModerationList('<?php echo $post_id ?>')">
			<div class="sbi-moderation-toggle" :data-type="$parent.checkPostModertationMode('<?php echo $post_id ?>')">
				<div class="sbi-moderation-toggle-icon sbi-moderation-checkmark"></div>
				<div class="sbi-moderation-toggle-icon sbi-moderation-x"></div>
			</div>
		</div>
		<?php
		$html = ob_get_contents();
		ob_get_clean();
		return $html;
	}

	/**
	 * Print Shoppable Button
	 *
	 * @param int    $post_id The post ID.
	 * @param string $media The media URL.
	 * @param string $caption The caption.
	 *
	 * @return string
	 */
	public static function print_shoppable_edit_button($post_id, $media, $caption)
	{
		ob_start();
		$caption = SB_Instagram_Display_Elements::sanitize_caption($caption);
		?>
		<button class="sb-shoppable-edit-btn sbi-btn-grey sb-button-standard"
				v-if="!$parent.checkPostShoppableFeed('<?php echo $post_id ?>') && $parent.valueIsEnabled( $parent.customizerFeedData.settings.shoppablefeed ) && $parent.customizerScreens.activeSection === 'settings_shoppable_feed'"
				:data-active="($parent.shoppableFeed.postId == '<?php echo $post_id ?>' || Object.keys($parent.customizerFeedData.settings.shoppablelist).length == 0 )  && $parent.valueIsEnabled( $parent.customizerFeedData.settings.shoppablefeed ) && $parent.customizerScreens.activeSection === 'settings_shoppable_feed'"
				@click.prevent.default="$parent.openPostShoppableFeed('<?php echo $post_id ?>', '<?php echo $media ?>', '<?php echo htmlspecialchars($caption) ?>')">
			<div class="sb-shoppable-edit-btn-link" v-html="$parent.svgIcons['link']"></div>
			<span v-html="$parent.genericText.add"></span>
		</button>
		<button class="sb-shoppable-edit-btn sbi-btn-blue sb-button-standard"
				v-if="$parent.checkPostShoppableFeed('<?php echo $post_id ?>') && $parent.valueIsEnabled( $parent.customizerFeedData.settings.shoppablefeed ) && $parent.customizerScreens.activeSection === 'settings_shoppable_feed'"
				:data-active="($parent.shoppableFeed.postId == '<?php echo $post_id ?>' || Object.keys($parent.customizerFeedData.settings.shoppablelist).length == 0 )  && $parent.valueIsEnabled( $parent.customizerFeedData.settings.shoppablefeed ) && $parent.customizerScreens.activeSection === 'settings_shoppable_feed'"
				@click.prevent.default="$parent.openPostShoppableFeed('<?php echo $post_id ?>', '<?php echo $media ?>', '<?php echo htmlspecialchars($caption) ?>')">
			<div class="sb-shoppable-edit-btn-link" v-html="$parent.svgIcons['link']"></div>
			<span v-html="$parent.genericText.update"></span>
		</button>
		<?php
		$html = ob_get_contents();
		ob_get_clean();
		return $html;
	}

	/**
	 * Post Item HTML Attributes
	 * Mainly used in the VueJS Feed Preview
	 *
	 * @param array $post The post data.
	 * @param array $settings The settings for the feed.
	 *
	 * @return string
	 * @since 6.0
	 */
	public static function get_sbi_item_attributes($post, $settings = array())
	{
		$post_id = SB_Instagram_Parse_Pro::get_post_id($post);
		$post_attributes = '';
		if (sbi_doing_customizer($settings)) {
			$post_attributes = ' :data-moderation="$parent.checkPostModertationModeAttribute(\'<?php echo $post_id ?>\')" ';
		}

		if ($settings['captionlinks'] && !empty($settings['shoppablelist'][$post_id])) {
			$post_attributes .= ' data-shoppable="' . esc_attr($settings['shoppablelist'][$post_id]) . '"';
		}

		/**
		 * Filter the HTML attributes for feed items
		 *
		 * Allows adding custom data attributes to feed items
		 *
		 * @param array $attributes_array Associative array of attribute key => value pairs
		 * @param array $post            Post data
		 * @param array $settings        Feed settings
		 *
		 * @since 6.8.2
		 */
		$attributes_array = array();
		$attributes_array = apply_filters('sbi_item_attributes', $attributes_array, $post, $settings);

		// Convert attributes array to string
		foreach ($attributes_array as $key => $value) {
			$post_attributes .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
		}

		return $post_attributes;
	}

	/**
	 * Print Header style
	 *
	 * @param array $settings The settings for the feed id.
	 *
	 * @return string
	 */
	public static function header_type($settings)
	{
		$header_template = 'header-generic';
		if ($settings['type'] != 'hashtag' && $settings['headerstyle'] != 'text') {
			$header_template = $settings['headerstyle'] !== 'boxed' ? 'header' : 'header-boxed';
		}
		if (isset($settings['headerstyle']) && $settings['headerstyle'] == 'text') {
			$header_template = 'header-text';
		}
		return $header_template;
	}

	/**
	 * Should Show Print HTML
	 *
	 * @param bool   $customizer Check if we are in the customizer.
	 * @param string $condition The condition to check.
	 *
	 * @return string
	 * @since 6.1
	 */
	public static function create_condition_show_vue($customizer, $condition)
	{
		if ($customizer) {
			return ' v-show="' . esc_attr($condition) . '" ';
		}
		return '';
	}

	/**
	 * Text Header Style
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 6.1
	 */
	public static function get_header_text_style($settings)
	{
		if (!empty($settings['headertextcolor']) && $settings['headertextcolor'] !== '#' && !sbi_doing_customizer($settings)) {
			return ' style="color: ' . esc_attr($settings['headertextcolor']) . ' "';
		}
		return '';
	}

	/**
	 * Text Header Data Attributes
	 *
	 * @param array $settings The settings array for the feed.
	 *
	 * @return string
	 * @since 6.3
	 */
	public static function get_header_text_data_attributes($settings)
	{
		$customizer = sbi_doing_customizer($settings);

		return SB_Instagram_Display_Elements_Pro::print_element_attribute(
			$customizer,
			array(
				'attr' => 'data-header-size',
				'vue_content' => '$parent.customizerFeedData.settings.headerstyle == \'text\' ? $parent.customizerFeedData.settings.headertextsize : $parent.customizerFeedData.settings.headersize',
				'php_content' => $settings['headerstyle'] == 'text' ? $settings['headertextsize'] : $settings['headersize'],
			)
		);
	}

	/**
	 * Retrieves user information attributes based on the provided settings.
	 *
	 * @param array $settings An array of settings to determine the user information attributes.
	 *
	 * @return string An array of user information attributes.
	 */
	public static function user_info_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme == \'modern\' || $parent.customizerFeedData.settings.feedtheme == \'social_wall\' || $parent.customizerFeedData.settings.feedtheme == \'outline\''
		);
	}

	/**
	 * Retrieves and processes user information with lower attributes.
	 *
	 * @param array $settings An array of settings to process user information.
	 *
	 * @return string Processed user information with lower attributes.
	 */
	public static function user_info_lower_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme == \'overlap\''
		);
	}

	/**
	 * Wraps the date attributes based on the provided settings.
	 *
	 * @param array $settings An array of settings to configure the date attributes.
	 *
	 * @return string The modified settings with wrapped date attributes.
	 */
	public static function date_wrap_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme == \'outline\''
		);
	}

	/**
	 * Generates a string representing the date on which a post was made, based on the provided settings.
	 *
	 * @param array $settings An associative array of settings that determine how the date string should be formatted.
	 * @return string The formatted date string.
	 */
	public static function posted_on_date_str_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme == \'social_wall\' || $parent.customizerFeedData.settings.feedtheme == \'modern\''
		);
	}

	/**
	 * Generates the user brand attributes based on the provided settings.
	 *
	 * @param array $settings An array of settings to configure the user brand attributes.
	 * @return string The user brand attributes.
	 */
	public static function user_brand_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme == \'social_wall\''
		);
	}

	/**
	 * Generate the attributes for the bottom logo based on the provided settings.
	 *
	 * @param array $settings An array of settings to configure the bottom logo attributes.
	 * @return string An array of attributes for the bottom logo.
	 */
	public static function bottom_logo_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme == \'modern\''
		);
	}

	/**
	 * Generates the attributes for the Instagram feed statistics based on the provided theme and settings.
	 *
	 * @param string $theme The theme to be used for the Instagram feed.
	 * @param array  $settings The settings array containing configuration options for the Instagram feed.
	 *
	 * @return string The attributes for the Instagram feed statistics.
	 */
	public static function stats_atts($theme, $settings)
	{
		if ($theme === 'default_theme') {
			return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
				$settings,
				'!$parent.customizerFeedData.settings.feedtheme || $parent.customizerFeedData.settings.feedtheme == \'default_theme\''
			);
		}
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme == \'' . $theme . '\''
		);
	}

	/**
	 * Generate HTML attributes for the hover date element based on the provided settings.
	 *
	 * @param array $settings An associative array of settings to customize the hover date element.
	 * @return string A string of HTML attributes for the hover date element.
	 */
	public static function hover_date_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'!$parent.customizerFeedData.settings.feedtheme || $parent.customizerFeedData.settings.feedtheme == \'default_theme\''
		);
	}

	/**
	 * Returns the default exclusion attributes for the given settings.
	 *
	 * @param array $settings The settings array to be used for generating the exclusion attributes.
	 * @return string The default exclusion attributes.
	 */
	public static function default_exclusion_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme && $parent.customizerFeedData.settings.feedtheme !== \'default_theme\''
		);
	}

	/**
	 * Generates the attributes for the Instagram view based on the provided settings.
	 *
	 * @param array $settings An array of settings to configure the Instagram view.
	 * @return string The attributes for the Instagram view.
	 */
	public static function instagram_view_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'$parent.customizerFeedData.settings.feedtheme == \'outline\' || $parent.customizerFeedData.settings.feedtheme == \'overlap\''
		);
	}

	/**
	 * Generates the bio information attributes based on the provided settings.
	 *
	 * @param array $settings An associative array of settings used to generate the bio information attributes.
	 *
	 * @return string An associative array of bio information attributes.
	 */
	public static function bio_info_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'!$parent.customizerFeedData.settings.feedtheme || $parent.customizerFeedData.settings.feedtheme == \'default_theme\' || $parent.customizerFeedData.settings.feedtheme == \'modern\' || $parent.customizerFeedData.settings.feedtheme == \'overlap\' || $parent.customizerFeedData.settings.headerstyle == \'centered\''
		);
	}

	/**
	 * Generates the header information attributes based on the provided settings.
	 *
	 * @param array $settings An array of settings used to generate the header information attributes.
	 *
	 * @return string An array of header information attributes.
	 */
	public static function header_info_atts($settings)
	{
		return ' ' . SB_Instagram_Display_Elements_Pro::theme_condition(
			$settings,
			'($parent.customizerFeedData.settings.feedtheme == \'social_wall\' || $parent.customizerFeedData.settings.feedtheme == \'outline\')	&& $parent.customizerFeedData.settings.headerstyle != \'centered\''
		);
	}

	/**
	 * Retrieves a specific template attribute based on the provided settings.
	 *
	 * @param array $settings An associative array of settings used to determine the template attribute.
	 * @return string The value of the template attribute based on the provided settings.
	 */
	public static function get_template_attribute($settings)
	{
		$customizer = sbi_doing_customizer($settings);
		$template_attr = '';
		if ($customizer) {
			$template_attr = ':data-template="($parent.customizerFeedData.settings.feedtemplate ? $parent.customizerFeedData.settings.feedtemplate : \'\')" ';
			$template_attr .= ' :data-layout="($parent.customizerFeedData.settings.layout ? $parent.customizerFeedData.settings.layout : \'\')" ';
		}
		if (!$customizer && !empty($settings['feedtemplate'])) {
			$template_attr = "data-template=" . esc_attr($settings['feedtemplate']);
		}

		return $template_attr;
	}

	/**
	 * Determines whether the stats element should be displayed based on the theme element type and settings.
	 *
	 * @param string $theme_element_type The type of theme element.
	 * @param array  $settings The settings array that contains configuration options.
	 * @return bool True if the stats element should be displayed, false otherwise.
	 */
	public static function should_show_stats_element($theme_element_type, $settings)
	{
		$customizer = sbi_doing_customizer($settings);
		if ($customizer) {
			return true;
		}
		$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;
		if (empty($feedtheme)) {
			return $theme_element_type === 'default_theme';
		}

		return $theme_element_type === $feedtheme;
	}

	/**
	 * Generates the theme types for follower count display.
	 *
	 * @param int    $follower_count The number of followers.
	 * @param string $header_text_color_style The CSS style for the header text color.
	 * @return array The theme types for follower count display.
	 */
	public static function follower_theme_types($follower_count, $header_text_color_style)
	{
		return array(
			array(
				'type' => 'default_theme',
				'no_colors' => true,
				'display' => SB_Instagram_Display_Elements_Pro::get_icon('user', 'svg') . number_format_i18n((int)$follower_count, 0)
			),
			array(
				'type' => 'modern',
				'display' => number_format_i18n((int)$follower_count, 0) . ' ' . __('Followers', 'instagram-feed')
			),
			array(
				'type' => 'social_wall',
				'display' => SB_Instagram_Display_Elements_Pro::get_icon('user', 'svg', $header_text_color_style, 'social_wall') . number_format_i18n((int)$follower_count, 0)
			),
			array(
				'type' => 'outline',
				'display' => SB_Instagram_Display_Elements_Pro::get_icon('user', 'svg', $header_text_color_style, 'outline') . number_format_i18n((int)$follower_count, 0)
			),
			array(
				'type' => 'overlap',
				'display' => SB_Instagram_Display_Elements_Pro::get_icon('user', 'svg', $header_text_color_style, 'overlap') . number_format_i18n((int)$follower_count, 0)
			)
		);
	}

	/**
	 * Pro - First looks for Pro only icons
	 *
	 * @param string $type The icon to retrieve.
	 * @param string $icon_type The type of icon.
	 * @param string $styles Styles to apply to the icon.
	 * @param string $theme The theme type.
	 *
	 * @return string
	 * @since 5.0
	 */
	public static function get_icon($type, $icon_type, $styles = '', $theme = '')
	{
		$icon = self::get_pro_icons($type, $icon_type, $styles, $theme);

		if ($icon === '') {
			$icon = self::get_basic_icons($type, $icon_type);
		}
		return $icon;
	}

	/**
	 * Get the icons for the pro elements.
	 *
	 * @param string $type The icon to retrieve.
	 * @param string $icon_type The type of icon.
	 * @param string $styles Styles to apply to the icon.
	 * @param string $theme The theme type.
	 *
	 * @return string
	 * @since 5.0
	 */
	private static function get_pro_icons($type, $icon_type, $styles = '', $theme = '')
	{
		$icons = [
			'date' => [
				'svg' => '<svg ' . $styles . ' class="svg-inline--fa fa-clock fa-w-16" aria-hidden="true" data-fa-processed="" data-prefix="far" data-icon="clock" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8L334.6 349c-3.9 5.3-11.4 6.5-16.8 2.6z"></path></svg>',
				'font' => '<i class="fa fa-clock" aria-hidden="true"></i>'
			],
			'likes' => [
				'svg' => '<svg ' . $styles . ' class="svg-inline--fa fa-heart fa-w-18" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="heart" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M414.9 24C361.8 24 312 65.7 288 89.3 264 65.7 214.2 24 161.1 24 70.3 24 16 76.9 16 165.5c0 72.6 66.8 133.3 69.2 135.4l187 180.8c8.8 8.5 22.8 8.5 31.6 0l186.7-180.2c2.7-2.7 69.5-63.5 69.5-136C560 76.9 505.7 24 414.9 24z"></path></svg>',
				'font' => '<i class="fa fa-heart" aria-hidden="true"></i>'
			],
			'comments' => [
				'svg' => '<svg ' . $styles . ' class="svg-inline--fa fa-comment fa-w-18" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="comment" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M576 240c0 115-129 208-288 208-48.3 0-93.9-8.6-133.9-23.8-40.3 31.2-89.8 50.3-142.4 55.7-5.2.6-10.2-2.8-11.5-7.7-1.3-5 2.7-8.1 6.6-11.8 19.3-18.4 42.7-32.8 51.9-94.6C21.9 330.9 0 287.3 0 240 0 125.1 129 32 288 32s288 93.1 288 208z"></path></svg>',
				'font' => '<i class="fa fa-comment" aria-hidden="true"></i>'
			],
			'newlogo' => [
				'svg' => '<svg ' . $styles . ' class="sbi_new_logo fa-instagram fa-w-14" aria-hidden="true" data-fa-processed="" data-prefix="fab" data-icon="instagram" role="img" viewBox="0 0 448 512"><path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path></svg>',
			],
			'map_marker' => [
				'svg' => '<svg ' . $styles . ' class="svg-inline--fa fa-map-marker fa-w-12" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="map-marker" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0z"></path></svg>',
				'font' => '<i class="fa fa-map-marker" aria-hidden="true"></i>'
			],
			'photo' => [
				'svg' => '<svg class="svg-inline--fa fa-image fa-w-16" aria-hidden="true" data-fa-processed="" data-prefix="far" data-icon="image" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 448H48c-26.51 0-48-21.49-48-48V112c0-26.51 21.49-48 48-48h416c26.51 0 48 21.49 48 48v288c0 26.51-21.49 48-48 48zM112 120c-30.928 0-56 25.072-56 56s25.072 56 56 56 56-25.072 56-56-25.072-56-56-56zM64 384h384V272l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L208 320l-55.515-55.515c-4.686-4.686-12.284-4.686-16.971 0L64 336v48z"></path></svg>',
				'font' => '<i class="fa fa-image" aria-hidden="true"></i>',
				'social_wall' => '<svg ' . $styles . ' viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_1037_2408920)"><circle cx="10" cy="10" r="10" fill="currentColor"/><path d="M13.25 5.5H7.25C6.85218 5.5 6.47064 5.65804 6.18934 5.93934C5.90804 6.22064 5.75 6.60218 5.75 7V13C5.75 13.3978 5.90804 13.7794 6.18934 14.0607C6.47064 14.342 6.85218 14.5 7.25 14.5H13.25C13.6478 14.5 14.0294 14.342 14.3107 14.0607C14.592 13.7794 14.75 13.3978 14.75 13V7C14.75 6.60218 14.592 6.22064 14.3107 5.93934C14.0294 5.65804 13.6478 5.5 13.25 5.5ZM7.25 6.5H13.25C13.3826 6.5 13.5098 6.55268 13.6036 6.64645C13.6973 6.74021 13.75 6.86739 13.75 7V11.18L12.15 9.815C11.9021 9.61102 11.591 9.4995 11.27 9.4995C10.949 9.4995 10.6379 9.61102 10.39 9.815L6.75 12.85V7C6.75 6.86739 6.80268 6.74021 6.89645 6.64645C6.99021 6.55268 7.11739 6.5 7.25 6.5Z" fill="white"/><path d="M8.75 9.5C9.30229 9.5 9.75 9.05229 9.75 8.5C9.75 7.94772 9.30229 7.5 8.75 7.5C8.19772 7.5 7.75 7.94772 7.75 8.5C7.75 9.05229 8.19772 9.5 8.75 9.5Z" fill="white"/></g><defs><clipPath id="clip0_1037_2408920"><rect width="20" height="20" fill="white"/></clipPath></defs></svg>',
				'outline' => '<svg ' . $styles . ' viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 5C4 4.44772 4.44772 4 5 4H19C19.5523 4 20 4.44772 20 5V19C20 19.5523 19.5523 20 19 20H5C4.44772 20 4 19.5523 4 19V5Z" stroke="currentColor"/><path d="M4.19922 16.5542L10.665 11.0874L14.8226 14.245L19.7864 9.28125" stroke="currentColor" stroke-linejoin="round"/></svg>',
				'overlap' => '<svg ' . $styles . ' viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 4.90625C6 4.90625 4.40625 6.53125 4.40625 8.5C4.40625 10.5 6 12.0938 8 12.0938C9.96875 12.0938 11.5938 10.5 11.5938 8.5C11.5938 6.53125 9.96875 4.90625 8 4.90625ZM8 10.8438C6.71875 10.8438 5.65625 9.8125 5.65625 8.5C5.65625 7.21875 6.6875 6.1875 8 6.1875C9.28125 6.1875 10.3125 7.21875 10.3125 8.5C10.3125 9.8125 9.28125 10.8438 8 10.8438ZM12.5625 4.78125C12.5625 4.3125 12.1875 3.9375 11.7188 3.9375C11.25 3.9375 10.875 4.3125 10.875 4.78125C10.875 5.25 11.25 5.625 11.7188 5.625C12.1875 5.625 12.5625 5.25 12.5625 4.78125ZM14.9375 5.625C14.875 4.5 14.625 3.5 13.8125 2.6875C13 1.875 12 1.625 10.875 1.5625C9.71875 1.5 6.25 1.5 5.09375 1.5625C3.96875 1.625 3 1.875 2.15625 2.6875C1.34375 3.5 1.09375 4.5 1.03125 5.625C0.96875 6.78125 0.96875 10.25 1.03125 11.4062C1.09375 12.5312 1.34375 13.5 2.15625 14.3438C3 15.1562 3.96875 15.4062 5.09375 15.4688C6.25 15.5312 9.71875 15.5312 10.875 15.4688C12 15.4062 13 15.1562 13.8125 14.3438C14.625 13.5 14.875 12.5312 14.9375 11.4062C15 10.25 15 6.78125 14.9375 5.625ZM13.4375 12.625C13.2188 13.25 12.7188 13.7188 12.125 13.9688C11.1875 14.3438 9 14.25 8 14.25C6.96875 14.25 4.78125 14.3438 3.875 13.9688C3.25 13.7188 2.78125 13.25 2.53125 12.625C2.15625 11.7188 2.25 9.53125 2.25 8.5C2.25 7.5 2.15625 5.3125 2.53125 4.375C2.78125 3.78125 3.25 3.3125 3.875 3.0625C4.78125 2.6875 6.96875 2.78125 8 2.78125C9 2.78125 11.1875 2.6875 12.125 3.0625C12.7188 3.28125 13.1875 3.78125 13.4375 4.375C13.8125 5.3125 13.7188 7.5 13.7188 8.5C13.7188 9.53125 13.8125 11.7188 13.4375 12.625Z" fill="currentColor"/></svg>'
			],
			'user' => [
				'svg' => '<svg class="svg-inline--fa fa-user fa-w-16" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="user" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M96 160C96 71.634 167.635 0 256 0s160 71.634 160 160-71.635 160-160 160S96 248.366 96 160zm304 192h-28.556c-71.006 42.713-159.912 42.695-230.888 0H112C50.144 352 0 402.144 0 464v24c0 13.255 10.745 24 24 24h464c13.255 0 24-10.745 24-24v-24c0-61.856-50.144-112-112-112z"></path></svg>',
				'font' => '<i class="fa fa-user" aria-hidden="true"></i>',
				'social_wall' => '<svg ' . $styles . ' viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="20" height="20" rx="10" fill="currentColor"/><path d="M10.2683 9.47952C11.6413 9.47952 12.7844 8.26185 12.7844 6.70248C12.7844 5.18039 11.6351 4 10.2683 4C8.90151 4 7.73976 5.19903 7.74597 6.71491C7.74597 8.26185 8.8953 9.47952 10.2683 9.47952ZM6.15554 15.2759H14.3748C15.0955 15.2759 15.5304 14.928 15.5304 14.3564C15.5304 12.7598 13.4864 10.5729 10.2621 10.5729C7.04395 10.5729 5 12.7598 5 14.3564C5 14.928 5.43488 15.2759 6.15554 15.2759Z" fill="white"/></svg>',
				'outline' => '<svg ' . $styles . ' viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12.5" cy="8" r="4" stroke="currentColor" stroke-width="1.25"/><path d="M19.5 20V20C19.5 16.134 16.366 13 12.5 13V13C8.63401 13 5.5 16.134 5.5 20V20" stroke="currentColor" stroke-width="1.25"/></svg>',
				'overlap' => '<svg ' . $styles . ' viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.88932 14.069H12.1185C12.4527 14.069 12.7153 13.9931 12.9062 13.8411C13.0972 13.6936 13.1927 13.4874 13.1927 13.2227C13.1927 12.8233 13.0733 12.4067 12.8346 11.9727C12.5959 11.5343 12.2487 11.1241 11.793 10.7422C11.3416 10.3602 10.7969 10.0499 10.1589 9.8112C9.52083 9.57248 8.80252 9.45312 8.00391 9.45312C7.2053 9.45312 6.48481 9.57248 5.84245 9.8112C5.20443 10.0499 4.65972 10.3602 4.20833 10.7422C3.75694 11.1241 3.40972 11.5343 3.16667 11.9727C2.92795 12.4067 2.80859 12.8233 2.80859 13.2227C2.80859 13.4874 2.90408 13.6936 3.09505 13.8411C3.28602 13.9931 3.55078 14.069 3.88932 14.069ZM8.01042 8.35286C8.45312 8.35286 8.86111 8.23351 9.23437 7.99479C9.60764 7.75174 9.90712 7.42405 10.1328 7.01172C10.3628 6.59939 10.4779 6.13498 10.4779 5.61849C10.4779 5.11502 10.3628 4.66363 10.1328 4.26432C9.90712 3.86502 9.60764 3.55035 9.23437 3.32031C8.86111 3.08594 8.45312 2.96875 8.01042 2.96875C7.56771 2.96875 7.15755 3.08594 6.77995 3.32031C6.40234 3.55469 6.09852 3.8737 5.86849 4.27734C5.6428 4.68099 5.53212 5.13238 5.53646 5.63151C5.53646 6.13932 5.64931 6.59939 5.875 7.01172C6.10069 7.42405 6.40017 7.75174 6.77344 7.99479C7.15104 8.23351 7.56337 8.35286 8.01042 8.35286Z" fill="currentColor"/></svg>'
			],
			'view_instagram' => [
				'svg' => '<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 3.90625C6 3.90625 4.40625 5.53125 4.40625 7.5C4.40625 9.5 6 11.0938 8 11.0938C9.96875 11.0938 11.5938 9.5 11.5938 7.5C11.5938 5.53125 9.96875 3.90625 8 3.90625ZM8 9.84375C6.71875 9.84375 5.65625 8.8125 5.65625 7.5C5.65625 6.21875 6.6875 5.1875 8 5.1875C9.28125 5.1875 10.3125 6.21875 10.3125 7.5C10.3125 8.8125 9.28125 9.84375 8 9.84375ZM12.5625 3.78125C12.5625 3.3125 12.1875 2.9375 11.7188 2.9375C11.25 2.9375 10.875 3.3125 10.875 3.78125C10.875 4.25 11.25 4.625 11.7188 4.625C12.1875 4.625 12.5625 4.25 12.5625 3.78125ZM14.9375 4.625C14.875 3.5 14.625 2.5 13.8125 1.6875C13 0.875 12 0.625 10.875 0.5625C9.71875 0.5 6.25 0.5 5.09375 0.5625C3.96875 0.625 3 0.875 2.15625 1.6875C1.34375 2.5 1.09375 3.5 1.03125 4.625C0.96875 5.78125 0.96875 9.25 1.03125 10.4062C1.09375 11.5312 1.34375 12.5 2.15625 13.3438C3 14.1562 3.96875 14.4062 5.09375 14.4688C6.25 14.5312 9.71875 14.5312 10.875 14.4688C12 14.4062 13 14.1562 13.8125 13.3438C14.625 12.5 14.875 11.5312 14.9375 10.4062C15 9.25 15 5.78125 14.9375 4.625ZM13.4375 11.625C13.2188 12.25 12.7188 12.7188 12.125 12.9688C11.1875 13.3438 9 13.25 8 13.25C6.96875 13.25 4.78125 13.3438 3.875 12.9688C3.25 12.7188 2.78125 12.25 2.53125 11.625C2.15625 10.7188 2.25 8.53125 2.25 7.5C2.25 6.5 2.15625 4.3125 2.53125 3.375C2.78125 2.78125 3.25 2.3125 3.875 2.0625C4.78125 1.6875 6.96875 1.78125 8 1.78125C9 1.78125 11.1875 1.6875 12.125 2.0625C12.7188 2.28125 13.1875 2.78125 13.4375 3.375C13.8125 4.3125 13.7188 6.5 13.7188 7.5C13.7188 8.53125 13.8125 10.7188 13.4375 11.625Z" fill="#434960"/></svg>',
				'font' => '<i class="fa fab fa-instagram" aria-hidden="true"></i>'
			],
			'instagram_colorful' => [
				'svg' => '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 5.10889C8 5.10889 5.60938 7.54639 5.60938 10.4995C5.60938 13.4995 8 15.8901 11 15.8901C13.9531 15.8901 16.3906 13.4995 16.3906 10.4995C16.3906 7.54639 13.9531 5.10889 11 5.10889ZM11 14.0151C9.07812 14.0151 7.48438 12.4683 7.48438 10.4995C7.48438 8.57764 9.03125 7.03076 11 7.03076C12.9219 7.03076 14.4688 8.57764 14.4688 10.4995C14.4688 12.4683 12.9219 14.0151 11 14.0151ZM17.8438 4.92139C17.8438 4.21826 17.2812 3.65576 16.5781 3.65576C15.875 3.65576 15.3125 4.21826 15.3125 4.92139C15.3125 5.62451 15.875 6.18701 16.5781 6.18701C17.2812 6.18701 17.8438 5.62451 17.8438 4.92139ZM21.4062 6.18701C21.3125 4.49951 20.9375 2.99951 19.7188 1.78076C18.5 0.562012 17 0.187012 15.3125 0.0932617C13.5781 -0.000488281 8.375 -0.000488281 6.64062 0.0932617C4.95312 0.187012 3.5 0.562012 2.23438 1.78076C1.01562 2.99951 0.640625 4.49951 0.546875 6.18701C0.453125 7.92139 0.453125 13.1245 0.546875 14.8589C0.640625 16.5464 1.01562 17.9995 2.23438 19.2651C3.5 20.4839 4.95312 20.8589 6.64062 20.9526C8.375 21.0464 13.5781 21.0464 15.3125 20.9526C17 20.8589 18.5 20.4839 19.7188 19.2651C20.9375 17.9995 21.3125 16.5464 21.4062 14.8589C21.5 13.1245 21.5 7.92139 21.4062 6.18701ZM19.1562 16.687C18.8281 17.6245 18.0781 18.3276 17.1875 18.7026C15.7812 19.2651 12.5 19.1245 11 19.1245C9.45312 19.1245 6.17188 19.2651 4.8125 18.7026C3.875 18.3276 3.17188 17.6245 2.79688 16.687C2.23438 15.3276 2.375 12.0464 2.375 10.4995C2.375 8.99951 2.23438 5.71826 2.79688 4.31201C3.17188 3.42139 3.875 2.71826 4.8125 2.34326C6.17188 1.78076 9.45312 1.92139 11 1.92139C12.5 1.92139 15.7812 1.78076 17.1875 2.34326C18.0781 2.67139 18.7812 3.42139 19.1562 4.31201C19.7188 5.71826 19.5781 8.99951 19.5781 10.4995C19.5781 12.0464 19.7188 15.3276 19.1562 16.687Z" fill="url(#paint0_linear_1620_42939)"/><defs><linearGradient id="paint0_linear_1620_42939" x1="7.95781" y1="40.1854" x2="52.1891" y2="-4.96455" gradientUnits="userSpaceOnUse"><stop stop-color="white"/><stop offset="0.147864" stop-color="#F6640E"/><stop offset="0.443974" stop-color="#BA03A7"/><stop offset="0.733337" stop-color="#6A01B9"/><stop offset="1" stop-color="#6B01B9"/></linearGradient></defs></svg>'
			]
		];

		if (isset($icons[$type][$icon_type])) {
			if (!empty($theme) && isset($icons[$type][$theme])) {
				return $icons[$type][$theme];
			}
			return $icons[$type][$icon_type];
		}

		return '';
	}

	/**
	 * Generates the post count theme types.
	 *
	 * @param int    $post_count The number of posts to display.
	 * @param string $header_text_color_style The CSS style for the header text color.
	 * @return array
	 */
	public static function post_count_theme_types($post_count, $header_text_color_style)
	{
		return array(
			array(
				'type' => 'default_theme',
				'no_colors' => true,
				'display' => SB_Instagram_Display_Elements_Pro::get_icon('photo', 'svg') . number_format_i18n((int)$post_count, 0)
			),
			array(
				'type' => 'modern',
				'display' => number_format_i18n((int)$post_count, 0) . ' ' . __('Posts', 'instagram-feed')
			),
			array(
				'type' => 'social_wall',
				'display' => SB_Instagram_Display_Elements_Pro::get_icon('photo', 'svg', $header_text_color_style, 'social_wall') . number_format_i18n((int)$post_count, 0)
			),
			array(
				'type' => 'outline',
				'display' => SB_Instagram_Display_Elements_Pro::get_icon('photo', 'svg', $header_text_color_style, 'outline') . number_format_i18n((int)$post_count, 0)
			),
			array(
				'type' => 'overlap',
				'display' => SB_Instagram_Display_Elements_Pro::get_icon('photo', 'svg', $header_text_color_style, 'overlap') . number_format_i18n((int)$post_count, 0)
			)
		);
	}
}
