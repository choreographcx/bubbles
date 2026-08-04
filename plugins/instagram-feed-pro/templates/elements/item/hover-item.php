<?php

/**
 * Hover elements
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;

$post_id = SB_Instagram_Parse_Pro::get_post_id($post);
$username = SB_Instagram_Parse_Pro::get_username($post);
$caption = SB_Instagram_Parse_Pro::get_caption($post);
$no_caption_class = $caption == '' ? ' sbi-no-caption' : '';
$custom_avatar = !empty($settings['customavatar']) ? $settings['customavatar'] : '';
$avatar = SB_Instagram_Parse_Pro::get_item_avatar($post, $settings['feed_avatars']);
$timestamp = SB_Instagram_Parse_Pro::get_timestamp($post);
$permalink = SB_Instagram_Parse_Pro::get_permalink($post);
$media_type = SB_Instagram_Parse_Pro::get_media_type($post);
$maybe_content = SB_Instagram_Display_Elements_Pro::get_photo_wrap_content($post, $settings);
$maybe_carousel_icon = $media_type === 'carousel' ? SB_Instagram_Display_Elements_Pro::get_icon('carousel', 'svg') : '';
$maybe_video_icon = $media_type === 'video' ? SB_Instagram_Display_Elements_Pro::get_icon('video', 'svg') : '';
$media_url = SB_Instagram_Display_Elements_Pro::get_optimum_media_url($post, $settings, $resized_images);

$media_full_res = SB_Instagram_Parse_Pro::get_media_url($post);
$media_all_sizes_json = SB_Instagram_Parse_Pro::get_media_src_set($post, $resized_images);
$media_iframe_url = SB_Instagram_Parse_Pro::get_iframe_url($post);
$media_product_type = SB_Instagram_Parse_Pro::get_media_video_type($post);
$media_posted_on = SB_Instagram_Parse_Pro::get_posted_on_date($post);

// style="background: rgba(30,115,190,0.85)" already escaped.
$link_styles = SB_Instagram_Display_Elements_Pro::get_sbi_link_styles($settings);
// style="color: rgba(153,231,255,1)" already escaped.
$hover_styles = SB_Instagram_Display_Elements_Pro::get_hover_styles($settings);
// ' sbi_disable_lightbox'.
$sbi_link_classes = SB_Instagram_Display_Elements_Pro::get_sbi_link_classes($settings);

// "basic display" API does not support comment or like counts as of January 2020.
$comment_or_like_counts_data_exists = SB_Instagram_Parse_Pro::comment_or_like_counts_data_exists($post);
// array( 'name' => $name, 'id' => $int, 'longitude' => $lon_int , 'lattitude' => $lat_int ).
$location_info = SB_Instagram_Parse_Pro::get_location_info($post);
// array( 'video' => $url, 'carousel' => $json ).
$lightbox_media_atts = SB_Instagram_Parse_Pro::get_lightbox_media_atts($post);

$sbi_post_image_style_attribute = SB_Instagram_Display_Elements_Pro::get_post_image_style_attributes($settings);
$show_hover_date = SB_Instagram_Display_Elements_Pro::should_show_element('hoverdate', $settings);
$show_hover_location = SB_Instagram_Display_Elements_Pro::should_show_element('hoverlocation', $settings);

/**
 * Text that appears in the "alt" attribute for this image
 *
 * @param string $img_alt full caption for post
 * @param array $post api data for the post
 *
 * @since 5.2.6
 */
/* translators: %s is the Instagram post ID */
$img_alt = SB_Instagram_Parse_Pro::get_caption($post, sprintf(__('Instagram post %s', 'instagram-feed'), $post_id));
$img_alt = apply_filters('sbi_img_alt', $img_alt, $post);

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

