<?php

namespace InstagramFeed\Builder\Controls;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Customizer Builder
 * Separator Control
 *
 * @since 6.0
 */
class SB_Separator_Control extends SB_Controls_Base
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
		return 'separator';
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
		?>
		<div class="sb-control-elem-separator sbi-fb-fs"
			 :style="'margin-top:'+ (control.top ? control.top : 0) +'px;margin-bottom:'+ (control.bottom ? control.bottom : 0) +'px;'"></div>
		<?php
	}
}
