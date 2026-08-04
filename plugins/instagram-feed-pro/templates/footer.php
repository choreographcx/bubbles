<?php

/**
 * Instagram Feed Footer Template
 * Adds pagination and html for errors and resized images
 *
 * @version 6.0 Instagram Feed Pro by Smash Balloon
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

// style="background: rgb();color: rgb();" already escaped.
$follow_btn_style = SB_Instagram_Display_Elements_Pro::get_follow_styles($settings);
$follow_btn_classes = strpos($follow_btn_style, 'background') !== false ? ' sbi_custom' : '';
$show_follow_button = $settings['showfollow'];
$follow_button_text = $settings['followtext'];

// style="background: rgb();color: rgb();"  already escaped.
$load_btn_style = SB_Instagram_Display_Elements_Pro::get_load_button_styles($settings);
$load_btn_classes = strpos($load_btn_style, 'background') !== false ? ' sbi_custom' : '';
$load_button_text = $settings['buttontext'];

$footer_attributes = SB_Instagram_Display_Elements_Pro::get_footer_attributes($settings);

?>
<div id="sbi_load" <?php echo $footer_attributes; ?>>

	<?php if ($use_pagination || sbi_doing_customizer($settings)) : ?>
		<button class="sbi_load_btn"
				type="button" <?php echo $load_btn_style; ?><?php echo SB_Instagram_Display_Elements_Pro::get_button_data_attributes($settings); ?>>
			<?php if (!empty($settings['feedtheme']) && $settings['feedtheme'] != 'default_theme') : ?>
				<span
					class="sbi-loadmore-icon" <?php echo SB_Instagram_Display_Elements_Pro::theme_condition($settings, '$parent.customizerFeedData.settings.feedtheme && $parent.customizerFeedData.settings.feedtheme != \'default_theme\''); ?>>
					<svg width="13" height="3" viewBox="0 0 13 3" fill="currentColor">
						<circle cx="1.75" cy="1.75" r="1.25" fill="currentColor"/>
						<circle cx="6.5" cy="1.75" r="1.25" fill="currentColor"/>
						<circle cx="11.25" cy="1.75" r="1.25" fill="currentColor"/>
					</svg>
				</span>
			<?php endif; ?>
			<span
				class="sbi_btn_text" <?php echo SB_Instagram_Display_Elements_Pro::get_button_attribute($settings); ?>><?php echo esc_html($load_button_text); ?></span>
			<span class="sbi_loader sbi_hidden" style="background-color: rgb(255, 255, 255);" aria-hidden="true"></span>
		</button>
	<?php endif; ?>

	<?php if (($first_username && $show_follow_button) || sbi_doing_customizer($settings)) : ?>
		<span
			class="sbi_follow_btn<?php echo esc_attr($follow_btn_classes); ?>" <?php echo SB_Instagram_Display_Elements_Pro::get_follow_data_attributes($settings); ?>>
			<a target="_blank"
			   rel="nofollow noopener" <?php echo SB_Instagram_Display_Elements_Pro::get_header_link($settings, $first_username) ?><?php echo $follow_btn_style; ?>>
				<?php echo SB_Instagram_Display_Elements::get_icon('instagram', 'svg'); ?>
				<span<?php echo SB_Instagram_Display_Elements_Pro::get_follow_attribute($settings); ?>><?php echo esc_html($follow_button_text); ?></span>
			</a>
		</span>
	<?php endif; ?>

</div>
