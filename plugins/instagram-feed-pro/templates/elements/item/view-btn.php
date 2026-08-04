<?php

/**
 * View on Instagram button
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;
$username = SB_Instagram_Parse_Pro::get_username($post);

if (SB_Instagram_Display_Elements_Pro::should_show_element('view_button', $settings)) : ?>
	<div class="sbi-instagram-link-btn"<?php echo SB_Instagram_Display_Elements_Pro::instagram_view_atts($settings); ?>>
		<a href="<?php echo esc_url('https://www.instagram.com/' . $username . '/'); ?>" target="_blank"
		   rel="nofollow noopener">
			<?php echo SB_Instagram_Display_Elements_Pro::get_icon('view_instagram', 'svg'); ?>
			<span><?php esc_html_e('View on Instagram', 'instagram-feed'); ?></span>
		</a>
	</div>
<?php endif;
