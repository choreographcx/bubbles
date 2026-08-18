<?php
if (!defined('ABSPATH')) {
    exit;
}

define('BPR_THEME_VERSION', '1.2.8');

function bpr_theme_setup() {
    load_theme_textdomain('bubbles-pet-rescue', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height' => 160, 'width' => 500, 'flex-width' => true, 'flex-height' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'bubbles-pet-rescue'),
        'footer' => __('Footer Menu', 'bubbles-pet-rescue'),
    ));
}
add_action('after_setup_theme', 'bpr_theme_setup');

// Category for the starter page patterns in /patterns (files auto-register).
function bpr_register_pattern_category() {
    if (function_exists('register_block_pattern_category')) {
        register_block_pattern_category('bubbles-pet-rescue', array(
            'label' => __('Bubbles Pets Rescue', 'bubbles-pet-rescue'),
        ));
    }
}
add_action('init', 'bpr_register_pattern_category');

function bpr_enqueue_assets() {
    wp_enqueue_style('bpr-fonts', 'https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Nunito+Sans:wght@400;500;600;700;800&display=swap', array(), null);
    wp_enqueue_style('bpr-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), '5.3.3');
    wp_enqueue_style('bpr-bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css', array(), '1.11.3');
    wp_enqueue_style('bpr-style', get_stylesheet_uri(), array('bpr-fonts', 'bpr-bootstrap'), BPR_THEME_VERSION);
    wp_enqueue_style(
        'bpr-mobile-layout',
        get_template_directory_uri() . '/assets/css/mobile-layout-v1.2.4.css',
        array('bpr-style'),
        BPR_THEME_VERSION
    );
    wp_enqueue_script('bpr-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array(), '5.3.3', true);
}
add_action('wp_enqueue_scripts', 'bpr_enqueue_assets');

/*
 * Compatibility fallback for sites where the companion Bubbles Pets Rescue Core
 * plugin hasn't been activated yet. The plugin owns these content types when active.
 */
function bpr_register_legacy_content_types() {
    $common_supports = array('title', 'editor', 'thumbnail', 'excerpt', 'revisions');

    register_post_type('dog', array(
        'labels' => array(
            'name' => __('Dogs', 'bubbles-pet-rescue'),
            'singular_name' => __('Dog', 'bubbles-pet-rescue'),
            'add_new_item' => __('Add New Dog', 'bubbles-pet-rescue'),
            'edit_item' => __('Edit Dog', 'bubbles-pet-rescue'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-pets',
        'rewrite' => array('slug' => 'dogs', 'with_front' => false),
        'supports' => $common_supports,
        'show_in_rest' => true,
    ));

    register_post_type('cat', array(
        'labels' => array(
            'name' => __('Cats', 'bubbles-pet-rescue'),
            'singular_name' => __('Cat', 'bubbles-pet-rescue'),
            'add_new_item' => __('Add New Cat', 'bubbles-pet-rescue'),
            'edit_item' => __('Edit Cat', 'bubbles-pet-rescue'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-pets',
        'rewrite' => array('slug' => 'cats', 'with_front' => false),
        'supports' => $common_supports,
        'show_in_rest' => true,
    ));

    register_post_type('application', array(
        'labels' => array(
            'name' => __('Applications', 'bubbles-pet-rescue'),
            'singular_name' => __('Application', 'bubbles-pet-rescue'),
        ),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
    ));

    register_taxonomy('pet_status', array('dog', 'cat'), array(
        'labels' => array(
            'name' => __('Pet Statuses', 'bubbles-pet-rescue'),
            'singular_name' => __('Pet Status', 'bubbles-pet-rescue'),
        ),
        'public' => true,
        'hierarchical' => true,
        'rewrite' => array('slug' => 'pet-status', 'with_front' => false),
        'show_in_rest' => true,
    ));
}

if (!function_exists('bpr_core_is_active')) {
    add_action('init', 'bpr_register_legacy_content_types');
}

function bpr_legacy_activation_terms() {
    if (function_exists('bpr_core_is_active')) {
        return;
    }

    $terms = array('Available', 'Foster Needed', 'Adoption Pending', 'Adopted', 'Medical Care', 'Coming Soon');
    foreach ($terms as $term) {
        if (!term_exists($term, 'pet_status')) {
            wp_insert_term($term, 'pet_status');
        }
    }
}
add_action('after_switch_theme', 'bpr_legacy_activation_terms');

function bpr_add_legacy_pet_meta_boxes() {
    if (function_exists('bpr_core_is_active')) {
        return;
    }
    add_meta_box('bpr_pet_details', __('Basic Pet Details', 'bubbles-pet-rescue'), 'bpr_legacy_pet_details_meta_box', array('dog', 'cat'), 'normal', 'high');
}
add_action('add_meta_boxes', 'bpr_add_legacy_pet_meta_boxes');

function bpr_legacy_pet_details_meta_box($post) {
    wp_nonce_field('bpr_save_pet_details', 'bpr_pet_details_nonce');
    $fields = array(
        'age' => 'Age',
        'gender' => 'Gender',
        'breed' => 'Breed',
        'size' => 'Size',
        'location' => 'Location',
        'good_with' => 'Good With',
        'health' => 'Health Notes',
        'application_link' => 'Application Link',
    );

    echo '<div class="row">';
    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, '_bpr_' . $key, true);
        $col = in_array($key, array('health', 'good_with'), true) ? 'col-12' : 'col-md-6';
        echo '<p class="' . esc_attr($col) . '"><label style="font-weight:600;display:block;margin-bottom:4px;">' . esc_html($label) . '</label>';
        if (in_array($key, array('health', 'good_with'), true)) {
            echo '<textarea name="bpr_' . esc_attr($key) . '" rows="4" style="width:100%;">' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<input type="text" name="bpr_' . esc_attr($key) . '" value="' . esc_attr($value) . '" style="width:100%;">';
        }
        echo '</p>';
    }
    echo '</div><p><strong>For image galleries and the full pet profile, activate the Bubbles Pets Rescue Core plugin.</strong></p>';
}

function bpr_save_legacy_pet_details($post_id) {
    if (function_exists('bpr_core_is_active')) {
        return;
    }
    if (!isset($_POST['bpr_pet_details_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bpr_pet_details_nonce'])), 'bpr_save_pet_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $keys = array('age', 'gender', 'breed', 'size', 'location', 'good_with', 'health', 'application_link');
    foreach ($keys as $key) {
        if (!isset($_POST['bpr_' . $key])) {
            continue;
        }
        $value = $key === 'application_link'
            ? esc_url_raw(wp_unslash($_POST['bpr_' . $key]))
            : sanitize_textarea_field(wp_unslash($_POST['bpr_' . $key]));
        update_post_meta($post_id, '_bpr_' . $key, $value);
    }
}
add_action('save_post_dog', 'bpr_save_legacy_pet_details');
add_action('save_post_cat', 'bpr_save_legacy_pet_details');

function bpr_customize_register($wp_customize) {
    $wp_customize->add_section('bpr_rescue_settings', array(
        'title' => __('Bubbles Rescue Settings', 'bubbles-pet-rescue'),
        'priority' => 30,
    ));

    $settings = array(
        'bpr_amazon_wishlist_url' => array('Amazon Wishlist URL', 'https://www.amazon.ae/hz/wishlist/ls/1TTLP5QW4EVHM'),
        'bpr_instagram_url' => array('Instagram URL', ''),
        'bpr_whatsapp_url' => array('WhatsApp URL', ''),
        'bpr_contact_email' => array('Contact Email', get_option('admin_email')),
        'bpr_hero_title' => array('Hero Title', 'Helping UAE rescue pets find safe, loving homes'),
        'bpr_hero_text' => array('Hero Text', 'Bubbles Pets Rescue connects dogs and cats with adopters, fosters, and practical support through wishlist items and care supplies.'),
        'bpr_home_intro_title' => array('Homepage Rescue Heading', 'Bubbles Pets Rescue'),
    );

    foreach ($settings as $key => $data) {
        $sanitize = $key === 'bpr_contact_email' ? 'sanitize_email' : 'sanitize_text_field';
        $wp_customize->add_setting($key, array('default' => $data[1], 'sanitize_callback' => $sanitize));
        $wp_customize->add_control($key, array(
            'label' => __($data[0], 'bubbles-pet-rescue'),
            'section' => 'bpr_rescue_settings',
            'type' => 'text',
        ));
    }

    $wp_customize->add_setting('bpr_home_intro_text', array(
        'default' => 'We’re a UAE community rescue helping dogs and cats move from uncertainty into safe foster care and loving permanent homes.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('bpr_home_intro_text', array(
        'label' => __('Homepage Rescue Introduction', 'bubbles-pet-rescue'),
        'section' => 'bpr_rescue_settings',
        'type' => 'textarea',
    ));

    $wp_customize->add_setting('bpr_home_mission_text', array(
        'default' => 'Our mission is to rescue responsibly, support every pet’s individual needs, and find homes where they’ll be safe, understood, and loved for life.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('bpr_home_mission_text', array(
        'label' => __('Homepage Mission Text', 'bubbles-pet-rescue'),
        'section' => 'bpr_rescue_settings',
        'type' => 'textarea',
    ));

    $pet_choices = array(0 => __('Automatically choose an available pet', 'bubbles-pet-rescue'));
    $pets = get_posts(array(
        'post_type' => array('dog', 'cat'),
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    foreach ($pets as $pet) {
        $type_label = get_post_type($pet->ID) === 'cat' ? __('Cat', 'bubbles-pet-rescue') : __('Dog', 'bubbles-pet-rescue');
        $pet_choices[$pet->ID] = sprintf('%s: %s', $type_label, $pet->post_title);
    }

    $wp_customize->add_setting('bpr_featured_pet_id', array(
        'default' => 0,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('bpr_featured_pet_id', array(
        'label' => __('Homepage Featured Pet', 'bubbles-pet-rescue'),
        'description' => __('Choose the pet shown in the homepage feature. Automatic selection prioritizes pets with an Available or Adoptable status.', 'bubbles-pet-rescue'),
        'section' => 'bpr_rescue_settings',
        'type' => 'select',
        'choices' => $pet_choices,
    ));
}
add_action('customize_register', 'bpr_customize_register');

function bpr_get_pet_breed($post_id) {
    if (function_exists('bpr_core_get_breed_display')) {
        return bpr_core_get_breed_display($post_id);
    }
    return trim((string) get_post_meta($post_id, '_bpr_breed', true));
}

function bpr_get_pet_age($post_id) {
    $age = function_exists('bpr_core_get_age_display')
        ? bpr_core_get_age_display($post_id)
        : trim((string) get_post_meta($post_id, '_bpr_age', true));

    if (strpos($age, ':') !== false) {
        $age = trim(strstr($age, ':', true));
    }

    return $age;
}

function bpr_get_pet_primary_image_id($post_id) {
    if (function_exists('bpr_core_get_primary_image_id')) {
        return bpr_core_get_primary_image_id($post_id);
    }
    return (int) get_post_thumbnail_id($post_id);
}

function bpr_get_home_featured_pet_id() {
    $selected_id = absint(get_theme_mod('bpr_featured_pet_id', 0));

    if ($selected_id && in_array(get_post_type($selected_id), array('dog', 'cat'), true) && get_post_status($selected_id) === 'publish') {
        return $selected_id;
    }

    $query_args = array(
        'post_type' => array('dog', 'cat'),
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'orderby' => array('modified' => 'DESC', 'date' => 'DESC'),
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
    );

    if (taxonomy_exists('pet_status')) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'pet_status',
                'field' => 'slug',
                'terms' => array('available', 'adoptable'),
                'operator' => 'IN',
            ),
        );
    }

    $featured_query = new WP_Query($query_args);
    if ($featured_query->have_posts()) {
        return (int) $featured_query->posts[0]->ID;
    }

    unset($query_args['tax_query']);
    $fallback_query = new WP_Query($query_args);
    return $fallback_query->have_posts() ? (int) $fallback_query->posts[0]->ID : 0;
}

function bpr_get_home_featured_pet_traits($post_id, $limit = 3) {
    $trait_fields = array(
        '_bpr_spayed_neutered' => __('Spayed/Neutered', 'bubbles-pet-rescue'),
        '_bpr_vaccinations_current' => __('Vaccinations Current', 'bubbles-pet-rescue'),
        '_bpr_good_with_dogs' => __('Good with Dogs', 'bubbles-pet-rescue'),
        '_bpr_good_with_cats' => __('Good with Cats', 'bubbles-pet-rescue'),
        '_bpr_good_with_children' => __('Good with Children', 'bubbles-pet-rescue'),
        '_bpr_house_trained' => __('House Trained', 'bubbles-pet-rescue'),
    );
    $positive_values = array('yes', 'true', 'current', 'good', 'suitable');
    $traits = array();

    foreach ($trait_fields as $meta_key => $label) {
        $value = strtolower(trim((string) get_post_meta($post_id, $meta_key, true)));
        if ($value && in_array($value, $positive_values, true)) {
            $traits[] = $label;
        }
        if (count($traits) >= $limit) {
            return $traits;
        }
    }

    $personality = get_post_meta($post_id, '_bpr_personality_tags', true);
    if (is_array($personality)) {
        foreach ($personality as $tag) {
            $tag = trim((string) $tag);
            if ($tag && !in_array($tag, $traits, true)) {
                $traits[] = $tag;
            }
            if (count($traits) >= $limit) {
                break;
            }
        }
    }

    return $traits;
}

function bpr_pet_card($post_id = null, $variant = 'default') {
    $post_id = $post_id ?: get_the_ID();
    $type = get_post_type($post_id);
    $breed = bpr_get_pet_breed($post_id);
    $age = bpr_get_pet_age($post_id);
    $gender = trim((string) get_post_meta($post_id, '_bpr_gender', true));
    $terms = get_the_terms($post_id, 'pet_status');
    $image_id = bpr_get_pet_primary_image_id($post_id);
    $permalink = get_permalink($post_id);
    $title = get_the_title($post_id);

    if ($variant === 'dog-archive') {
        $status_slugs = wp_get_post_terms($post_id, 'pet_status', array('fields' => 'slugs'));
        $status_slugs = is_wp_error($status_slugs) ? array() : $status_slugs;
        $card_classes = array('bpr-dog-directory-card', 'h-100');

        if (array_intersect($status_slugs, array('coming-soon', 'medical-care', 'adoption-pending'))) {
            $card_classes[] = 'bpr-dog-directory-card-soon';
        }
        ?>
        <article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>">
            <a class="bpr-dog-directory-image-link" href="<?php echo esc_url($permalink); ?>" aria-label="Meet <?php echo esc_attr($title); ?>">
                <?php
                if ($image_id) {
                    echo wp_get_attachment_image($image_id, 'large', false, array(
                        'class' => 'bpr-dog-directory-image',
                        'loading' => 'lazy',
                        'sizes' => '(max-width: 767px) calc(100vw - 2rem), (max-width: 991px) 50vw, 300px',
                    ));
                } else {
                    echo '<span class="bpr-dog-directory-image bpr-dog-directory-placeholder"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>';
                }
                ?>
            </a>
            <div class="bpr-dog-directory-body">
                <h2 class="bpr-dog-directory-name"><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h2>
                <?php if ($age || $gender) : ?>
                    <p class="bpr-dog-directory-meta"><?php echo esc_html(implode(' • ', array_filter(array($age, $gender)))); ?></p>
                <?php endif; ?>
                <?php if ($breed) : ?>
                    <p class="bpr-dog-directory-breed"><?php echo esc_html($breed); ?></p>
                <?php endif; ?>
            </div>
        </article>
        <?php
        return;
    }
    ?>
    <article class="bpr-card h-100 bpr-pet-card">
        <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>">
            <?php
            if ($image_id) {
                echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'bpr-pet-img', 'loading' => 'lazy'));
            } else {
                echo '<div class="bpr-pet-img d-flex align-items-center justify-content-center"><i class="bi bi-heart-pulse fs-1 text-primary"></i></div>';
            }
            ?>
        </a>
        <div class="p-4">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="bpr-badge"><?php echo esc_html(ucfirst($type)); ?></span>
                <?php if ($terms && !is_wp_error($terms)) : foreach ($terms as $term) : ?>
                    <span class="bpr-badge bpr-badge-coral"><?php echo esc_html($term->name); ?></span>
                <?php endforeach; endif; ?>
            </div>
            <h3 class="h4 bpr-section-title mb-2"><a class="text-decoration-none" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h3>
            <p class="text-muted mb-3"><?php echo esc_html(implode(' • ', array_filter(array($breed, $age, $gender)))); ?></p>
            <p class="mb-0"><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 18)); ?></p>
        </div>
    </article>
    <?php
}

function bpr_application_shortcode($atts) {
    $atts = shortcode_atts(array('type' => 'adoption'), $atts, 'bpr_application_form');
    $type = sanitize_key($atts['type']);
    $message = '';
    $prefilled_pet = isset($_GET['pet']) ? sanitize_text_field(wp_unslash($_GET['pet'])) : '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bpr_application_type']) && sanitize_key(wp_unslash($_POST['bpr_application_type'])) === $type) {
        if (!isset($_POST['bpr_application_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bpr_application_nonce'])), 'bpr_application_' . $type)) {
            $message = '<div class="alert alert-danger">Sorry, we couldn’t verify the form. Please try again.</div>';
        } else {
            $name = sanitize_text_field(wp_unslash($_POST['bpr_name'] ?? ''));
            $email = sanitize_email(wp_unslash($_POST['bpr_email'] ?? ''));
            $phone = sanitize_text_field(wp_unslash($_POST['bpr_phone'] ?? ''));
            $pet = sanitize_text_field(wp_unslash($_POST['bpr_pet'] ?? ''));
            $home = sanitize_textarea_field(wp_unslash($_POST['bpr_home'] ?? ''));
            $experience = sanitize_textarea_field(wp_unslash($_POST['bpr_experience'] ?? ''));
            $body = "Name: $name\nEmail: $email\nPhone: $phone\nPet: $pet\n\nHome details:\n$home\n\nExperience:\n$experience";
            $app_id = wp_insert_post(array(
                'post_type' => 'application',
                'post_status' => 'private',
                'post_title' => ucfirst($type) . ' application from ' . $name,
                'post_content' => $body,
            ));

            if ($app_id && !is_wp_error($app_id)) {
                update_post_meta($app_id, '_bpr_application_type', $type);
                wp_mail(get_option('admin_email'), 'New ' . $type . ' application: ' . $name, $body);
                $message = '<div class="alert alert-success">Thank you. Your application has been sent to the rescue team.</div>';
            } else {
                $message = '<div class="alert alert-danger">Something went wrong. Please contact us directly.</div>';
            }
        }
    }

    ob_start();
    echo $message;
    ?>
    <form method="post" class="bpr-form bpr-card p-4 p-lg-5">
        <?php wp_nonce_field('bpr_application_' . $type, 'bpr_application_nonce'); ?>
        <input type="hidden" name="bpr_application_type" value="<?php echo esc_attr($type); ?>">
        <div class="row g-3">
            <div class="col-md-6"><label>Your Name</label><input class="form-control" name="bpr_name" required></div>
            <div class="col-md-6"><label>Email</label><input class="form-control" type="email" name="bpr_email" required></div>
            <div class="col-md-6"><label>Phone or WhatsApp</label><input class="form-control" name="bpr_phone" required></div>
            <div class="col-md-6"><label>Pet Name, if Known</label><input class="form-control" name="bpr_pet" value="<?php echo esc_attr($prefilled_pet); ?>"></div>
            <div class="col-12"><label>Tell Us About Your Home</label><textarea class="form-control" name="bpr_home" rows="4" required></textarea></div>
            <div class="col-12"><label>Previous Pet Experience</label><textarea class="form-control" name="bpr_experience" rows="4"></textarea></div>
            <div class="col-12"><button class="btn btn-bpr-primary" type="submit">Send <?php echo esc_html(ucfirst($type)); ?> Application</button></div>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('bpr_application_form', 'bpr_application_shortcode');
add_shortcode('bubbles_adoption_application', function() {
    return bpr_application_shortcode(array('type' => 'adoption'));
});
add_shortcode('bubbles_foster_application', function() {
    return bpr_application_shortcode(array('type' => 'foster'));
});
