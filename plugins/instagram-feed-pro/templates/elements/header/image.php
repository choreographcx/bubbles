<?php

/**
 * Instagram Feed Pro Header Image
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$header_image_atts = SB_Instagram_Display_Elements_Pro::get_header_img_data_attributes($settings, $header_data);
$avatar = SB_Instagram_Parse_Pro::get_avatar($header_data, $settings);
$avatar_el_atts = SB_Instagram_Display_Elements_Pro::get_avatar_element_data_attributes($settings, $header_data);

?>
	<div class="sbi_header_img" <?php echo $header_image_atts; ?>>
		<?php if ($avatar !== '' || $customizer) : ?>
			<div class="sbi_header_img_hover">
				<?php echo SB_Instagram_Display_Elements_Pro::get_icon('newlogo', 'svg'); ?>
			</div>
			<img <?php echo $avatar_el_atts; ?> width="84" height="84">
		<?php else : ?>
			<div class="sbi_header_hashtag_icon">
				<?php echo SB_Instagram_Display_Elements_Pro::get_icon('newlogo', 'svg'); ?>
			</div>
		<?php endif; ?>
	</div>
<?php
