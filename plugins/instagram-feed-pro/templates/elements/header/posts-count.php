<?php

/**
 * Instagram Feed Pro Header Posts Count
 *
 * @version 6.0 Custom Feeds for Instagram Pro by Smash Balloon
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$post_count = SB_Instagram_Parse_Pro::get_post_count($header_data);
// style="color: #517fa4;" already escaped.
$header_text_color_style = SB_Instagram_Display_Elements_Pro::get_header_text_color_styles($settings);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;

foreach (SB_Instagram_Display_Elements_Pro::post_count_theme_types($post_count, $header_text_color_style) as $theme_item) :
	$header_text_color_style_this = $header_text_color_style;
	if (!empty($theme_item['no_colors'])) {
		$header_text_color_style_this = '';
	}

	if (SB_Instagram_Display_Elements_Pro::should_show_stats_element($theme_item['type'], $settings)) : ?>
		<span
			class="sbi_posts_count" <?php echo $header_text_color_style; ?>
			aria-label="<?php echo esc_attr($post_count . ' posts'); ?>"
			<?php echo SB_Instagram_Display_Elements_Pro::stats_atts($theme_item['type'], $settings); ?>>
			<?php echo $theme_item['display']; ?>
		</span>
	<?php endif;
endforeach;
