<?php

namespace InstagramFeed;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Class SBI_View
 *
 * This class loads view page template files on the admin dashboard area.
 *
 * @since 6.0
 */
class SBI_View
{
	/**
	 * Base file path of the templates
	 *
	 * @since 6.0
	 */
	protected const BASE_PATH = SBI_PLUGIN_DIR . 'admin/views/';

	/**
	 * Render template
	 *
	 * @param string $file Template file name.
	 * @param array  $data Data to pass to the template.
	 *
	 * @since 6.0
	 */
	public static function render($file, $data = array())
	{
		$file = str_replace('.', '/', $file);
		$file = self::BASE_PATH . $file . '.php';

		if (file_exists($file)) {
			if (!empty($data)) {
				extract($data);
			}
			include_once $file;
		}
	}
}
