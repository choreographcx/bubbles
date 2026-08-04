<?php

namespace InstagramFeed\Builder\Controls;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Customizer Builder
 * Action Button Control
 *
 * @since 6.0
 */
class SB_Actionbutton_Control extends SB_Controls_Base
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
		return 'actionbutton';
	}

	/**
	 * Output Control
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 *
	 * @since 6.0
	 * @access public
	 */
	public function get_control_output($controlEditingTypeModel)
	{
		?>
		<button class="sb-control-action-button sb-btn sbi-fb-fs sb-btn-grey">
			<div v-if="control.buttonIcon" v-html="svgIcons[control.buttonIcon]"></div>
			<span class="sb-small-p sb-bold sb-dark-text">{{control.label}}</span>
		</button>
		<?php
	}
}
