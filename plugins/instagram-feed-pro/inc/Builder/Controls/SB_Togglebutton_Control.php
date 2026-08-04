<?php

namespace InstagramFeed\Builder\Controls;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Customizer Builder
 * Toggle Buttons
 *
 * @since 6.0
 */
class SB_Togglebutton_Control extends SB_Controls_Base
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
		return 'togglebutton';
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
		<div class="sb-control-togglebutton-ctn sbi-fb-fs">
			<div class="sb-control-togglebutton-elm sbi-fb-fs sb-tr-1" v-for="toggle in control.options"
				 :data-active="<?php echo $controlEditingTypeModel; ?>[control.id] == toggle.value"
				 v-show="toggle.condition != undefined ? checkControlCondition(toggle.condition) : true"
				 @click.prevent.default="changeSettingValue(control.id,toggle.value, true)">
				{{toggle.label}}
			</div>
		</div>

		<?php
	}
}