?>
	<div class="sbi_photo_wrap" <?php echo $sbi_post_image_style_attribute; ?>>
		<?php echo $maybe_content; ?>
		<?php echo $maybe_carousel_icon; ?>
		<?php echo $maybe_video_icon; ?>

		<div <?php echo $link_styles; ?> <?php echo $sbi_link_classes; ?>>
			<div class="sbi_hover_top">

				<?php
				/**
				 * Hover username and caption
				 */
				include sbi_get_feed_template_part('elements/item/hover-username', $settings);
				include sbi_get_feed_template_part('elements/item/hover-caption', $settings);
				?>

			</div>

			<?php if ($customizer || SB_Instagram_Display_Elements_Pro::should_show_element('hoverinstagram', $settings)) : ?>
				<a class="sbi_instagram_link" target="_blank" rel="nofollow noopener"
				   href="<?php echo esc_url($permalink); ?>" <?php echo SB_Instagram_Display_Elements_Pro::get_hoverinstagram_data_attributes($settings); ?><?php echo $hover_styles; ?>>
					<span class="sbi-screenreader">
						<?php esc_html_e('View Instagram post by ' . esc_html($username), 'instagram-feed'); ?>
					</span>
					<?php echo SB_Instagram_Display_Elements_Pro::get_icon('instagram', 'svg'); ?>
				</a>
			<?php endif; ?>

			<div class="sbi_hover_bottom <?php echo esc_attr($no_caption_class); ?>" <?php echo $hover_styles; ?>>

				<?php if ($customizer || ($timestamp > 0 && $show_hover_date)) : ?>
					<p>
						<?php if ($customizer || (!$feedtheme || $feedtheme == 'default_theme') && ($timestamp > 0 && $show_hover_date)) : ?>
							<span
								class="sbi_date" <?php echo SB_Instagram_Display_Elements_Pro::get_hoverdate_data_attributes($settings); ?> <?php echo SB_Instagram_Display_Elements_Pro::hover_date_atts($settings); ?>>
								<?php echo SB_Instagram_Display_Elements_Pro::get_icon('date', 'svg'); ?>
								<?php echo esc_html($posted_on_date_str); ?>
							</span>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ($comment_or_like_counts_data_exists && ($customizer || SB_Instagram_Display_Elements_Pro::should_show_element('hoverlikes', $settings))) : ?>
					<div
						class="sbi_meta" <?php echo SB_Instagram_Display_Elements_Pro::get_hoverlikes_data_attributes($settings); ?>>

						<?php
						/**
						 * Hover likes and comments
						 */
						include sbi_get_feed_template_part('elements/item/hover-likes', $settings);
						include sbi_get_feed_template_part('elements/item/hover-comments', $settings);
						?>
					</div>
				<?php endif; ?>

			</div>

			<a class="sbi_link_area nofancybox" rel="nofollow noopener" href="<?php echo esc_url($media_full_res); ?>"
			   data-lightbox-sbi=""
			   data-title="<?php echo str_replace('&lt;br /&gt;', '&lt;br&gt;', esc_attr(nl2br($caption))); ?>"
			   data-video="<?php echo esc_attr($lightbox_media_atts['video']); ?>"
			   data-carousel="<?php echo esc_attr($lightbox_media_atts['carousel']); ?>"
			   data-id="sbi_<?php echo esc_attr($post_id); ?>" data-user="<?php echo esc_attr($username); ?>"
			   data-url="<?php echo esc_attr($permalink); ?>" data-avatar="<?php echo esc_attr($avatar); ?>"
			   data-account-type="<?php echo esc_attr($account_type); ?>"
			   data-iframe='<?php echo esc_url($media_iframe_url); ?>'
			   data-media-type="<?php echo esc_attr($media_product_type); ?>"
			   data-posted-on="<?php echo esc_attr($media_posted_on); ?>"
			   data-custom-avatar="<?php echo esc_attr($custom_avatar); ?>">
				<span class="sbi-screenreader">
					<?php esc_html_e('Open post by ' . esc_html($username) . ' with ID ' . esc_html($post_id), 'instagram-feed'); ?>
				</span>
				<?php echo $maybe_video_icon; ?>
			</a>
		</div>

		<a class="sbi_photo" target="_blank" rel="nofollow noopener" href="<?php echo esc_url($permalink); ?>"
		   data-full-res="<?php echo esc_url($media_full_res); ?>"
		   data-img-src-set="<?php echo esc_attr(sbi_json_encode($media_all_sizes_json)); ?>"
		   tabindex="-1">
			<img src="<?php echo esc_url($media_url); ?>" alt="<?php echo esc_attr($img_alt); ?>">
		</a>
	</div>

<?php
