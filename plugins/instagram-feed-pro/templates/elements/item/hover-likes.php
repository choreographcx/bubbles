<?php

/**
 * Likes Template
 *
 * @version 6.3 Custom Feeds for Instagram Pro by Smash Balloon
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	die('-1');
}

$customizer = sbi_doing_customizer($settings);
$sbi_meta_hover_styles = SB_Instagram_Display_Elements_Pro::get_sbi_meta_hover_styles($settings);
$likes_count = SB_Instagram_Parse_Pro::get_likes_count($post);
$feedtheme = !empty($settings['feedtheme']) ? $settings['feedtheme'] : false;

/*
 * Default Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('default_theme', $settings)) : ?>
	<span
		class="sbi_likes"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('default_theme', $settings); ?>>
		<?php echo SB_Instagram_Display_Elements_Pro::get_icon('likes', 'svg', $sbi_meta_hover_styles); ?>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;

/*
 * Modern Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('modern', $settings)) : ?>
	<span
		class="sbi_likes"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('modern', $settings); ?>>
		<svg <?php echo $sbi_meta_hover_styles; ?> viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path
				d="M20.5829 6.53131C19.8157 4.95685 17.6058 3.66865 15.0351 4.41867C13.8067 4.77349 12.735 5.53441 11.995 6.57711C11.255 5.53441 10.1832 4.77349 8.95482 4.41867C6.37843 3.6801 4.17419 4.95685 3.40699 6.53131C2.33064 8.73555 2.77721 11.2146 4.73527 13.8998C6.26965 16.001 8.46245 18.1308 11.6457 20.6041C11.7463 20.6826 11.8702 20.7252 11.9978 20.7252C12.1254 20.7252 12.2493 20.6826 12.3499 20.6041C15.5275 18.1365 17.726 16.0239 19.2604 13.8998C21.2127 11.2146 21.6593 8.73555 20.5829 6.53131Z"
				fill="currentColor"/></svg>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;

/*
 * Social Wall Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('social_wall', $settings)) : ?>
	<span
		class="sbi_likes"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('social_wall', $settings); ?>>
		<svg <?php echo $sbi_meta_hover_styles; ?> viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><rect
				y="0.500488" width="20" height="20" rx="10" fill="currentColor"/><path
				d="M15.0615 7.87517C14.6092 6.94708 13.3065 6.18774 11.7912 6.62985C11.0671 6.839 10.4354 7.28753 9.99918 7.90217C9.56298 7.28753 8.93122 6.839 8.20713 6.62985C6.68845 6.19449 5.38913 6.94708 4.9369 7.87517C4.30242 9.17449 4.53116 10.6615 5.71987 12.2186C6.6413 13.4256 8.90554 15.4427 9.7108 16.1469C9.87757 16.2927 10.1238 16.2934 10.2909 16.1479C11.1139 15.4311 13.4577 13.3521 14.2819 12.2186C15.4327 10.6358 15.6959 9.17449 15.0615 7.87517Z"
				fill="white"/></svg>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;

/*
 * Outline Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('outline', $settings)) : ?>
	<span
		class="sbi_likes"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('outline', $settings); ?>>
		<svg <?php echo $sbi_meta_hover_styles; ?> viewBox="0 0 32 33" fill="none" xmlns="http://www.w3.org/2000/svg"><path
				fill-rule="evenodd" clip-rule="evenodd"
				d="M14.9943 7.22234C14.5488 7.11857 14.0929 7.05919 13.6332 7.04586C13.9162 7.32257 14.1734 7.6271 14.4005 7.95579C14.5803 7.69556 14.7789 7.45047 14.9943 7.22234ZM11.3174 23.8354C13.5826 26.1503 16.1878 28.0246 18.0153 29.2991L18.4729 29.6183L18.9306 29.2991C20.8944 27.9295 23.7563 25.8672 26.1293 23.31C28.4906 20.7655 30.4729 17.6188 30.4729 14.0773C30.4729 13.1549 30.2934 12.2412 29.9444 11.3885C29.5954 10.5358 29.0836 9.76031 28.4378 9.10668C28.0581 8.72243 27.6365 8.3847 27.1817 8.09961C26.8629 7.89974 26.5278 7.72574 26.1796 7.57974C25.3973 7.25179 24.5627 7.07106 23.7169 7.04599C23.7436 7.07201 23.77 7.09829 23.7963 7.12482C24.2943 7.62889 24.7027 8.21511 25.0043 8.85758C25.1929 8.91297 25.3788 8.97894 25.561 9.05532C25.8287 9.16756 26.0865 9.30139 26.3319 9.45523C26.6819 9.67467 27.0068 9.93485 27.2996 10.2312C27.7978 10.7354 28.1935 11.3345 28.4636 11.9945C28.7338 12.6546 28.8729 13.3623 28.8729 14.0773C28.8729 17.0184 27.2153 19.7875 24.9565 22.2217C22.8798 24.4596 20.3837 26.3199 18.4729 27.6661C17.5532 27.0181 16.4978 26.251 15.4188 25.38C15.061 25.6364 14.7196 25.8763 14.4005 26.0988C13.5057 25.4747 12.4358 24.7144 11.3174 23.8354Z"
				fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd"
										   d="M9.36039 4.5C10.4801 4.49985 11.5828 4.77316 12.5749 5.29617C13.2577 5.65609 13.8739 6.12683 14.4004 6.6872C14.9268 6.12683 15.5431 5.65609 16.2258 5.29617C17.218 4.77316 18.3207 4.49985 19.4404 4.5M9.36027 4.5C7.51129 4.50003 5.74001 5.2435 4.43555 6.56371C3.13137 7.88362 2.40039 9.67179 2.40039 11.5343C2.40039 15.0758 4.38276 18.2225 6.74398 20.767C9.11699 23.3243 11.9789 25.3865 13.9427 26.7562L14.4004 27.0754L14.858 26.7562C16.8218 25.3865 19.6838 23.3243 22.0568 20.767C24.418 18.2225 26.4004 15.0758 26.4004 11.5343C26.4004 10.6119 26.2209 9.69827 25.8719 8.84553C25.5229 7.99278 25.0111 7.21734 24.3652 6.56371C23.7194 5.91007 22.9521 5.39105 22.107 5.03677C21.2619 4.68249 20.3558 4.50002 19.4405 4.5M11.8288 6.71154C11.0661 6.30947 10.2194 6.09987 9.36051 6.1H9.36039C7.94195 6.1 6.57963 6.67018 5.57368 7.68828C4.56741 8.70669 4.00039 10.09 4.00039 11.5343C4.00039 14.4754 5.65802 17.2445 7.91681 19.6787C9.99351 21.9166 12.4897 23.7769 14.4004 25.1231C16.3111 23.7769 18.8073 21.9166 20.884 19.6787C23.1428 17.2445 24.8004 14.4754 24.8004 11.5343C24.8004 10.8194 24.6612 10.1116 24.3911 9.45158C24.121 8.79156 23.7253 8.19247 23.2271 7.68828C22.7289 7.1841 22.138 6.78468 21.4884 6.51235C20.8388 6.24003 20.1429 6.1 19.4404 6.1V5.30516L19.4403 6.1C18.5814 6.09987 17.7347 6.30947 16.972 6.71154C16.2092 7.11364 15.5526 7.69659 15.0586 8.4117L14.4004 9.36435L13.7422 8.4117C13.2481 7.69659 12.5915 7.11364 11.8288 6.71154Z"
										   fill="currentColor"/></svg>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;

/*
 * Overlap Theme
 */
if (SB_Instagram_Display_Elements_Pro::should_show_stats_element('overlap', $settings)) : ?>
	<span
		class="sbi_likes"<?php echo $sbi_meta_hover_styles; ?><?php echo SB_Instagram_Display_Elements_Pro::stats_atts('overlap', $settings); ?>>
		<svg <?php echo $sbi_meta_hover_styles; ?> viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path
				d="M20.4293 6.4545C19.6764 4.90948 17.5078 3.64537 14.9852 4.38136C13.7798 4.72955 12.7281 5.47624 12.0019 6.49944C11.2757 5.47624 10.224 4.72955 9.01859 4.38136C6.49038 3.65661 4.32735 4.90948 3.5745 6.4545C2.51827 8.61753 2.89906 11.0931 4.87794 13.6852C6.41188 15.6945 10.1813 19.0524 11.5218 20.2247C11.7994 20.4675 12.2094 20.4686 12.4875 20.2264C13.8576 19.0332 17.7595 15.5722 19.1315 13.6852C21.0472 11.0502 21.4855 8.61753 20.4293 6.4545Z"
				fill="currentColor"/></svg>
		<?php echo esc_html($likes_count); ?>
	</span>
<?php endif;
