<?php
/**
 * The template for displaying the header text
 *
 * @package instagram-feed-pro
 * @since INSTA_FEED_PRO_SINCE
 *
 * @var array $settings
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$header_atts = SB_Instagram_Display_Elements_Pro::get_header_data_attributes('text', $settings, array());
$header_text = SB_Instagram_Display_Elements_Pro::get_header_text($settings);
$header_text_style = SB_Instagram_Display_Elements_Pro::get_header_text_style($settings);
$header_text_atts = SB_Instagram_Display_Elements_Pro::get_header_text_data_attributes($settings);
$outside_class = !empty($settings['headeroutside']) ? ' sbi_header_outside' : '';

?>

<div
	class="sbi-header sbi-header-type-text <?php echo esc_attr($outside_class); ?>" <?php echo $header_text_atts ?> <?php echo $header_atts ?> <?php echo $header_text_style ?>><?php echo esc_html($header_text); ?></div>
