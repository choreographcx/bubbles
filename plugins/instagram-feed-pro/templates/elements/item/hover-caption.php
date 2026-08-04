<?php

/**
 * Hover caption
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
// style="color: rgba(153,231,255,1)" already escaped.
$hover_styles = SB_Instagram_Display_Elements_Pro::get_hover_styles($settings);
$caption = SB_Instagram_Parse_Pro::get_caption($post);
$sanitized_caption = SB_Instagram_Display_Elements_Pro::sanitize_caption($caption);

if ($customizer || SB_Instagram_Display_Elements_Pro::should_show_element('hovercaption', $settings)) : ?>
	<p class="sbi_hover_caption_wrap"
		<?php echo SB_Instagram_Display_Elements_Pro::get_hovercaption_data_attributes($settings, $caption); ?>>
		<span
			class="sbi_caption"<?php echo $hover_styles; ?>><?php echo str_replace('&lt;br&gt;', '<br>', esc_html(nl2br($sanitized_caption))); ?></span>
	</p>
<?php endif;
