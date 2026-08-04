<?php

/**
 * Instagram Feed Pro Header Follow Button
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

// style="background: rgb();color: rgb();" already escaped.
$follow_btn_style = SB_Instagram_Display_Elements_Pro::get_follow_styles($settings);
$follow_btn_classes = strpos($follow_btn_style, 'background') !== false ? ' sbi_custom' : '';
$header_link = SB_Instagram_Display_Elements_Pro::get_header_link($settings, $username);
$follow_attribute = SB_Instagram_Display_Elements_Pro::get_follow_attribute($settings);
$follow_button_text = $settings['followtext'];

?>
	<a class="sbi_header_follow_btn<?php echo esc_attr($follow_btn_classes); ?>"
	   <?php echo $header_link; ?>target="_blank" rel="nofollow noopener"<?php echo $follow_btn_style; ?>>
		<?php echo SB_Instagram_Display_Elements_Pro::get_icon('instagram', 'svg'); ?>
		<span <?php echo $follow_attribute; ?>>
			<?php echo esc_html($follow_button_text); ?>
		</span>
	</a>
<?php
