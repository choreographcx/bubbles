<?php
/**
 * Social announcements.
 *
 * When a dog or cat is first published, POSTs a JSON payload describing the
 * pet (name, details, photos, link) to a configurable webhook URL. Connect
 * that webhook to Make.com / Zapier / a scheduler to fan the announcement out
 * to Facebook, Instagram, TikTok, and anywhere else.
 */

if (!defined('ABSPATH')) {
    exit;
}

/*
 * =========================
 * Settings
 * =========================
 */

function bpr_core_register_social_settings() {
    register_setting('bpr_core_social', 'bpr_core_social_webhook_url', array(
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ));
    register_setting('bpr_core_social', 'bpr_core_social_auto', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '1',
    ));
}
add_action('admin_init', 'bpr_core_register_social_settings');

function bpr_core_register_social_page() {
    add_submenu_page(
        'bubbles-pet-rescue',
        __('Social Sharing', 'bubbles-pet-rescue-core'),
        __('Social Sharing', 'bubbles-pet-rescue-core'),
        'manage_options',
        'bpr-social',
        'bpr_core_render_social_page'
    );
}
add_action('admin_menu', 'bpr_core_register_social_page', 12);

function bpr_core_render_social_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'bubbles-pet-rescue-core'));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Social Sharing', 'bubbles-pet-rescue-core'); ?></h1>
        <p><?php esc_html_e('When a dog or cat is published, the details below are sent to your automation webhook (Make.com, Zapier, or similar), which can then post to Facebook, Instagram, TikTok, and more.', 'bubbles-pet-rescue-core'); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields('bpr_core_social'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bpr_core_social_webhook_url"><?php esc_html_e('Webhook URL', 'bubbles-pet-rescue-core'); ?></label></th>
                    <td>
                        <input type="url" class="regular-text" id="bpr_core_social_webhook_url" name="bpr_core_social_webhook_url" value="<?php echo esc_attr(get_option('bpr_core_social_webhook_url', '')); ?>" placeholder="https://hook.eu1.make.com/...">
                        <p class="description"><?php esc_html_e('From your Make.com or Zapier scenario. Leave empty to disable announcements.', 'bubbles-pet-rescue-core'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Automatic announcements', 'bubbles-pet-rescue-core'); ?></th>
                    <td>
                        <label><input type="checkbox" name="bpr_core_social_auto" value="1" <?php checked(get_option('bpr_core_social_auto', '1'), '1'); ?>> <?php esc_html_e('Announce each dog or cat automatically when first published', 'bubbles-pet-rescue-core'); ?></label>
                        <p class="description"><?php esc_html_e('Each pet is only announced once. Use the Social Sharing box on a pet\'s edit screen to announce it again.', 'bubbles-pet-rescue-core'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <h2><?php esc_html_e('What gets sent', 'bubbles-pet-rescue-core'); ?></h2>
        <p><?php esc_html_e('A JSON payload with: name, dog/cat, age, gender, breed, status, personality tags, a short description, the profile link, and up to four photo URLs (featured image first).', 'bubbles-pet-rescue-core'); ?></p>
    </div>
    <?php
}

/*
 * =========================
 * Payload + sending
 * =========================
 */

function bpr_core_social_payload($post) {
    $post_id = $post->ID;

    $images = array();
    $featured_id = get_post_thumbnail_id($post_id);
    if ($featured_id) {
        $images[] = wp_get_attachment_image_url($featured_id, 'large');
    }
    if (function_exists('bpr_core_get_gallery_ids')) {
        foreach (bpr_core_get_gallery_ids($post_id, false) as $image_id) {
            if (count($images) >= 4) {
                break;
            }
            $url = wp_get_attachment_image_url($image_id, 'large');
            if ($url && !in_array($url, $images, true)) {
                $images[] = $url;
            }
        }
    }

    $excerpt = get_the_excerpt($post);
    if (!$excerpt) {
        $excerpt = wp_trim_words(wp_strip_all_tags((string) $post->post_content), 40);
    }

    $status_terms = get_the_terms($post_id, 'pet_status');
    $status = ($status_terms && !is_wp_error($status_terms)) ? $status_terms[0]->name : '';

    return array(
        'event' => 'pet_published',
        'site' => home_url('/'),
        'pet' => array(
            'id' => $post_id,
            'type' => get_post_type($post_id),
            'name' => get_the_title($post_id),
            'url' => get_permalink($post_id),
            'age' => function_exists('bpr_core_get_age_display') ? bpr_core_get_age_display($post_id) : '',
            'gender' => trim((string) get_post_meta($post_id, '_bpr_gender', true)),
            'breed' => function_exists('bpr_core_get_breed_display') ? bpr_core_get_breed_display($post_id) : '',
            'status' => $status,
            'tags' => function_exists('bpr_core_get_personality_tags') ? bpr_core_get_personality_tags($post_id) : array(),
            'description' => $excerpt,
            'images' => array_values(array_filter($images)),
        ),
    );
}

/**
 * Send the webhook for a pet. Returns true when a request was dispatched.
 */
function bpr_core_send_social_webhook($post_id) {
    $webhook = trim((string) get_option('bpr_core_social_webhook_url', ''));
    if ($webhook === '') {
        return false;
    }

    $post = get_post($post_id);
    if (!$post || !in_array($post->post_type, array('dog', 'cat'), true) || $post->post_status !== 'publish') {
        return false;
    }

    wp_remote_post($webhook, array(
        'timeout' => 15,
        'blocking' => false,
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode(bpr_core_social_payload($post)),
    ));

    update_post_meta($post_id, '_bpr_social_announced', current_time('mysql'));
    return true;
}

/**
 * Auto-announce on first publish. wp_after_insert_post runs after the pet's
 * meta fields have been saved, so the payload carries the full details.
 */
function bpr_core_social_on_publish($post_id, $post, $update, $post_before) {
    if (get_option('bpr_core_social_auto', '1') !== '1') {
        return;
    }
    if (!in_array($post->post_type, array('dog', 'cat'), true) || $post->post_status !== 'publish') {
        return;
    }
    if ($post_before && $post_before->post_status === 'publish') {
        return; // Not the first publish.
    }
    if (get_post_meta($post_id, '_bpr_social_announced', true)) {
        return; // Already announced once.
    }
    bpr_core_send_social_webhook($post_id);
}
add_action('wp_after_insert_post', 'bpr_core_social_on_publish', 20, 4);

/*
 * =========================
 * Re-announce box on the pet edit screen
 * =========================
 */

function bpr_core_social_meta_box() {
    add_meta_box(
        'bpr_core_social',
        __('Social Sharing', 'bubbles-pet-rescue-core'),
        'bpr_core_render_social_meta_box',
        array('dog', 'cat'),
        'side'
    );
}
add_action('add_meta_boxes', 'bpr_core_social_meta_box');

function bpr_core_render_social_meta_box($post) {
    wp_nonce_field('bpr_core_social_box', 'bpr_core_social_nonce');
    $announced = get_post_meta($post->ID, '_bpr_social_announced', true);

    if (!trim((string) get_option('bpr_core_social_webhook_url', ''))) {
        echo '<p>' . esc_html__('No webhook configured. Set one under Bubbles Pets Rescue > Social Sharing.', 'bubbles-pet-rescue-core') . '</p>';
        return;
    }

    if ($announced) {
        echo '<p>' . esc_html(sprintf(__('Announced on %s.', 'bubbles-pet-rescue-core'), $announced)) . '</p>';
    } else {
        echo '<p>' . esc_html__('Not announced yet. Publishing will announce automatically.', 'bubbles-pet-rescue-core') . '</p>';
    }

    echo '<label><input type="checkbox" name="bpr_core_social_reannounce" value="1"> '
        . esc_html__('Announce to social when I save this post', 'bubbles-pet-rescue-core') . '</label>';
}

function bpr_core_social_handle_reannounce($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (empty($_POST['bpr_core_social_reannounce'])) {
        return;
    }
    if (!isset($_POST['bpr_core_social_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bpr_core_social_nonce'])), 'bpr_core_social_box')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    bpr_core_send_social_webhook($post_id);
}
add_action('save_post_dog', 'bpr_core_social_handle_reannounce', 30);
add_action('save_post_cat', 'bpr_core_social_handle_reannounce', 30);
