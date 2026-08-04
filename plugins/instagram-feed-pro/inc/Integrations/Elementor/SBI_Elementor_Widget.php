<?php

namespace InstagramFeed\Integrations\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use InstagramFeed\Builder\SBI_Db;
use InstagramFeed\Builder\SBI_Feed_Builder;
use InstagramFeed\Integrations\SBI_Integration;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class SBI_Elementor_Widget extends Widget_Base
{
	/**
	 * Get the name of the Elementor widget.
	 *
	 * @return string The name of the widget.
	 */
	public function get_name()
	{
		return 'sbi-widget';
	}

	/**
	 * Get the title of the Elementor widget.
	 *
	 * @return string The title of the Elementor widget.
	 */
	public function get_title()
	{
		return esc_html__('Instagram Feed', 'instagram-feed');
	}

	/**
	 * Retrieve the icon for the Elementor widget.
	 *
	 * @return string The icon for the Elementor widget.
	 */
	public function get_icon()
	{
		return 'sb-elem-icon sb-elem-instagram';
	}

	/**
	 * Retrieve the categories for the Elementor widget.
	 *
	 * @return array The categories for the Elementor widget.
	 */
	public function get_categories()
	{
		return array('smash-balloon');
	}

	/**
	 * Hide this legacy widget from the Elementor panel.
	 *
	 * @return bool
	 */
	public function show_in_panel()
	{
		return false;
	}

	/**
	 * Hide this legacy widget from Elementor search results.
	 *
	 * @return bool
	 */
	public function hide_on_search()
	{
		return true;
	}

	/**
	 * Get the script dependencies for the Elementor widget.
	 *
	 * @return array List of script handles.
	 */
	public function get_script_depends()
	{
		return [
			'sbiscripts',
			'elementor-preview'
		];
	}

	/**
	 * Register Elementor widget controls.
	 *
	 * @return void
	 */
	protected function register_controls()
	{
		/********************************************
		 * CONTENT SECTION
		 */
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__('Instagram Feed Settings', 'instagram-feed'),
			]
		);
		$this->add_control(
			'feed_id',
			[
				'label' => esc_html__('Select a Feed', 'instagram-feed'),
				'type' => 'sbi_feed_control',
				'label_block' => true,
				'dynamic' => ['active' => true],
				'options' => SBI_Db::elementor_feeds_query($custom = true),
			]
		);
		$this->end_controls_section();
	}

	/**
	 * Render the Elementor widget.
	 *
	 * @return void
	 */
	protected function render()
	{
		$settings = $this->get_settings_for_display();
		if (!empty($settings['feed_id'])) {
			$output = do_shortcode(shortcode_unautop('[instagram-feed feed=' . $settings['feed_id'] . ']'));
		} else {
			$output = is_admin() ? SBI_Integration::get_widget_cta() : '';
		}
		echo apply_filters('sbi_output', $output, $settings);
	}
}
