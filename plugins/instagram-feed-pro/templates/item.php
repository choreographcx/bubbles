<?php

/**
 * Instagram Feed Item Template
 * Adds an image, link, and other data for each post in the feed
 *
 * @version 6.0 Instagram Feed Pro by Smash Balloon
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;
$classes = SB_Instagram_Display_Elements_Pro::get_item_classes($settings, $post);
$post_id = SB_Instagram_Parse_Pro::get_post_id($post);
$timestamp = SB_Instagram_Parse_Pro::get_timestamp($post);
$media_type = SB_Instagram_Parse_Pro::get_media_type($post);

// Pro Elements.
$caption = SB_Instagram_Parse_Pro::get_caption($post);
$sanitized_caption = SB_Instagram_Display_Elements_Pro::sanitize_caption($caption);
$no_caption_class = $caption == '' ? ' sbi-no-caption' : '';
$custom_avatar = !empty($settings['customavatar']) ? $settings['customavatar'] : '';
$avatar = SB_Instagram_Parse_Pro::get_item_avatar($post, $settings['feed_avatars']);
$feedtheme_avatar = $custom_avatar != '' ? $custom_avatar : $avatar;
$feedtheme_avatar_class = $feedtheme_avatar == '' ? ' sbi-no-feed-avatar' : '';
$username = SB_Instagram_Parse_Pro::get_username($post);
$feedtheme_avatar_class .= $username == '' ? ' sbi-no-username' : '';
$comments_count = SB_Instagram_Parse_Pro::get_comments_count($post);
// "basic display" API does not support comment or like counts as of January 2020.
$comment_or_like_counts_data_exists = SB_Instagram_Parse_Pro::comment_or_like_counts_data_exists($post);
// post item HTML attributes.
$sbi_item_attributes = SB_Instagram_Display_Elements_Pro::get_sbi_item_attributes($post, $settings);

// Pro Styles.
// style="font-size: 13px;" already escaped.
$sbi_info_styles = SB_Instagram_Display_Elements_Pro::get_sbi_info_styles($settings);
// style="font-size: 13px;" already escaped.
$sbi_meta_color_styles = SB_Instagram_Display_Elements_Pro::get_sbi_meta_color_styles($settings);
$sbi_inner_wrapper_style = SB_Instagram_Display_Elements_Pro::get_item_styles_for_post_style($settings);

/**
 * Date string for date posted
 *
 * @param string $img_alt full caption for post
 * @param array $post api data for the post
 *
 * @since 5.6.3
 */
$posted_on_date_str = ucfirst(date_i18n('M j', $timestamp)); // ex. Feb 2.
$posted_on_date_str = apply_filters('sbi_posted_on_date', $posted_on_date_str, $timestamp);

$template_attr = SB_Instagram_Display_Elements_Pro::get_template_attribute($settings);

