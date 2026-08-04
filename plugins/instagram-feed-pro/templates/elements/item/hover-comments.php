<?php

/**
 * Comments Template
 *
 * @version 6.3 Custom Feeds for Instagram Pro by Smash Balloon
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$sbi_meta_hover_styles = SB_Instagram_Display_Elements_Pro::get_sbi_meta_hover_styles($settings);
$comments_count = SB_Instagram_Parse_Pro::get_comments_count($post);

/*
 * Default Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('default_theme', $settings)) : ?>
	<span
		class="sbi_comments"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('default_theme', $settings); ?>>
		<?php echo SB_Instagram_Display_Elements_Pro::get_icon('comments', 'svg', $sbi_meta_hover_styles); ?>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;

/*
 * Modern Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('modern', $settings)) : ?>
	<span
		class="sbi_comments"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('modern', $settings); ?>>
		<svg <?php echo $sbi_meta_hover_styles; ?> viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.5 20.4531C16.6421 20.4531 20 17.0953 20 12.9531C20 8.81099 16.6421 5.45312 12.5 5.45312C8.35786 5.45312 5 8.81099 5 12.9531C5 14.6417 5.55805 16.2 6.49978 17.4536L5.21708 20.019C5.11735 20.2184 5.2624 20.4531 5.48541 20.4531H12.5Z" fill="currentColor"/></svg>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;

/*
 * Social Wall Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('social_wall', $settings)) : ?>
	<span
		class="sbi_comments"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('social_wall', $settings); ?>>
		<svg
			<?php echo $sbi_meta_hover_styles; ?>
			viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><rect y="0.500488" width="20" height="20" rx="10" fill="currentColor"></rect><path d="M6.00332 16.0884C6.61281 16.0884 8.07659 15.444 8.97583 14.8095C9.07075 14.7396 9.15069 14.7146 9.23062 14.7196C9.29057 14.7196 9.35052 14.7246 9.40547 14.7246C13.0125 14.7246 15.5703 12.7462 15.5703 10.0735C15.5703 7.49563 12.9975 5.42236 9.78516 5.42236C6.57285 5.42236 4 7.49563 4 10.0735C4 11.6821 4.96919 13.1159 6.60282 14.0002C6.69774 14.0501 6.72272 14.1251 6.67276 14.215C6.383 14.6946 5.89841 15.2391 5.69858 15.4939C5.47876 15.7737 5.60366 16.0884 6.00332 16.0884Z" fill="white"></path>
		</svg>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;

/*
 * Outline Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('outline', $settings)) : ?>
	<span
		class="sbi_comments"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('outline', $settings); ?>>
		<svg <?php echo $sbi_meta_hover_styles; ?> viewBox="0 0 32 33" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M27.9998 2.90039C28.8835 2.90039 29.5998 3.61674 29.5998 4.50039V19.7071C29.5998 21.0231 28.0995 21.7763 27.0441 20.9903L24.0004 18.7233V16.7283L27.9998 19.7071V4.50039H10.3998V10.1006H8.7998V4.50039C8.7998 3.61673 9.51615 2.90039 10.3998 2.90039H27.9998Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M2.40039 10.9008C2.40039 10.0171 3.11673 9.30078 4.00039 9.30078H23.2004C24.084 9.30078 24.8004 10.0171 24.8004 10.9008V23.7008C24.8004 24.5844 24.084 25.3008 23.2004 25.3008H9.86706L4.96039 28.9808C3.90561 29.7719 2.40039 29.0193 2.40039 27.7008V10.9008ZM23.2004 10.9008L4.00039 10.9008L4.00039 27.7008L8.90706 24.0208C9.18401 23.8131 9.52087 23.7008 9.86706 23.7008H23.2004V10.9008Z" fill="currentColor"/><circle cx="8.4002" cy="16.8992" r="1.2" fill="currentColor"/><ellipse cx="13.2" cy="16.8992" rx="1.2" ry="1.2" fill="currentColor"/><ellipse cx="17.9998" cy="16.8992" rx="1.2" ry="1.2" fill="currentColor"/></svg>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;

/*
 * Overlap Theme
 */
?>
<?php if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('overlap', $settings)) : ?>
	<span
		class="sbi_comments"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('overlap', $settings); ?>>
		<svg <?php echo $sbi_meta_hover_styles; ?> viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 10.7518C2 6.86322 5.15228 3.71094 9.04082 3.71094H14.9592C18.8477 3.71094 22 6.86322 22 10.7518C22 14.6403 18.8477 17.7926 14.9592 17.7926H13.9024C13.8268 17.7926 13.7534 17.818 13.694 17.8647L9.50449 21.1564C8.99365 21.5578 8.2557 21.1241 8.35777 20.4825L8.73609 18.1045C8.76214 17.9408 8.63564 17.7926 8.46986 17.7926C4.89665 17.7926 2 14.8959 2 11.3227V10.7518Z" fill="currentColor"/><circle cx="7.71373" cy="10.8544" r="1.42857" fill="#141B38"/><circle cx="12.0008" cy="10.8544" r="1.42857" fill="#141B38"/><circle cx="16.284" cy="10.8544" r="1.42857" fill="#141B38"/></svg>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;
