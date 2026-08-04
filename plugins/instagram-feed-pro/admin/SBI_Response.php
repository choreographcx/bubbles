<?php

namespace InstagramFeed;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Class SBI_Response
 *
 * Sends back ajax response to client end
 *
 * @since 6.0
 */
class SBI_Response
{
	/** Is the response successful?
	 *
	 * @var boolean
	 */
	private $is_success;

	/** Data to send back, either success or error.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Response constructor.
	 *
	 * @param boolean $is_success If the response is successful or not.
	 * @param array   $data Data to send back, either success or error.
	 *
	 * @throws Exception If the data is not an array.
	 */
	public function __construct($is_success, $data)
	{
		$this->is_success = $is_success;
		$this->data = $data;
	}

	/**
	 * Send JSON response
	 */
	public function send()
	{
		if ($this->is_success) {
			wp_send_json_success($this->data);
		}
		wp_send_json_error($this->data);
	}
}
