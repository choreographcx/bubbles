<?php

namespace InstagramFeed\Builder\Controls;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Customizer Builder
 * Hidden Field Control
 *
 * @since 6.0
 */
class SB_Hidden_Control extends SB_Controls_Base
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
		return 'hidden';
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
		<div class="sb-control-input-ctn sbi-fb-fs">
			<input type="hidden" v-model="<?php echo $controlEditingTypeModel; ?>[control.id]">
		</div>
		<?php
	}
}
