<?php
/**
 * Plugin Name: Bubbles Pets Rescue Core
 * Description: Manages Bubbles Pets Rescue dogs, cats, pet details, statuses, galleries, and saved applications independently from the active theme.
 * Version: 1.2.0
 * Author: Bubbles Pets Rescue
 * Text Domain: bubbles-pet-rescue-core
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BPR_CORE_VERSION', '1.2.0');
define('BPR_CORE_FILE', __FILE__);
define('BPR_CORE_DIR', plugin_dir_path(__FILE__));
define('BPR_CORE_URL', plugin_dir_url(__FILE__));

require_once BPR_CORE_DIR . 'includes/helpers.php';
require_once BPR_CORE_DIR . 'includes/admin.php';
require_once BPR_CORE_DIR . 'includes/applications.php';
require_once BPR_CORE_DIR . 'includes/social.php';

function bpr_core_is_active() {
    return true;
}

function bpr_core_register_content_types() {
    $pet_supports = array('title', 'editor', 'thumbnail', 'excerpt', 'revisions');

    register_post_type('dog', array(
        'labels' => array(
            'name' => __('Dogs', 'bubbles-pet-rescue-core'),
            'singular_name' => __('Dog', 'bubbles-pet-rescue-core'),
            'add_new_item' => __('Add New Dog', 'bubbles-pet-rescue-core'),
            'edit_item' => __('Edit Dog', 'bubbles-pet-rescue-core'),
            'new_item' => __('New Dog', 'bubbles-pet-rescue-core'),
            'view_item' => __('View Dog', 'bubbles-pet-rescue-core'),
            'search_items' => __('Search Dogs', 'bubbles-pet-rescue-core'),
            'not_found' => __('No dogs found.', 'bubbles-pet-rescue-core'),
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'dogs', 'with_front' => false),
        'supports' => $pet_supports,
        'menu_icon' => 'dashicons-pets',
    ));

    register_post_type('cat', array(
        'labels' => array(
            'name' => __('Cats', 'bubbles-pet-rescue-core'),
            'singular_name' => __('Cat', 'bubbles-pet-rescue-core'),
            'add_new_item' => __('Add New Cat', 'bubbles-pet-rescue-core'),
            'edit_item' => __('Edit Cat', 'bubbles-pet-rescue-core'),
            'new_item' => __('New Cat', 'bubbles-pet-rescue-core'),
            'view_item' => __('View Cat', 'bubbles-pet-rescue-core'),
            'search_items' => __('Search Cats', 'bubbles-pet-rescue-core'),
            'not_found' => __('No cats found.', 'bubbles-pet-rescue-core'),
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'cats', 'with_front' => false),
        'supports' => $pet_supports,
        'menu_icon' => 'dashicons-pets',
    ));

    register_post_type('application', array(
        'labels' => array(
            'name' => __('Applications', 'bubbles-pet-rescue-core'),
            'singular_name' => __('Application', 'bubbles-pet-rescue-core'),
            'edit_item' => __('View Application', 'bubbles-pet-rescue-core'),
            'search_items' => __('Search Applications', 'bubbles-pet-rescue-core'),
            'not_found' => __('No applications found.', 'bubbles-pet-rescue-core'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'show_in_rest' => false,
        'supports' => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
    ));

    register_taxonomy('pet_status', array('dog', 'cat'), array(
        'labels' => array(
            'name' => __('Pet Statuses', 'bubbles-pet-rescue-core'),
            'singular_name' => __('Pet Status', 'bubbles-pet-rescue-core'),
            'menu_name' => __('Statuses', 'bubbles-pet-rescue-core'),
        ),
        'public' => true,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'pet-status', 'with_front' => false),
    ));
}
add_action('init', 'bpr_core_register_content_types');

function bpr_core_register_meta() {
    $post_types = array('dog', 'cat');
    $single_text_fields = array(
        '_bpr_age', '_bpr_age_range', '_bpr_gender', '_bpr_breed', '_bpr_other_breed',
        '_bpr_size', '_bpr_weight_kg', '_bpr_color', '_bpr_coat_length', '_bpr_location',
        '_bpr_energy_level', '_bpr_good_with', '_bpr_health', '_bpr_application_link',
        '_bpr_foster_link', '_bpr_good_with_dogs', '_bpr_good_with_cats',
        '_bpr_good_with_children', '_bpr_house_trained', '_bpr_crate_trained',
        '_bpr_leash_trained', '_bpr_apartment_suitable', '_bpr_can_be_left_alone',
        '_bpr_spayed_neutered', '_bpr_vaccinations_current', '_bpr_microchipped',
        '_bpr_dewormed', '_bpr_special_needs'
    );

    foreach ($post_types as $post_type) {
        foreach ($single_text_fields as $meta_key) {
            register_post_meta($post_type, $meta_key, array(
                'single' => true,
                'type' => 'string',
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                },
            ));
        }

        register_post_meta($post_type, '_bpr_breeds', array(
            'single' => true,
            'type' => 'array',
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array('type' => 'string'),
                ),
            ),
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        register_post_meta($post_type, '_bpr_personality_tags', array(
            'single' => true,
            'type' => 'array',
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array('type' => 'string'),
                ),
            ),
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        register_post_meta($post_type, '_bpr_gallery', array(
            'single' => true,
            'type' => 'array',
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array('type' => 'integer'),
                ),
            ),
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));
    }
}
add_action('init', 'bpr_core_register_meta', 20);

function bpr_core_seed_statuses() {
    $statuses = array(
        'available' => 'Available',
        'foster-needed' => 'Foster Needed',
        'adoption-pending' => 'Adoption Pending',
        'adopted' => 'Adopted',
        'medical-care' => 'Medical Care',
        'coming-soon' => 'Coming Soon',
    );

    foreach ($statuses as $slug => $name) {
        if (!term_exists($slug, 'pet_status')) {
            wp_insert_term($name, 'pet_status', array('slug' => $slug));
        }
    }
}

function bpr_core_activate() {
    bpr_core_register_content_types();
    bpr_core_seed_statuses();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'bpr_core_activate');

function bpr_core_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'bpr_core_deactivate');