?>
<div class="sbi_item sbi_type_<?php echo esc_attr($media_type); ?><?php echo esc_attr($classes); ?>"
	 id="sbi_<?php echo esc_attr($post_id); ?>" data-date="<?php echo esc_attr($timestamp); ?>"
	 data-numcomments="<?php echo esc_attr($comments_count); ?>" <?php echo $sbi_item_attributes ?><?php echo $template_attr; ?>>
	<div class="sbi_inner_wrap" <?php echo $sbi_inner_wrapper_style; ?>>

		<?php if (SB_Instagram_Display_Elements_Pro::should_show_element('user_info', $settings)) : ?>
			<div class="sbi-user-info" <?php echo SB_Instagram_Display_Elements_Pro::user_info_atts($settings); ?>>
				<?php if ($feedtheme_avatar) : ?>
					<img class="sbi-feedtheme-avatar" src="<?php echo esc_attr($feedtheme_avatar); ?>"
						 alt="<?php echo esc_attr($username) . ' ' . esc_attr__('avatar', 'instagram-feed'); ?>"/>
				<?php endif; ?>

				<div class="sbi-user-info-inner<?php echo esc_attr($feedtheme_avatar_class); ?>">
					<strong><?php echo esc_html($username); ?></strong>

					<?php if (SB_Instagram_Display_Elements_Pro::should_show_element('date_wrap', $settings)) : ?>
						<div
							class="sbi-date-wrap" <?php echo SB_Instagram_Display_Elements_Pro::date_wrap_atts($settings); ?>>
							<svg width="12" height="13" viewBox="0 0 12 13" fill="none">
								<path
									d="M6 11.1855C8.58883 11.1855 10.6875 9.08688 10.6875 6.49805C10.6875 3.90921 8.58883 1.81055 6 1.81055C3.41117 1.81055 1.3125 3.90921 1.3125 6.49805C1.3125 9.08688 3.41117 11.1855 6 11.1855Z"
									stroke="#434960" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M6.1875 4.06055V6.68555L4.3125 8.18555" stroke="#434960" stroke-linecap="round"
									  stroke-linejoin="round"/>
							</svg>

							<span><?php echo esc_html($posted_on_date_str); ?></span>
						</div>
					<?php endif; ?>

					<?php if (SB_Instagram_Display_Elements_Pro::should_show_element('posted_on_date_str', $settings)) : ?>
						<span<?php echo SB_Instagram_Display_Elements_Pro::posted_on_date_str_atts($settings); ?>><?php echo esc_html($posted_on_date_str); ?></span>
					<?php endif; ?>
				</div>

				<?php if (SB_Instagram_Display_Elements_Pro::should_show_element('user_brand', $settings)) : ?>
					<div
						class="sbi-user-brand" <?php echo SB_Instagram_Display_Elements_Pro::user_brand_atts($settings); ?>>
						<?php echo SB_Instagram_Display_Elements_Pro::get_icon('instagram_colorful', 'svg'); ?>
					</div>
				<?php endif; ?>

			</div>
		<?php endif; ?>

		<?php
		/**
		 * Hover items
		 */
		include sbi_get_feed_template_part('elements/item/hover-item', $settings);
		?>

		<?php if (SB_Instagram_Display_Elements_Pro::should_show_element('user_info_lower', $settings)) : ?>
			<div
				class="sbi-user-info" <?php echo SB_Instagram_Display_Elements_Pro::user_info_lower_atts($settings); ?>>

				<?php if ($feedtheme_avatar) : ?>
					<img class="sbi-feedtheme-avatar" src="<?php echo esc_attr($feedtheme_avatar); ?>"
						 alt="<?php echo esc_attr($username) . ' ' . esc_attr__('avatar', 'instagram-feed'); ?> "/>
				<?php endif; ?>

				<div class="sbi-user-info-inner <?php echo esc_attr($feedtheme_avatar_class); ?>">
					<strong><?php echo esc_html($username); ?></strong>
					<span><?php echo esc_html($posted_on_date_str); ?></span>
				</div>
			</div>

		<?php endif; ?>

		<div class="sbi_info_wrapper">
			<div class="sbi_info <?php echo esc_attr($no_caption_class); ?>">

				<?php if (!empty($sanitized_caption) && ($customizer || SB_Instagram_Display_Elements_Pro::should_show_element('caption', $settings))) : ?>
					<p class="sbi_caption_wrap" <?php echo SB_Instagram_Display_Elements_Pro::get_caption_data_attributes($settings, $caption, $post_id); ?>>
						<span
							class="sbi_caption" <?php echo $sbi_info_styles; ?> aria-hidden="true"><?php echo str_replace('&lt;br&gt;', '<br>', esc_html(nl2br($sanitized_caption))); ?></span>
						<span class="sbi_expand">
							<a href="#"><span class="sbi_more">...</span></a>
						</span>
					</p>
				<?php endif; ?>

				<div class="sbi_meta_wrap">
					<?php if ($comment_or_like_counts_data_exists && ($customizer || SB_Instagram_Display_Elements_Pro::should_show_element('likes', $settings))) : ?>
						<div
							class="sbi_meta" <?php echo $sbi_meta_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::get_meta_data_attributes($settings); ?>>
							<?php
							/**
							 * Likes and comments
							 */
							include sbi_get_feed_template_part('elements/item/likes', $settings);
							include sbi_get_feed_template_part('elements/item/comments', $settings);
							?>

							<?php if (SB_Instagram_Display_Elements_Pro::should_show_element('bottom_logo', $settings)) : ?>
								<span
									class="sbi-instagram-icon" <?php echo SB_Instagram_Display_Elements_Pro::bottom_logo_atts($settings); ?>>
									<a href="<?php echo esc_url('https://www.instagram.com/' . $username . '/'); ?>"
									   target="_blank" rel="nofollow noopener">
										<img
											src="<?php echo trailingslashit(SBI_PLUGIN_URL); ?>img/theme-assets/modern-soc-icon.svg"
											alt="<?php esc_attr_e('instagram icon', 'instagram-feed'); ?>">
									</a>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php
					/**
					 * View on Instagram button
					 */
					include sbi_get_feed_template_part('elements/item/view-btn', $settings);
					?>
				</div>
			</div>
		</div>
	</div>

	<div class="sbi-divider"></div>
</div>
