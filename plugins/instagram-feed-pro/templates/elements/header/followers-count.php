<?php

/**
 * Instagram Feed Pro Header Followers Count
 *
 * @version 6.0 Custom Feeds for Instagram Pro by Smash Balloon
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$follower_count = SB_Instagram_Parse_Pro::get_follower_count($header_data);
$show_followers = SB_Instagram_Display_Elements_Pro::should_show_header_followers($settings, 'showfollowers');
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;
$follower_themes_array = SB_Instagram_Display_Elements_Pro::follower_theme_types($follower_count, $header_text_color_style);

if ($follower_count !== '' && SB_Instagram_Display_Elements_Pro::should_show_element('headerfollowers', $settings)) :
	foreach ($follower_themes_array as $theme_item) :
		$header_text_color_style_this = $header_text_color_style;
		if (!empty($theme_item['no_colors'])) {
			$header_text_color_style_this = '';
		}

		if (SB_Instagram_Display_Elements_Pro::should_show_stats_element($theme_item['type'], $settings)) : ?>
			<span
				class="sbi_followers" <?php echo $show_followers; ?><?php echo $header_text_color_style_this; ?>
				aria-label="<?php echo esc_attr($follower_count . ' followers'); ?>" 
				<?php echo SB_Instagram_Display_Elements_Pro::stats_atts($theme_item['type'], $settings); ?>>
				<?php echo $theme_item['display']; ?>
			</span>
		<?php endif;
	endforeach;
endif;
