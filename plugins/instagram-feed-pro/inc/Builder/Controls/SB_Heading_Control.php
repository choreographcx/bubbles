<?php

namespace InstagramFeed\Builder\Controls;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Customizer Builder
 * Heading Text Control
 *
 * @since 6.0
 */
class SB_Heading_Control extends SB_Controls_Base
{
	/**
	 * Get control type.
	 *
	 * Getting the Control Type
	 *
	 * @return string
	 * @since 6.0
	 * @access public
	 */
	public function get_type()
	{
		return 'heading';
	}

	/**
	 * Output Control
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Modal.
	 * @since 6.0
	 * @access public
	 */
	public function get_control_output($controlEditingTypeModel)
	{
	}
}
