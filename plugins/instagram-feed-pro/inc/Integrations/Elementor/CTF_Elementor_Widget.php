<?php

namespace InstagramFeed\Integrations\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class CTF_Elementor_Widget extends Widget_Base
{
	/**
	 * Retrieve the name of the Elementor widget.
	 *
	 * This function returns the unique name of the widget which is used to identify it within Elementor.
	 *
	 * @return string The name of the Elementor widget.
	 */
	public function get_name()
	{
		return 'ctf-widget';
	}

	/**
	 * Get the title of the Elementor widget.
	 *
	 * This function returns the title of the widget that will be displayed in the Elementor editor.
	 *
	 * @return string The title of the Elementor widget.
	 */
	public function get_title()
	{
		return esc_html__('Twitter Feed', 'instagram-feed');
	}

	/**
	 * Retrieves the icon associated with the Elementor widget.
	 *
	 * @return string The icon for the Elementor widget.
	 */
	public function get_icon()
	{
		return 'sb-elem-icon sb-elem-inactive sb-elem-twitter';
	}

	/**
	 * Retrieves the categories for the Elementor widget.
	 *
	 * @return array The list of categories for the Elementor widget.
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
	 * This function returns an array of script handles that the Elementor widget depends on.
	 *
	 * @return array An array of script handles.
	 */
	public function get_script_depends()
	{
		return [
			'elementor-handler'
		];
	}
}
