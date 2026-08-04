<?php

namespace InstagramFeed;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Class SBI_HTTP_Request
 *
 * This class with make remote request
 *
 * @since 6.0
 */
class SBI_HTTP_Request
{
	/**
	 * Make the HTTP remote request
	 *
	 * @param string     $method The HTTP method to use for the request (GET, POST, DELETE, PATCH, PUT).
	 * @param string     $url The URL to which the request is sent.
	 * @param array|null $data Optional. An array of additional arguments to pass with the request.
	 *                           This can include headers, body, and other request options.
	 *
	 * @return array|WP_Error The response or WP_Error on failure.
	 * @since 6.0
	 */
	public static function request($method, $url, $data = null)
	{
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
		);

		$args = array_merge($args, $data);

		if ('GET' === $method) {
			$request = wp_safe_remote_get($url, $args);
		} elseif ('DELETE' === $method) {
			$args['method'] = 'DELETE';
			$request = wp_safe_remote_request($url, $args);
		} elseif ('PATCH' === $method) {
			$args['method'] = 'PATCH';
			$request = wp_safe_remote_request($url, $args);
		} elseif ('PUT' === $method) {
			$args['method'] = 'PUT';
			$request = wp_safe_remote_request($url, $args);
		} else {
			$args['method'] = 'POST';
			$request = wp_safe_remote_post($url, $args);
		}

		return $request;
	}

	/**
	 * Check if WP_Error returned
	 *
	 * @param array|WP_Error $request The response or WP_Error on failure.
	 *
	 * @return bool True if WP_Error returned, false otherwise.
	 * @since 6.0
	 */
	public static function is_error($request)
	{
		return is_wp_error($request);
	}

	/**
	 * Get the remote call status code
	 *
	 * @param array|WP_Error $request The response or WP_Error on failure.
	 *
	 * @return string|void The status code or void on failure.
	 * @since 6.0
	 */
	public static function status($request)
	{
		if (is_wp_error($request)) {
			return null;
		}

		return wp_remote_retrieve_response_code($request);
	}

	/**
	 * Get the remote call body data
	 *
	 * @param array|WP_Error $request The response or WP_Error on failure.
	 *
	 * @return array $response The response data.
	 * @since 6.0
	 */
	public static function data($request)
	{
		$response = wp_remote_retrieve_body($request);
		return json_decode($response);
	}
}
