<?php

namespace InstagramFeed\Integrations\Elementor;

use InstagramFeed\Vendor\Smashballoon\Framework\Packages\Blocks\RecommendedElementorWidgets;
use InstagramFeed\Vendor\Smashballoon\Framework\Packages\Blocks\SB_Feed_Blocks_Registry;
use InstagramFeed\Vendor\Smashballoon\Framework\Packages\Blocks\SB_Block_Utils;
use InstagramFeed\Vendor\Smashballoon\Framework\Packages\Blocks\SB_Elementor_Editor_Assets;
use InstagramFeed\Builder\SBI_Db;

if (! defined('ABSPATH')) {
	exit;
}

class SBI_Elementor_Base
{
	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Register and return the singleton instance.
	 *
	 * @return self
	 */
	public static function register()
	{
		if (null === self::$instance) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * Alias for register() for backward compatibility.
	 *
	 * @return self
	 */
	public static function instance()
	{
		return self::register();
	}

	/**
	 * Initialize the Elementor integration.
	 *
	 * @return void
	 */
	private function init()
	{
		if (doing_action('init') || did_action('init')) {
			$this->init_elementor_integration();
		} else {
			add_action('init', array( $this, 'init_elementor_integration' ), 4);
		}
	}

	/**
	 * Set up Elementor widgets, controls, and hooks.
	 *
	 * @return void
	 */
	public function init_elementor_integration()
	{
		if (! did_action('elementor/loaded')) {
			return;
		}

		// Require Elementor 3.5.0+ for the widgets/register hook and register() method.
		if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '<')) {
			return;
		}

		$recommended = new RecommendedElementorWidgets('instagram');
		$recommended->setup();

		$registry = SB_Feed_Blocks_Registry::instance();
		$registry->register_elementor_widget(array(
			'blockId'    => 'instagram',
			'widgetName' => 'sb-instagram-feed',
			'globalVar'  => 'sbiElementorData',
			'feedInitFn' => 'sbi_init',
		));

		add_action('elementor/widgets/register', array( $this, 'register_widgets' ));
		add_action('elementor/controls/register', array( $this, 'register_controls' ));
		add_action('elementor/frontend/after_register_scripts', array( $this, 'register_frontend_scripts' ));
		add_action('elementor/elements/categories_registered', array( $this, 'add_smashballoon_categories' ));
		add_action('elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ));
		add_action('elementor/preview/enqueue_scripts', array( $this, 'enqueue_preview_feed_assets' ));
		// Belt-and-suspenders: also hook the standard wp_enqueue_scripts and
		// gate on Elementor's preview-mode helper, in case the dedicated
		// preview hook fires after our window.
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_preview_feed_assets' ), 5);
	}

	/**
	 * Register Elementor widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 *
	 * @return void
	 */
	public function register_widgets($widgets_manager)
	{
		$widgets_manager->register(new SBI_Modern_Elementor_Widget());
		$widgets_manager->register(new SBI_Elementor_Widget());
	}

	/**
	 * Register Elementor controls.
	 *
	 * @param \Elementor\Controls_Manager $controls_manager Elementor controls manager.
	 *
	 * @return void
	 */
	public function register_controls($controls_manager)
	{
		$controls_manager->register(new SBI_Feed_Elementor_Control());
	}

	/**
	 * Register and localize frontend scripts for Elementor.
	 *
	 * @return void
	 */
	public function register_frontend_scripts()
	{
		sb_instagram_scripts_enqueue();

		// Register legacy script + style handle aliases for backward compatibility.
		// The legacy SBI_Elementor_Widget declares these in get_script_depends()
		// and get_style_depends().
		if (! wp_script_is('sbiscripts', 'registered')) {
			wp_register_script('sbiscripts', '', array( 'sbi_scripts' ), SBIVER, true);
		}
		if (! wp_script_is('elementor-preview', 'registered')) {
			wp_register_script('elementor-preview', '', array(), SBIVER, true);
		}
		if (! wp_style_is('sbistyles', 'registered')) {
			wp_register_style('sbistyles', '', array( 'sbi_styles' ), SBIVER);
		}

		$feeds = SBI_Db::elementor_feeds_list();

		$data = array(
			'feeds'         => ! empty($feeds) ? $feeds : array(),
			'feed_url'      => admin_url('admin.php?page=sbi-feed-builder'),
			'is_pro_active' => sbi_is_pro_version(),
		);

		wp_localize_script('sbi_scripts', 'sbiElementorData', $data);

		SB_Feed_Blocks_Registry::instance()->enqueue_elementor_assets();
	}

