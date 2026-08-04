<?php

/**
 * Username hover
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$username = SB_Instagram_Parse_Pro::get_username($post);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;
$timestamp = SB_Instagram_Parse_Pro::get_timestamp($post);

$posted_on_date_str = ucfirst(date_i18n('M j', $timestamp)); // ex. Feb 2.
$posted_on_date_str = apply_filters('sbi_posted_on_date', $posted_on_date_str, $timestamp);

if ($customizer || SB_Instagram_Display_Elements_Pro::should_show_element('hoverusername', $settings)) : ?>
	<p class="sbi_username" <?php echo SB_Instagram_Display_Elements_Pro::get_hoverusername_data_attributes($settings); ?>>
		<?php if (!empty($username)) : ?>
			<a target="_blank" rel="nofollow noopener"
				href="<?php echo esc_url('https://www.instagram.com/' . $username . '/'); ?>" <?php echo $hover_styles; ?>
				tabindex="-1">
				<?php if (SB_Instagram_Display_Elements_Pro::should_show_element('inner_username_span', $settings)) : ?>
					<span<?php echo SB_Instagram_Display_Elements_Pro::default_exclusion_atts($settings); ?>>@</span>
				<?php endif; ?>

					<?php echo esc_html($username); ?>
			</a>
		<?php endif; ?>

		<?php if (SB_Instagram_Display_Elements_Pro::should_show_element('hover_top_inner', $settings)) : ?>
			<span
				class="sbi-hover-top-inner" <?php echo SB_Instagram_Display_Elements_Pro::default_exclusion_atts($settings); ?><?php echo $hover_styles; ?>>
				<span class="sbi-separator"> . </span>
				<span class="sbi_username-date">
					<?php echo esc_html($posted_on_date_str); ?>
				</span>
			</span>
		<?php endif; ?>
	</p>
<?php endif;
