<?php

/**
 * Instagram Feed Pro Header Bio
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$bio = SB_Instagram_Parse_Pro::get_bio($header_data, $settings);
$should_show_bio = $bio !== '' && SB_Instagram_Display_Elements_Pro::should_show_element('headerbio', $settings);
// style="color: #517fa4;".
$header_text_color_style = SB_Instagram_Display_Elements_Pro::get_header_text_color_styles($settings);
$bio_attribute = SB_Instagram_Display_Elements_Pro::get_bio_data_attributes($settings);

if ($should_show_bio) : ?>
	<p class="sbi_bio"<?php echo $header_text_color_style; ?><?php echo $bio_attribute; ?>>
		<?php echo str_replace('&lt;br /&gt;', '<br>', esc_html(nl2br($bio))); ?>
	</p>
<?php endif;
