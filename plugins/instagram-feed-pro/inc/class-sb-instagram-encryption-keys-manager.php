<?php

if (!defined('ABSPATH')) {
	die('-1');
}

class SBI_Ecnryption_Keys_Manager
{
	/**
	 * The constant used for the encryption key.
	 *
	 * @var string
	 */
	private $encryption_key_constant;

	/**
	 * The constant used for the encryption salt.
	 *
	 * @var string
	 */
	private $encryption_salt_constant;

	/**
	 * Class constructor.
	 *
	 * @param string $encryption_key_constant The encryption key constant.
	 * @param string $encryption_salt_constant The encryption salt constant.
	 */
	public function __construct($encryption_key_constant, $encryption_salt_constant)
	{
		$this->encryption_key_constant = $encryption_key_constant;
		$this->encryption_salt_constant = $encryption_salt_constant;
	}

	/**
	 * Removes the encryption keys by setting the constants to true.
	 *
	 * @return void
	 */
	public function remove_keys()
	{
		$this->set_constants(true);
	}

	/**
	 * Sets the encryption keys constants in the wp-config.php file.
	 *
	 * @param bool $remove Optional. Whether to remove the constants. Default false.
	 * @return bool|int False on failure, number of bytes written to the file on success.
	 */
	public function set_constants($remove = false)
	{
		if (!$this->should_set_keys_constants()) {
			return false;
		}

		$config_file_path = $this->find_wpconfig_path();

		if (!$config_file_path) {
			return false;
		}

		$config_file_contents = file_get_contents($config_file_path);

		$salt_value = wp_generate_password(64);
		$key_value = wp_generate_password(64);

		$salt_constant_value = $this->get_constant_content($this->encryption_salt_constant, $salt_value);
		$encryption_constant_value = $this->get_constant_content($this->encryption_key_constant, $key_value);

		$updated_with_salt = $this->add_or_update_constant($this->encryption_salt_constant, true === $remove ? '' : $salt_constant_value, true === $remove ? '' : $salt_value, $config_file_contents);

		if (false !== $updated_with_salt) {
			$config_file_contents = $updated_with_salt;
		}

		$updated_with_encryption = $this->add_or_update_constant(
			$this->encryption_key_constant,
			true === $remove ? '' : $encryption_constant_value,
			true === $remove ? '' : $key_value,
			$config_file_contents
		);

		if (false !== $updated_with_encryption) {
			$config_file_contents = $updated_with_encryption;
		}

		if (empty($config_file_contents)) {
			return false;
		}

		return file_put_contents($config_file_path, $config_file_contents);
	}

	/**
	 * Determines if the keys constants should be set.
	 *
	 * @return bool True if the current user can manage Instagram feed options, false otherwise.
	 */
	private function should_set_keys_constants()
	{
		return current_user_can('manage_instagram_feed_options');
	}

	/**
	 * Finds the path to the writable wp-config.php file.
	 *
	 * @return string|false The path to the writable wp-config.php file, or false if no writable file is found.
	 */
	private function find_wpconfig_path()
	{
		$config_file_name = 'wp-config';
		$abspath = ABSPATH;
		$config_file = "{$abspath}{$config_file_name}.php";

		if (is_writable($config_file)) {
			return $config_file;
		}

		$abspath_parent = dirname($abspath) . DIRECTORY_SEPARATOR;
		$config_file_alt = "{$abspath_parent}{$config_file_name}.php";

		if (
			is_file($config_file_alt)
			&&
			is_writable($config_file_alt)
			&&
			!is_file("{$abspath_parent}wp-settings.php")
		) {
			return $config_file_alt;
		}

		// No writable file found.
		return false;
	}

	/**
	 * Generates a string that defines a PHP constant with a given value.
	 *
	 * @param string $constant The name of the constant to define.
	 * @param string $value The value to assign to the constant. Default is 'true'.
	 * @return string The generated string that defines the constant.
	 */
	private function get_constant_content($constant, $value = 'true')
	{
		return "define( '{$constant}', '{$value}' );";
	}

	/**
	 * Adds or updates a constant in the given configuration file contents.
	 *
	 * @param string $constant_name The name of the constant to add or update.
	 * @param string $constant The constant definition to add if the constant is not found.
	 * @param string $value The value of the constant to check against.
	 * @param string $config_file_contents The contents of the configuration file.
	 *
	 * @return string|false The updated configuration file contents if the constant was added or updated, or false if the constant was found with the same value.
	 */
	private function add_or_update_constant($constant_name, $constant, $value, $config_file_contents)
	{
		$constant_found = preg_match(
			"/^\s*define\(\s*'{$constant_name}'\s*,.*?(?<value>[^\s\)]*)\s*\)/m",
			$config_file_contents,
			$matches
		);

		if (
			!empty($matches['value'])
			&&
			$matches['value'] === $value
		) {
			return false;
		}

		if (!$constant_found) {
			$config_file_contents = preg_replace('/(<\?php)/i', "<?php\r\n{$constant}\r\n", $config_file_contents, 1);
		}

		return $config_file_contents;
	}
}
