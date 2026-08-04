<?php
/**
 * Forminator Pro.
 *
 * @package Forminator
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'ForminatorPro' ) ) {
	/**
	 * Class ForminatorPro
	 *
	 * @since 1.52
	 */
	class ForminatorPro {

		/**
		 * ForminatorPro instance
		 *
		 * @var ForminatorPro
		 */
		private static $instance = null;

		/**
		 * Return the ForminatorPro instance
		 *
		 * @since 1.52
		 * @return ForminatorPro
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
			$this->include_dashboard_notification();
			$this->add_hooks();
			$this->includes();
		}

		/**
		 * Include Forminator Pro files.
		 *
		 * @return void
		 */
		public function includes() {
			include_once forminator_plugin_dir() . 'pro/includes/class-hub-connector.php';
		}

		/**
		 * Add hooks for Forminator Pro.
		 *
		 * @return void
		 */
		public function add_hooks() {
			add_action( 'forminator_before_admin_footer_links', array( $this, 'add_admin_footer_links' ) );
			// Add links next to plugin details.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 11, 2 );
			// Add notice message for cloud templates.
			add_filter( 'forminator_cloud_templates_notice_message', array( $this, 'add_cloud_templates_notice_message' ), 10, 4 );
			add_filter( 'forminator_support_url', array( $this, 'get_support_url' ), 10 );
			add_action( 'forminator_after_deactivation_survey_message', array( $this, 'add_support_link' ) );
			add_filter( 'forminator_page_ids', array( $this, 'get_page_ids' ) );
			add_filter( 'Forminator_mixpanel_super_properties', array( $this, 'set_mixpanel_properties' ) );
		}

		/**
		 * Set Mixpanel properties for Forminator Pro.
		 *
		 * @param array $properties Mixpanel properties.
		 *
		 * @return array
		 */
		public function set_mixpanel_properties( $properties ) {
			$properties['plugin_type'] = 'Pro';
			return $properties;
		}

		/**
		 * Get page IDs for Forminator Pro.
		 *
		 * @param array $page_ids Page IDs.
		 *
		 * @return array
		 */
		public function get_page_ids( $page_ids ) {
			$title = sanitize_title( esc_html__( 'Forminator Pro', 'forminator' ) );
			return array(
				$title . '_page_forminator-quiz-view',
				$title . '_page_forminator-cform-view',
				$title . '_page_forminator-poll-view',
				$title . '_page_forminator-entries',
			);
		}

		/**
		 * Add message with support link in deactivation survey.
		 *
		 * @return void
		 */
		public function add_support_link() {
			if ( forminator_is_show_documentation_link() ) {
				echo ' ';
				/* Translators: 1. Opening <a> tag, 2. closing <a> tag. */
				printf( esc_html__( '%1$sNeed Help?%2$s', 'forminator' ), '<a class="forminator-deactivation-help" href="https://wpmudev.com/hub2/support" target="_blank">', '</a>' );
			}
		}

		/**
		 * Get support URL for Forminator Pro.
		 *
		 * @param string $support_url Support URL.
		 *
		 * @return string
		 */
		public function get_support_url( $support_url ) {
			return 'https://wpmudev.com/hub2/support/';
		}

		/**
		 * Add notice message for cloud templates.
		 *
		 * @param string $message Notice message.
		 * @param string $slug Module slug.
		 * @param string $save_to_cloud_url Save to cloud URL.
		 * @param string $learn_more_url Learn more URL.
		 *
		 * @return string
		 */
		public function add_cloud_templates_notice_message( $message, $slug, $save_to_cloud_url, $learn_more_url ) {
			$message = sprintf(
				/* translators: 1. Opening <b> tag, 2. Closing </b> tag, 3. Module slug, 4. Opening <a> tag for Save To Cloud link, 5. Closing </a> tag, 6. Opening <a> tag for Learn more link, 7. Closing </a> tag */
				__( '%1$sWant to use this %3$s on another site?%2$s Use %4$sSave To Cloud%5$s to save it as a template and reuse it on any site you manage via The Hub. %6$sLearn more%7$s', 'forminator' ),
				'<b>',
				'</b>',
				esc_html( $slug ),
				'<a href="' . esc_url( $save_to_cloud_url ) . '" target="_blank" rel="noopener">',
				'</a>',
				'<a href="' . esc_url( $learn_more_url ) . '" target="_blank" rel="noopener">',
				'</a>'
			);
			return $message;
		}

		/**
		 * Show row meta on the plugin screen.
		 *
		 * @param mixed $links Plugin Row Meta.
		 * @param mixed $file Plugin Base file.
		 *
		 * @return array
		 * @since 1.52
		 */
		public function plugin_row_meta( $links, $file ) {
			if ( FORMINATOR_PLUGIN_BASENAME === $file ) {
				// Show network meta links only when activated network wide.
				if ( is_network_admin() && ! forminator_is_networkwide() ) {
					return $links;
				}

				$domain = 'https://wpmudev.com';

				// Change AuthorURI link.
				if ( isset( $links[1] ) ) {
					$author_uri = sprintf(
						'<a href="%s" target="_blank">%s</a>',
						$domain,
						esc_html__( 'WPMU DEV', 'forminator' )
					);
					$links[1]   = /* translators: %s: Authorise url. */ sprintf( esc_html__( 'By %s', 'forminator' ), $author_uri );
				}

				// Change 'Visit plugins' link to 'View details'.
				if ( isset( $links[2] ) && false !== strpos( $links[2], 'project/forminator' ) ) {
					$pro_link = "{$domain}/project/forminator-pro/";
					$links[2] = sprintf(
						'<a href="%s" target="_blank">%s</a>',
						esc_url( $pro_link ),
						esc_html__( 'View details', 'forminator' )
					);
				}
				$support_link        = "{$domain}/get-support/";
				$row_meta['support'] = '<a href="' . esc_url( $support_link ) . '" aria-label="' . esc_attr__( 'Premium Support', 'forminator' ) . '" target="_blank">' . esc_html__( 'Premium Support', 'forminator' ) . '</a>';

				return array_merge( $links, $row_meta );
			}

			return $links;
		}

		/**
		 * Add admin footer links for Forminator Pro.
		 *
		 * @param bool $hide_footer Hide footer or not.
		 * @return void
		 */
		public function add_admin_footer_links( $hide_footer ) {
			if ( ! $hide_footer ) :
				?>
				<ul class="sui-footer-nav">
					<li><a href="https://wpmudev.com/hub2/" target="_blank"><?php esc_html_e( 'The Hub', 'forminator' ); ?></a></li>
					<li><a href="https://wpmudev.com/projects/category/plugins/" target="_blank"><?php esc_html_e( 'Plugins', 'forminator' ); ?></a></li>
					<li><a href="https://wpmudev.com/roadmap/" target="_blank"><?php esc_html_e( 'Roadmap', 'forminator' ); ?></a></li>
					<li><a href="https://wpmudev.com/hub2/support/" target="_blank"><?php esc_html_e( 'Support', 'forminator' ); ?></a></li>
					<li><a href="https://wpmudev.com/docs/" target="_blank"><?php esc_html_e( 'Docs', 'forminator' ); ?></a></li>
					<li><a href="https://wpmudev.com/hub2/community/" target="_blank"><?php esc_html_e( 'Community', 'forminator' ); ?></a></li>
					<li><a href="https://wpmudev.com/terms-of-service/" target="_blank"><?php esc_html_e( 'Terms of Service', 'forminator' ); ?></a></li>
					<li><a href="https://incsub.com/privacy-policy/" target="_blank"><?php esc_html_e( 'Privacy Policy', 'forminator' ); ?></a></li>
				</ul>
				<?php
			endif;
		}

		/**
		 * Include dashboard notification for pro plugin.
		 *
		 * @since 1.52
		 */
		private function include_dashboard_notification() {
			if ( file_exists( forminator_plugin_dir() . 'library/lib/dash-notice/wpmudev-dash-notification.php' ) ) {
				// load dashboard notice.
				global $wpmudev_notices;
				$wpmudev_notices[] = array(
					'id'      => 2097296,
					'name'    => 'Forminator Pro',
					'screens' => array(
						'toplevel_page_forminator',
						'toplevel_page_forminator-network',
						'forminator_page_forminator-cform',
						'forminator_page_forminator-cform-network',
						'forminator_page_forminator-poll',
						'forminator_page_forminator-poll-network',
						'forminator_page_forminator-quiz',
						'forminator_page_forminator-quiz-network',
						'forminator_page_forminator-settings',
						'forminator_page_forminator-settings-network',
						'forminator_page_forminator-cform-wizard',
						'forminator_page_forminator-cform-wizard-network',
						'forminator_page_forminator-cform-view',
						'forminator_page_forminator-cform-view-network',
						'forminator_page_forminator-poll-wizard',
						'forminator_page_forminator-poll-wizard-network',
						'forminator_page_forminator-poll-view',
						'forminator_page_forminator-poll-view-network',
						'forminator_page_forminator-nowrong-wizard',
						'forminator_page_forminator-nowrong-wizard-network',
						'forminator_page_forminator-knowledge-wizard',
						'forminator_page_forminator-knowledge-wizard-network',
						'forminator_page_forminator-quiz-view',
						'forminator_page_forminator-quiz-view-network',
					),
				);
				// @noinspection PhpIncludeInspection.
				include_once forminator_plugin_dir() . 'library/lib/dash-notice/wpmudev-dash-notification.php';
			}
		}
	}
}
if ( FORMINATOR_PRO ) {
	ForminatorPro::get_instance();
}