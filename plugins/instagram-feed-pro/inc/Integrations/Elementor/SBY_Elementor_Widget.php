<?php

namespace InstagramFeed\Integrations\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class SBY_Elementor_Widget extends Widget_Base
{
	/**
	 * Get the name of the Elementor widget.
	 *
	 * @return string The name of the Elementor widget.
	 */
	public function get_name()
	{
		return 'sby-widget';
	}

	/**
	 * Get the title of the Elementor widget.
	 *
	 * @return string The title of the Elementor widget.
	 */
	public function get_title()
	{
		return esc_html__('YouTube Feed', 'instagram-feed');
	}

	/**
	 * Get the icon for the Elementor widget.
	 *
	 * @return string The icon for the Elementor widget.
	 */
	public function get_icon()
	{
		return 'sb-elem-icon sb-elem-inactive sb-elem-youtube';
	}

	/**
	 * Get the categories for the Elementor widget.
	 *
	 * @return array The categories for the Elementor widget.
	 */
	public function get_categories()
	{
		return array('smash-balloon');
	}

	public function show_in_panel()
	{
		return false;
	}

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
			'elementor-handler'
		];
	}
}
