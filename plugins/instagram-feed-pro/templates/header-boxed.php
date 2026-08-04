<?php

/**
 * Custom Feeds for Instagram Header Boxed Template
 * Adds account information and an avatar to the top of the feed
 *
 * @version 6.0 Custom Feeds for Instagram Pro by Smash Balloon
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

// Elements.
$customizer = sbi_doing_customizer($settings);
$bio = SB_Instagram_Parse_Pro::get_bio($header_data, $settings);
$username = SB_Instagram_Parse_Pro::get_username($header_data);
$avatar = SB_Instagram_Parse_Pro::get_avatar($header_data, $settings);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;

// Attributes.
$header_atts = SB_Instagram_Display_Elements_Pro::get_header_data_attributes('boxed', $settings, $header_data);
$header_classes = SB_Instagram_Display_Elements_Pro::get_header_class($settings, $avatar, 'boxed');
// style="color: #517fa4;". Already escaped.
$header_text_color_style = SB_Instagram_Display_Elements_Pro::get_header_text_color_styles($settings);
$header_link = SB_Instagram_Display_Elements_Pro::get_header_link($settings, $username);
$header_link_title = SB_Instagram_Display_Elements_Pro::get_header_link_title($settings, $username);
$should_show_bio = $bio !== '' && SB_Instagram_Display_Elements_Pro::should_show_element('headerbio', $settings);
$bio_class = !$should_show_bio ? ' sbi_no_bio' : '';
$header_text_class = SB_Instagram_Display_Elements_Pro::get_header_text_class($header_data, $settings);
$bio_attribute = SB_Instagram_Display_Elements_Pro::get_bio_data_attributes($settings);

// Boxed Header Specific.
$has_info = $should_show_bio || SB_Instagram_Display_Elements_Pro::should_show_element('headerfollowers', $settings);
$info_class = !$has_info ? ' sbi_no_info' : '';
// style="background: #517fa4;" already escaped.
$header_style = SB_Instagram_Display_Elements_Pro::get_boxed_header_styles($settings);
// style="background: #eeeeee;" already escaped.
$header_bar_style = SB_Instagram_Display_Elements_Pro::get_header_bar_styles($settings);
// style="color: #517fa4;" already escaped.
$header_info_style = SB_Instagram_Display_Elements_Pro::get_header_info_styles($settings);
$header_info_bio_class = SB_Instagram_Display_Elements_Pro::get_feedtheme_info_bio_class($header_data, $settings);

?>
<div<?php echo $header_classes; ?><?php echo $header_style; ?><?php echo $header_atts; ?>>
	<a class="sbi_header_link" target="_blank"
	   rel="nofollow noopener"<?php echo $header_link; ?><?php echo $header_link_title; ?>>
		<div class="sbi_header_text<?php echo esc_attr($bio_class) . esc_attr($info_class); ?>">
			<?php include sbi_get_feed_template_part('elements/header/image', $settings); ?>

			<div class="sbi_feedtheme_header_text">
				<div class="sbi_feedtheme_title_wrapper">
					<h3 <?php echo $header_text_color_style; ?>>
						<?php echo esc_html($username); ?>
					</h3>

					<?php if ($customizer || ($feedtheme && in_array($feedtheme, ['modern', 'overlap']))) : ?>
						<div
							class="sbi_bio_info"<?php echo SB_Instagram_Display_Elements_Pro::theme_condition($settings, '$parent.customizerFeedData.settings.feedtheme && $parent.customizerFeedData.settings.feedtheme == \'modern\' || $parent.customizerFeedData.settings.feedtheme == \'overlap\''); ?>>

							<?php
							include sbi_get_feed_template_part('elements/header/posts-count', $settings);
							include sbi_get_feed_template_part('elements/header/followers-count', $settings);
							?>
						</div>
					<?php endif; ?>
				</div>

				<?php include sbi_get_feed_template_part('elements/header/bio', $settings); ?>
			</div>
		</div>
	</a>

	<?php if ($customizer || (!$feedtheme || $feedtheme == 'default_theme')) : ?>
		<div
			class="sbi_header_bar"<?php echo $header_bar_style; ?><?php echo SB_Instagram_Display_Elements_Pro::theme_condition($settings, '!$parent.customizerFeedData.settings.feedtheme || $parent.customizerFeedData.settings.feedtheme == \'default_theme\''); ?>>
			<p class="sbi_bio_info"<?php echo $header_info_style; ?>>
				<?php
				include sbi_get_feed_template_part('elements/header/posts-count', $settings);
				include sbi_get_feed_template_part('elements/header/followers-count', $settings);
				?>
			</p>
			<?php include sbi_get_feed_template_part('elements/header/follow-btn', $settings); ?>
		</div>
	<?php endif; ?>

	<?php if ($customizer || ($feedtheme && in_array($feedtheme, ['social_wall', 'outline']))) : ?>
		<div <?php echo $header_info_bio_class; ?><?php echo $header_info_style; ?><?php echo SB_Instagram_Display_Elements_Pro::theme_condition($settings, '$parent.customizerFeedData.settings.feedtheme == \'social_wall\' || $parent.customizerFeedData.settings.feedtheme == \'outline\''); ?>>
			<?php
			include sbi_get_feed_template_part('elements/header/posts-count', $settings);
			include sbi_get_feed_template_part('elements/header/followers-count', $settings);
			?>
		</div>
	<?php endif; ?>
</div>
