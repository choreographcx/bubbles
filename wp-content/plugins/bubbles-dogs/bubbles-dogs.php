<?php
/**
 * Plugin Name:       Bubbles Dogs
 * Plugin URI:        https://www.bubblespetrescue.ae/
 * Description:       Adoptable dog listings for Bubbles Pet Rescue — a "Dog" post type with structured details, a filterable archive, and a CSV importer for bulk-loading dogs from social media exports.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Bubbles Pet Rescue
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bubbles-dogs
 *
 * @package BubblesDogs
 */

defined( 'ABSPATH' ) || exit;

define( 'BPR_DOGS_VERSION', '1.0.0' );
define( 'BPR_DOGS_FILE', __FILE__ );
define( 'BPR_DOGS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BPR_DOGS_URL', plugin_dir_url( __FILE__ ) );

require_once BPR_DOGS_DIR . 'includes/class-bpr-dogs-fields.php';
require_once BPR_DOGS_DIR . 'includes/class-bpr-dogs-post-type.php';
require_once BPR_DOGS_DIR . 'includes/class-bpr-dogs-admin.php';
require_once BPR_DOGS_DIR . 'includes/class-bpr-dogs-settings.php';
require_once BPR_DOGS_DIR . 'includes/class-bpr-dogs-display.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once BPR_DOGS_DIR . 'includes/class-bpr-dogs-import.php';
}

/**
 * Boot the plugin.
 *
 * Everything hangs off `plugins_loaded` so the post type is registered before
 * `init` fires and so nothing touches the database at include time.
 */
function bpr_dogs_bootstrap() {
	BPR_Dogs_Post_Type::init();
	BPR_Dogs_Admin::init();
	BPR_Dogs_Settings::init();
	BPR_Dogs_Display::init();
}
add_action( 'plugins_loaded', 'bpr_dogs_bootstrap' );

/**
 * On activation, register the post type then flush rewrite rules so the
 * /adopt-a-dog/ archive and individual dog URLs work immediately rather than
 * 404ing until someone re-saves permalinks.
 */
function bpr_dogs_activate() {
	BPR_Dogs_Post_Type::register_post_type();
	BPR_Dogs_Post_Type::register_taxonomies();
	BPR_Dogs_Post_Type::insert_default_terms();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bpr_dogs_activate' );

/**
 * Clean up rewrite rules on deactivation. Dog posts and their meta are left
 * untouched — deactivating the plugin must never destroy rescue records.
 */
function bpr_dogs_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'bpr_dogs_deactivate' );