	/**
	 * Add Smash Balloon widget category to Elementor.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 *
	 * @return void
	 */
	public function add_smashballoon_categories($elements_manager)
	{
		$elements_manager->add_category(
			SB_Block_Utils::CATEGORY_SLUG,
			array(
				'title' => esc_html__('Smash Balloon', 'instagram-feed'),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Enqueue shared Elementor editor styles.
	 *
	 * @return void
	 */
	public function enqueue_editor_scripts()
	{
		SB_Elementor_Editor_Assets::enqueue_shared_elementor_styles(SBIVER);
	}

	/**
	 * Force-enqueue the feed CSS/JS inside the Elementor preview iframe.
	 *
	 * The legacy SBI_Elementor_Widget renders via do_shortcode() which only
	 * registers sbi_scripts/sbi_styles by default. Elementor processes widget
	 * render after wp_head fires inside the preview iframe, so the shortcode's
	 * late enqueue never lands in the page. Hook elementor/preview/enqueue_scripts
	 * and force-enqueue here so the feed initializes inside the iframe even
	 * when only the legacy widget is on the page.
	 *
	 * @return void
	 */
	public function enqueue_preview_feed_assets()
	{
		// Only act inside the Elementor preview iframe — never affect the
		// public frontend. is_preview_mode() returns true when the request
		// has ?elementor-preview=<post-id>.
		if (! class_exists('\Elementor\Plugin') || empty(\Elementor\Plugin::instance()->preview)) {
			return;
		}
		if (! \Elementor\Plugin::instance()->preview->is_preview_mode()) {
			return;
		}
		sb_instagram_scripts_enqueue(true);

		// The framework's sb-elementor-editor.js wires sbi_init() to the
		// 'frontend/element_ready/sb-instagram-feed.default' hook for the
		// modern widget. The legacy SBI_Elementor_Widget registers under
		// the name 'sbi-widget', so that hook never fires for it and
		// sbi_init() never runs in preview when only the legacy widget is
		// on the page. Bridge it with a small dedicated script.
		wp_enqueue_script(
			'sbi-elementor-preview',
			trailingslashit( SBI_PLUGIN_URL ) . 'js/sbi-elementor-preview.js',
			array( 'sbi_scripts' ),
			SBIVER,
			true
		);

		// Chromium-only workaround: the masonry/highlight layout engine runs
		// before the preview iframe is sized, so it reads a wrong container
		// width and collapses #sbi_images. Re-run the real layout once the
		// iframe has its final width (see the JS) instead of overriding CSS.
		// Preview-only. Version by filemtime so editor edits bust the asset
		// cache (Elementor/browser otherwise reuse the ?ver=SBIVER copy).
		$layout_fix_rel = 'admin/assets/js/elementor-preview-layout-fix.js';
		$layout_fix_ver = defined( 'SBI_PLUGIN_DIR' ) && file_exists( SBI_PLUGIN_DIR . $layout_fix_rel )
			? filemtime( SBI_PLUGIN_DIR . $layout_fix_rel )
			: SBIVER;
		wp_enqueue_script(
			'sbi-elementor-preview-layout-fix',
			trailingslashit( SBI_PLUGIN_URL ) . $layout_fix_rel,
			array( 'jquery', 'sbi_scripts' ),
			$layout_fix_ver,
			true
		);
	}
}
