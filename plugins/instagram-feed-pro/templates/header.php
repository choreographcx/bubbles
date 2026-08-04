<?php

/**
 * Instagram Feed Header Template
 * Adds account information and an avatar to the top of the feed
 *
 * @version 6.0 Instagram Feed Pro by Smash Balloon
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

// Elements.
$username = SB_Instagram_Parse_Pro::get_username($header_data);
$avatar = SB_Instagram_Parse_Pro::get_avatar($header_data, $settings);
$name = SB_Instagram_Parse_Pro::get_name($header_data);
$bio = SB_Instagram_Parse_Pro::get_bio($header_data, $settings);
$customizer = sbi_doing_customizer($settings);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;

// Attributes.
$header_atts = SB_Instagram_Display_Elements_Pro::get_header_data_attributes('standard-centered', $settings, $header_data);
$avatar_el_atts = SB_Instagram_Display_Elements_Pro::get_avatar_element_data_attributes($settings, $header_data);
$header_padding = (int)$settings['imagepadding'] > 0 ? 'padding: ' . (int)$settings['imagepadding'] . esc_attr($settings['imagepaddingunit']) . ';' : '';
$header_margin = (int)$settings['imagepadding'] < 10 ? ' margin-bottom: 10px;' : '';
// style="color: #517fa4;" already escaped.
$header_text_color_style = SB_Instagram_Display_Elements_Pro::get_header_text_color_styles($settings);
$header_classes = SB_Instagram_Display_Elements_Pro::get_header_class($settings, $avatar);
$header_heading_attribute = SB_Instagram_Display_Elements_Pro::get_header_heading_data_attributes($settings);
$should_show_bio = $bio !== '' && SB_Instagram_Display_Elements_Pro::should_show_element('headerbio', $settings);
$header_text_class = SB_Instagram_Display_Elements_Pro::get_header_text_class($header_data, $settings);
$header_info_bio_class = SB_Instagram_Display_Elements_Pro::get_feedtheme_info_bio_class($header_data, $settings);
$header_link = SB_Instagram_Display_Elements_Pro::get_header_link($settings, $username);
$header_link_title = SB_Instagram_Display_Elements_Pro::get_header_link_title($settings, $username);

?>
	<div<?php echo $header_classes; ?><?php echo $header_atts; ?>>
		<a class="sbi_header_link" target="_blank"
		   rel="nofollow noopener" <?php echo $header_link ?><?php echo $header_link_title ?>>
			<div<?php echo $header_text_class; ?>>
				<?php include sbi_get_feed_template_part('elements/header/image', $settings); ?>

				<div class="sbi_feedtheme_header_text">
					<div class="sbi_feedtheme_title_wrapper">
						<h3<?php echo $header_text_color_style . $header_heading_attribute; ?>>
							<?php echo esc_html($username); ?>
						</h3>

						<?php if ($customizer || $settings['headerstyle'] === 'centered' || !$feedtheme || in_array($feedtheme, ['default_theme', 'modern', 'overlap'])) : ?>
							<p class="sbi_bio_info"<?php echo $header_text_color_style; ?><?php echo SB_Instagram_Display_Elements_Pro::bio_info_atts($settings); ?>>
								<?php
								include sbi_get_feed_template_part('elements/header/posts-count', $settings);
								include sbi_get_feed_template_part('elements/header/followers-count', $settings);
								?>
							</p>
						<?php endif; ?>

					</div>

					<?php if ($should_show_bio) : ?>
						<?php include sbi_get_feed_template_part('elements/header/bio', $settings); ?>
					<?php endif; ?>
				</div>

			</div>

		</a>

		<?php if ($customizer || ($feedtheme && in_array($feedtheme, ['social_wall', 'outline']) && $settings['headerstyle'] !== 'centered')) : ?>
			<div <?php echo $header_info_bio_class; ?><?php echo $header_text_color_style; ?><?php echo SB_Instagram_Display_Elements_Pro::header_info_atts($settings); ?>>
				<?php
				include sbi_get_feed_template_part('elements/header/posts-count', $settings);
				include sbi_get_feed_template_part('elements/header/followers-count', $settings);
				?>
			</div>
		<?php endif; ?>
	</div>
<?php
