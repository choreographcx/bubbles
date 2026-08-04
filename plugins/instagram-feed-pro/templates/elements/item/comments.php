<?php

/**
 * Comments count
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
// style="font-size: 13px;color: rgba(153,231,255,1)" already escaped.
$sbi_meta_size_color_styles = SB_Instagram_Display_Elements_Pro::get_sbi_meta_size_color_styles($settings);
$comments_count = SB_Instagram_Parse_Pro::get_comments_count($post);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;

/*
 * Default Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('default_theme', $settings)) : ?>
	<span
		class="sbi_comments" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('default_theme', $settings); ?>
		aria-label="<?php echo esc_attr($comments_count . ' comments'); ?>">
		<span>
			<?php echo SB_Instagram_Display_Elements_Pro::get_icon('comments', 'svg', $sbi_meta_size_color_styles); ?>
		</span>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;

/*
 * Modern Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('modern', $settings)) : ?>
	<span
		class="sbi_comments" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('modern', $settings); ?>
		aria-label="<?php echo esc_attr($comments_count . ' comments'); ?>">
		<span>
			<svg <?php echo $sbi_meta_size_color_styles; ?> viewBox="0 0 16 17" fill="none">
				<path
					d="M3.99985 11.5003L4.44707 11.7239C4.53183 11.5544 4.51345 11.3515 4.39961 11.2L3.99985 11.5003ZM3 13.5L2.55279 13.2764C2.47529 13.4314 2.48357 13.6155 2.57467 13.7629C2.66578 13.9103 2.82671 14 3 14V13.5ZM12.5 8.5C12.5 10.9853 10.4853 13 8 13V14C11.0376 14 13.5 11.5376 13.5 8.5H12.5ZM8 4C10.4853 4 12.5 6.01472 12.5 8.5H13.5C13.5 5.46243 11.0376 3 8 3V4ZM3.5 8.5C3.5 6.01472 5.51472 4 8 4V3C4.96243 3 2.5 5.46243 2.5 8.5H3.5ZM4.39961 11.2C3.83461 10.4479 3.5 9.51374 3.5 8.5H2.5C2.5 9.73774 2.90945 10.8813 3.60009 11.8006L4.39961 11.2ZM3.44721 13.7236L4.44707 11.7239L3.55264 11.2767L2.55279 13.2764L3.44721 13.7236ZM8 13H3V14H8V13Z"
					fill="currentColor"/>
			</svg>
		</span>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;

/*
 * Social Wall
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('social_wall', $settings)) : ?>
	<span
		class="sbi_comments" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('social_wall', $settings); ?>
		aria-label="<?php echo esc_attr($comments_count . ' comments'); ?>">
		<span>
			<svg <?php echo $sbi_meta_size_color_styles; ?> viewBox="0 0 20 21" fill="none">
				<rect y="0.500488" width="20" height="20" rx="10" fill="currentColor"/>
				<path
					d="M6.00332 16.0884C6.61281 16.0884 8.07659 15.444 8.97583 14.8095C9.07075 14.7396 9.15069 14.7146 9.23062 14.7196C9.29057 14.7196 9.35052 14.7246 9.40547 14.7246C13.0125 14.7246 15.5703 12.7462 15.5703 10.0735C15.5703 7.49563 12.9975 5.42236 9.78516 5.42236C6.57285 5.42236 4 7.49563 4 10.0735C4 11.6821 4.96919 13.1159 6.60282 14.0002C6.69774 14.0501 6.72272 14.1251 6.67276 14.215C6.383 14.6946 5.89841 15.2391 5.69858 15.4939C5.47876 15.7737 5.60366 16.0884 6.00332 16.0884Z"
					fill="white"/>
			</svg>
		</span>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;

/*
 * Outline Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('outline', $settings)) : ?>
	<span
		class="sbi_comments" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('outline', $settings); ?>
		aria-label="<?php echo esc_attr($comments_count . ' comments'); ?>">
		<span>
			<svg <?php echo $sbi_meta_size_color_styles; ?> viewBox="0 0 20 21" fill="none">
				<path
					d="M18 3.00195C18 2.72581 17.7761 2.50195 17.5 2.50195H6.5C6.22386 2.50195 6 2.72581 6 3.00195V10.252C6 10.5281 6.22386 10.752 6.5 10.752H14.142C14.2496 10.752 14.3543 10.7867 14.4406 10.851L17.2013 12.9071C17.5312 13.1528 18 12.9174 18 12.5061V10.752V3.00195Z"
					stroke="currentColor" stroke-width="1.1"/>
				<path
					d="M2 7.00195C2 6.72581 2.22386 6.50195 2.5 6.50195H14.5C14.7761 6.50195 15 6.72581 15 7.00195V15.002C15 15.2781 14.7761 15.502 14.5 15.502H6.16667C6.05848 15.502 5.95321 15.537 5.86667 15.602L2.8 17.902C2.47038 18.1492 2 17.914 2 17.502V15.502V7.00195Z"
					fill="white" stroke="currentColor" stroke-width="1.1"/>
				<circle cx="5.25" cy="10.752" r="0.75" fill="currentColor"/>
				<circle cx="8.25" cy="10.752" r="0.75" fill="currentColor"/>
				<circle cx="11.25" cy="10.752" r="0.75" fill="currentColor"/>
			</svg>
		</span>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;

/*
 * Overlap Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('overlap', $settings)) : ?>
	<span
		class="sbi_comments sbi-dark-text" <?php echo $sbi_meta_size_color_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('overlap', $settings); ?>
		aria-label="<?php echo esc_attr($comments_count . ' comments'); ?>">
		<svg <?php echo $sbi_meta_size_color_styles; ?> viewBox="0 0 20 21" fill="none">
			<g clip-path="url(#clip0_2402_8742)">
				<path
					d="M2 9.20687C2 6.09604 4.52183 3.57422 7.63266 3.57422H12.3673C15.4782 3.57422 18 6.09604 18 9.20687C18 12.3177 15.4782 14.8395 12.3673 14.8395H11.5219C11.4614 14.8395 11.4027 14.8598 11.3552 14.8972L8.00359 17.5306C7.59492 17.8517 7.00456 17.5047 7.08622 16.9915L7.38887 15.0891C7.40971 14.9581 7.30851 14.8395 7.17589 14.8395C4.31732 14.8395 2 12.5222 2 9.66364V9.20687Z"
					fill="currentColor"/>
				<circle cx="6.57059" cy="9.28739" r="1.14286" fill="white"/>
				<circle cx="10.0003" cy="9.28739" r="1.14286" fill="white"/>
				<circle cx="13.428" cy="9.28739" r="1.14286" fill="white"/>
			</g>
			<defs>
				<clipPath id="clip0_2402_8742">
					<rect y="0.501953" width="20" height="20" rx="1" fill="white"/>
				</clipPath>
			</defs>
		</svg>
		<?php echo esc_html($comments_count); ?>
	</span>
<?php endif;
