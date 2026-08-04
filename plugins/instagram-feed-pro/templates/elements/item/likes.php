<?php

/**
 * Likes count
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
// style="font-size: 13px;color: rgba(153,231,255,1)" already escaped.
$sbi_meta_size_color_styles = SB_Instagram_Display_Elements_Pro::get_sbi_meta_size_color_styles($settings);
$likes_count = SB_Instagram_Parse_Pro::get_likes_count($post);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;

/*
 * Default Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('default_theme', $settings)) : ?>
	<span
		class="sbi_likes" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('default_theme', $settings); ?>
		aria-label="<?php echo esc_attr($likes_count . ' likes'); ?>">
		<span>
			<?php echo SB_Instagram_Display_Elements_Pro::get_icon('likes', 'svg', $sbi_meta_size_color_styles); ?>
		</span>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;

/*
 * Modern Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('modern', $settings)) : ?>
	<span
		class="sbi_likes" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('modern', $settings); ?>
		aria-label="<?php echo esc_attr($likes_count . ' likes'); ?>">
		<span>
			<svg <?php echo $sbi_meta_size_color_styles; ?> viewBox="0 0 16 17" fill="none"
															xmlns="http://www.w3.org/2000/svg">
				<path
					d="M13.722 4.80473C13.2105 3.75509 11.7372 2.89629 10.0234 3.3963C9.20448 3.63285 8.48998 4.14013 7.99664 4.83526C7.50331 4.14013 6.78881 3.63285 5.96988 3.3963C4.25229 2.90392 2.78279 3.75509 2.27133 4.80473C1.55376 6.27422 1.85147 7.92693 3.15684 9.71705C4.17977 11.1178 5.64163 12.5377 7.76381 14.1866C7.83088 14.2389 7.9135 14.2673 7.99855 14.2673C8.0836 14.2673 8.16622 14.2389 8.23329 14.1866C10.3517 12.5415 11.8173 11.1331 12.8403 9.71705C14.1418 7.92693 14.4395 6.27422 13.722 4.80473V4.80473Z"
					stroke="currentColor" stroke-linejoin="round"/>
			</svg>
		</span>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;

/*
 * Social Wall Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('social_wall', $settings)) : ?>
	<span
		class="sbi_likes" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('social_wall', $settings); ?>
		aria-label="<?php echo esc_attr($likes_count . ' likes'); ?>">
		<span>
			<svg <?php echo $sbi_meta_size_color_styles; ?> viewBox="0 0 20 21" fill="none"
															xmlns="http://www.w3.org/2000/svg">
				<rect y="0.500488"
					  width="20"
					  height="20" rx="10"
					  fill="currentColor"/>
				<path
					d="M15.0615 7.87517C14.6092 6.94708 13.3065 6.18774 11.7912 6.62985C11.0671 6.839 10.4354 7.28753 9.99918 7.90217C9.56298 7.28753 8.93122 6.839 8.20713 6.62985C6.68845 6.19449 5.38913 6.94708 4.9369 7.87517C4.30242 9.17449 4.53116 10.6615 5.71987 12.2186C6.6413 13.4256 8.90554 15.4427 9.7108 16.1469C9.87757 16.2927 10.1238 16.2934 10.2909 16.1479C11.1139 15.4311 13.4577 13.3521 14.2819 12.2186C15.4327 10.6358 15.6959 9.17449 15.0615 7.87517Z"
					fill="white"/>
			</svg>
		</span>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;

/*
 * Outline Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('outline', $settings)) : ?>
	<span
		class="sbi_likes" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('outline', $settings); ?>
		aria-label="<?php echo esc_attr($likes_count . ' likes'); ?>">
		<span>
			<svg <?php echo $sbi_meta_size_color_styles; ?> viewBox="0 0 20 21" fill="none"
															xmlns="http://www.w3.org/2000/svg">
				<path
					d="M14.6949 5.0918C14.0766 5.09171 13.4674 5.24261 12.919 5.53171C12.3706 5.8208 11.8992 6.23955 11.5449 6.7524C11.2447 6.31786 10.8605 5.95089 10.4165 5.67309C10.3365 5.62302 10.2546 5.57585 10.1708 5.53171C9.62246 5.24261 9.01324 5.09171 8.39492 5.0918C7.37384 5.0918 6.39458 5.50232 5.67256 6.23304C4.95055 6.96377 4.54492 7.95485 4.54492 8.98825C4.54492 13.0399 9.09492 16.383 11.5449 18.0918C13.9949 16.383 18.5449 13.0399 18.5449 8.98825C18.5449 8.47656 18.4453 7.96988 18.2519 7.49714C18.0584 7.0244 17.7748 6.59486 17.4173 6.23304C17.2071 6.02034 16.9738 5.8335 16.7223 5.67583C16.546 5.56529 16.3608 5.4691 16.1683 5.3884C15.7011 5.19258 15.2005 5.0918 14.6949 5.0918Z"
					fill="white" stroke="currentColor" stroke-width="1.1"/>
				<path
					d="M9 16.502C6.55 14.7932 2 11.45 2 7.39844L4.54414 8.9882C4.54414 13.0398 9.09414 16.383 11.5441 18.0917L9 16.502Z"
					fill="white" stroke="currentColor" stroke-width="1.1" stroke-linejoin="round"/>
				<path
					d="M12.15 3.50195C11.5317 3.50186 10.9225 3.65277 10.3741 3.94186C9.82569 4.23096 9.35432 4.64971 9 5.16255C8.64568 4.64971 8.17431 4.23096 7.62592 3.94186C7.07754 3.65277 6.46832 3.50186 5.85 3.50195C4.82892 3.50195 3.84965 3.91247 3.12764 4.6432C2.40562 5.37393 2 6.36501 2 7.39841C2 11.45 6.55 14.7932 9 16.502C11.45 14.7932 16 11.45 16 7.39841C16 6.88672 15.9004 6.38004 15.7069 5.9073C15.5135 5.43456 15.2299 5.00502 14.8724 4.6432C14.5149 4.28138 14.0904 3.99437 13.6233 3.79855C13.1562 3.60274 12.6556 3.50195 12.15 3.50195Z"
					fill="white" stroke="currentColor" stroke-width="1.1"/>
			</svg>
		</span>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;

/*
 * Overlap Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('overlap', $settings)) : ?>
	<span
		class="sbi_likes sbi-dark-text" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('overlap', $settings); ?>
		aria-label="<?php echo esc_attr($likes_count . ' likes'); ?>">
		<span>
			<svg <?php echo $sbi_meta_size_color_styles; ?>
				viewBox="0 0 20 21" fill="none"
				xmlns="http://www.w3.org/2000/svg">
				<g
					clip-path="url(#clip0_2402_8737)">
					<path
						d="M16.5539 5.7816C15.9685 4.58011 14.282 3.59707 12.3203 4.16942C11.3829 4.44019 10.5651 5.02085 10.0003 5.81655C9.43564 5.02085 8.61777 4.44019 7.68038 4.16942C5.7143 3.60581 4.03222 4.58011 3.44676 5.7816C2.62538 7.46368 2.9215 9.38881 4.46038 11.4046C5.65326 12.9671 8.58452 15.5784 9.62701 16.49C9.8429 16.6788 10.1617 16.6797 10.378 16.4914C11.4434 15.5634 14.4777 12.872 15.5447 11.4046C17.0345 9.35547 17.3753 7.46368 16.5539 5.7816Z"
						fill="currentColor"/>
				</g>
				<defs>
					<clipPath id="clip0_2402_8737">
						<rect y="0.501953" width="20"
							  height="20" rx="1"
							  fill="white"/>
					</clipPath>
				</defs>
			</svg>
		</span>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;
