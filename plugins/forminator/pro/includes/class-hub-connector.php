<?php
/**
 * Forminator_PRO_Hub_Connector.
 *
 * @package Forminator
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'Forminator_PRO_Hub_Connector' ) ) {
	/**
	 * Class Forminator_PRO_Hub_Connector
	 *
	 * @since 1.52
	 */
	class Forminator_PRO_Hub_Connector {

		/**
		 * Forminator_PRO_Hub_Connector instance
		 *
		 * @var Forminator_PRO_Hub_Connector
		 */
		private static $instance = null;

		/**
		 * Return the Forminator_PRO_Hub_Connector instance
		 *
		 * @since 1.52
		 * @return Forminator_PRO_Hub_Connector
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * ForminatorPro constructor.
		 *
		 * @since 1.52
		 */
		public function __construct() {
			$this->add_hooks();
		}

		/**
		 * Add hooks for Forminator Pro.
		 *
		 * @return void
		 */
		public function add_hooks() {
			add_filter( 'forminator_hub_connect_url', array( $this, 'get_hub_connect_url' ) );
			add_filter( 'forminator_hub_connect_cta_text', array( $this, 'get_hub_connect_cta_text' ) );
			add_filter( 'forminator_hub_connect_title', array( $this, 'get_hub_connect_title' ) );
			add_filter( 'forminator_hub_connect_description', array( $this, 'get_hub_connect_description' ) );
			add_filter( 'forminator_hub_connect_logo', array( $this, 'get_hub_connect_logo' ) );
		}

		/**
		 * Get Hub Connector connection logo for Forminator Pro.
		 *
		 * @param string $logo Hub Connector connection logo.
		 * @return string
		 */
		public function get_hub_connect_logo( $logo ) {
			if ( ! Forminator_Hub_Connector::is_wpmudev_dashboard_installed() ) {
				return 'wpmudev-logo';
			}
			return $logo;
		}

		/**
		 * Get Hub Connector connection description for Forminator Pro.
		 *
		 * @param string $description Hub Connector connection description.
		 * @return string
		 */
		public function get_hub_connect_description( $description ) {
			if ( ! Forminator_Hub_Connector::is_wpmudev_dashboard_installed() ) {
				return __( 'You don\'t have the WPMU DEV Dashboard plugin, which you\'ll need to access Pro preset templates. Install and log in to the dashboard to unlock the complete list of preset templates.', 'forminator' );
			}
			return $description;
		}

		/**
		 * Get Hub Connector connection title for Forminator Pro.
		 *
		 * @param string $title Hub Connector connection title.
		 * @return string
		 */
		public function get_hub_connect_title( $title ) {
			if ( ! Forminator_Hub_Connector::is_wpmudev_dashboard_installed() ) {
				return __( 'Install WPMU DEV Dashboard', 'forminator' );
			}
			return $title;
		}

		/**
		 * Get Hub Connector connection CTA text for Forminator Pro.
		 *
		 * @param string $text CTA text.
		 * @return string
		 */
		public function get_hub_connect_cta_text( $text ) {
			return __( 'Install Plugin', 'forminator' );
		}

		/**
		 * Get Hub Connector connection URL for Forminator Pro.
		 *
		 * @param mixed $url Hub Connector connection URL.
		 * @return string
		 */
		public function get_hub_connect_url( $url ) {
			return 'https://wpmudev.com/project/wpmu-dev-dashboard/';
		}
	}
}
Forminator_PRO_Hub_Connector::get_instance();