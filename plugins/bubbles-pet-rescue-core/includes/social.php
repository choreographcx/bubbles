<?php
/**
 * Social announcements — direct Meta (Facebook + Instagram) integration.
 *
 * When a dog or cat is first published, the plugin posts a photo + caption
 * directly to the configured Facebook Page and Instagram Business account
 * via the Meta Graph API. An optional webhook can also be notified for any
 * further automation. Results are logged per pet for troubleshooting.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BPR_CORE_GRAPH', 'https://graph.facebook.com/v21.0/');

/*
 * =========================
 * Settings
 * =========================
 */

function bpr_core_register_social_settings() {
    $settings = array(
        'bpr_core_meta_token' => 'sanitize_text_field',
        'bpr_core_meta_page_id' => 'sanitize_text_field',
        'bpr_core_meta_ig_id' => 'sanitize_text_field',
        'bpr_core_social_webhook_url' => 'esc_url_raw',
        'bpr_core_social_auto' => 'sanitize_text_field',
    );
    foreach ($settings as $key => $sanitize) {
        register_setting('bpr_core_social', $key, array(
            'type' => 'string',
            'sanitize_callback' => $sanitize,
            'default' => $key === 'bpr_core_social_auto' ? '1' : '',
        ));
    }
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

    $verify = get_transient('bpr_core_meta_verify_' . get_current_user_id());
    if ($verify) {
        delete_transient('bpr_core_meta_verify_' . get_current_user_id());
        $class = !empty($verify['ok']) ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . esc_attr($class) . '"><p>' . wp_kses_post($verify['message']) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Social Sharing', 'bubbles-pet-rescue-core'); ?></h1>
        <p><?php esc_html_e('When a dog or cat is published, a photo post is sent directly to your Facebook Page and Instagram account through the Meta API. Each pet is announced once; use the Social Sharing box on a pet\'s edit screen to announce it again.', 'bubbles-pet-rescue-core'); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields('bpr_core_social'); ?>
            <h2><?php esc_html_e('Meta connection (Facebook + Instagram)', 'bubbles-pet-rescue-core'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bpr_core_meta_token"><?php esc_html_e('Access token', 'bubbles-pet-rescue-core'); ?></label></th>
                    <td>
                        <input type="password" class="regular-text" id="bpr_core_meta_token" name="bpr_core_meta_token" value="<?php echo esc_attr(get_option('bpr_core_meta_token', '')); ?>" autocomplete="off">
                        <p class="description"><?php esc_html_e('A non-expiring System User token from Meta Business Manager with pages_manage_posts, pages_read_engagement, instagram_basic, and instagram_content_publish permissions.', 'bubbles-pet-rescue-core'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bpr_core_meta_page_id"><?php esc_html_e('Facebook Page ID', 'bubbles-pet-rescue-core'); ?></label></th>
                    <td><input type="text" class="regular-text" id="bpr_core_meta_page_id" name="bpr_core_meta_page_id" value="<?php echo esc_attr(get_option('bpr_core_meta_page_id', '')); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bpr_core_meta_ig_id"><?php esc_html_e('Instagram account ID', 'bubbles-pet-rescue-core'); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="bpr_core_meta_ig_id" name="bpr_core_meta_ig_id" value="<?php echo esc_attr(get_option('bpr_core_meta_ig_id', '')); ?>">
                        <p class="description"><?php esc_html_e('Leave empty and click Verify connection: it is filled in automatically from the Instagram account linked to your Page.', 'bubbles-pet-rescue-core'); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e('Options', 'bubbles-pet-rescue-core'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Automatic announcements', 'bubbles-pet-rescue-core'); ?></th>
                    <td><label><input type="checkbox" name="bpr_core_social_auto" value="1" <?php checked(get_option('bpr_core_social_auto', '1'), '1'); ?>> <?php esc_html_e('Announce each dog or cat automatically when first published', 'bubbles-pet-rescue-core'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bpr_core_social_webhook_url"><?php esc_html_e('Webhook URL (optional)', 'bubbles-pet-rescue-core'); ?></label></th>
                    <td>
                        <input type="url" class="regular-text" id="bpr_core_social_webhook_url" name="bpr_core_social_webhook_url" value="<?php echo esc_attr(get_option('bpr_core_social_webhook_url', '')); ?>">
                        <p class="description"><?php esc_html_e('Also send the pet details to this URL as JSON, for any extra automation (e.g. a TikTok scheduler or notifications).', 'bubbles-pet-rescue-core'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:-0.5rem;">
            <?php wp_nonce_field('bpr_core_meta_verify'); ?>
            <input type="hidden" name="action" value="bpr_core_meta_verify">
            <button type="submit" class="button button-secondary"><?php esc_html_e('Verify connection', 'bubbles-pet-rescue-core'); ?></button>
            <p class="description"><?php esc_html_e('Checks the saved token against your Page and Instagram account without posting anything.', 'bubbles-pet-rescue-core'); ?></p>
        </form>
    </div>
    <?php
}

/*
 * =========================
 * Graph API helpers
 * =========================
 */

function bpr_core_graph_request($method, $endpoint, $params = array()) {
    $token = trim((string) get_option('bpr_core_meta_token', ''));
    if ($token === '') {
        return new WP_Error('bpr_no_token', __('No Meta access token configured.', 'bubbles-pet-rescue-core'));
    }
    $params['access_token'] = $token;

    if ($method === 'GET') {
        $response = wp_remote_get(BPR_CORE_GRAPH . $endpoint . '?' . http_build_query($params), array('timeout' => 20));
    } else {
        $response = wp_remote_post(BPR_CORE_GRAPH . $endpoint, array('timeout' => 30, 'body' => $params));
    }

    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($body)) {
        return new WP_Error('bpr_bad_response', __('Unexpected response from Meta.', 'bubbles-pet-rescue-core'));
    }
    if (!empty($body['error'])) {
        $message = isset($body['error']['message']) ? (string) $body['error']['message'] : __('Unknown Meta API error.', 'bubbles-pet-rescue-core');
        return new WP_Error('bpr_graph_error', $message);
    }

    return $body;
}

/**
 * Page access token derived from the system-user token (cached for an hour).
 * Falls back to the stored token when it already is a Page token.
 */
function bpr_core_meta_page_token() {
    $page_id = trim((string) get_option('bpr_core_meta_page_id', ''));
    if ($page_id === '') {
        return new WP_Error('bpr_no_page', __('No Facebook Page ID configured.', 'bubbles-pet-rescue-core'));
    }

    $cached = get_transient('bpr_core_meta_page_token');
    if (is_string($cached) && $cached !== '') {
        return $cached;
    }

    $result = bpr_core_graph_request('GET', $page_id, array('fields' => 'access_token'));
    if (is_wp_error($result)) {
        return trim((string) get_option('bpr_core_meta_token', ''));
    }

    $token = !empty($result['access_token']) ? (string) $result['access_token'] : trim((string) get_option('bpr_core_meta_token', ''));
    set_transient('bpr_core_meta_page_token', $token, HOUR_IN_SECONDS);
    return $token;
}

function bpr_core_handle_meta_verify() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Not allowed.', 'bubbles-pet-rescue-core'));
    }
    check_admin_referer('bpr_core_meta_verify');

    $page_id = trim((string) get_option('bpr_core_meta_page_id', ''));
    $messages = array();
    $ok = true;

    $page = $page_id !== '' ? bpr_core_graph_request('GET', $page_id, array('fields' => 'name,instagram_business_account{id,username}')) : new WP_Error('bpr_no_page', __('Enter and save a Facebook Page ID first.', 'bubbles-pet-rescue-core'));

    if (is_wp_error($page)) {
        $ok = false;
        $messages[] = '<strong>Facebook:</strong> ' . esc_html($page->get_error_message());
    } else {
        $messages[] = '<strong>Facebook:</strong> connected to page "' . esc_html($page['name']) . '".';
        if (!empty($page['instagram_business_account']['id'])) {
            update_option('bpr_core_meta_ig_id', (string) $page['instagram_business_account']['id']);
            $username = !empty($page['instagram_business_account']['username']) ? '@' . $page['instagram_business_account']['username'] : $page['instagram_business_account']['id'];
            $messages[] = '<strong>Instagram:</strong> connected to ' . esc_html($username) . '.';
        } elseif (trim((string) get_option('bpr_core_meta_ig_id', '')) === '') {
            $ok = false;
            $messages[] = '<strong>Instagram:</strong> no Instagram Business account is linked to this Page. Link it in your Page settings (the Instagram account must be a Professional account).';
        }
        delete_transient('bpr_core_meta_page_token');
    }

    set_transient('bpr_core_meta_verify_' . get_current_user_id(), array('ok' => $ok, 'message' => implode('<br>', $messages)), 60);
    wp_safe_redirect(admin_url('admin.php?page=bpr-social'));
    exit;
}
add_action('admin_post_bpr_core_meta_verify', 'bpr_core_handle_meta_verify');

/*
 * =========================
 * Payload + caption
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

function bpr_core_social_caption($payload, $network) {
    $pet = $payload['pet'];
    $details = implode(' | ', array_filter(array($pet['age'], $pet['gender'], $pet['breed'])));

    $lines = array();
    $lines[] = 'Meet ' . $pet['name'] . '!';
    if ($details !== '') {
        $lines[] = $details;
    }
    if ($pet['description'] !== '') {
        $lines[] = '';
        $lines[] = $pet['description'];
    }
    $lines[] = '';
    if ($network === 'instagram') {
        $lines[] = 'Apply to adopt through the link in our bio, or WhatsApp +971 50 808 3083.';
    } else {
        $lines[] = 'Apply to adopt: ' . $pet['url'];
    }
    $lines[] = '';
    $species = ($pet['type'] === 'cat') ? '#UAECats #CatsOfDubai' : '#UAEDogs #DogsOfDubai';
    $lines[] = '#AdoptDontShop #UAERescue #DubaiPets ' . $species;

    $caption = implode("\n", $lines);
    return apply_filters('bpr_core_social_caption', $caption, $payload, $network);
}

/*
 * =========================
 * Posting
 * =========================
 */

function bpr_core_social_post_facebook($payload) {
    $page_id = trim((string) get_option('bpr_core_meta_page_id', ''));
    if ($page_id === '') {
        return new WP_Error('bpr_no_page', __('No Facebook Page ID configured.', 'bubbles-pet-rescue-core'));
    }

    $page_token = bpr_core_meta_page_token();
    if (is_wp_error($page_token)) {
        return $page_token;
    }

    $caption = bpr_core_social_caption($payload, 'facebook');
    $image = !empty($payload['pet']['images'][0]) ? $payload['pet']['images'][0] : '';

    if ($image !== '') {
        $params = array('url' => $image, 'caption' => $caption, 'access_token' => $page_token);
        $endpoint = $page_id . '/photos';
    } else {
        $params = array('message' => $caption, 'link' => $payload['pet']['url'], 'access_token' => $page_token);
        $endpoint = $page_id . '/feed';
    }

    $response = wp_remote_post(BPR_CORE_GRAPH . $endpoint, array('timeout' => 30, 'body' => $params));
    if (is_wp_error($response)) {
        return $response;
    }
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($body) || !empty($body['error'])) {
        return new WP_Error('bpr_graph_error', isset($body['error']['message']) ? (string) $body['error']['message'] : __('Unknown Meta API error.', 'bubbles-pet-rescue-core'));
    }

    return isset($body['post_id']) ? (string) $body['post_id'] : (isset($body['id']) ? (string) $body['id'] : 'ok');
}

function bpr_core_social_post_instagram($payload) {
    $ig_id = trim((string) get_option('bpr_core_meta_ig_id', ''));
    if ($ig_id === '') {
        return new WP_Error('bpr_no_ig', __('No Instagram account ID configured. Click Verify connection on the Social Sharing page.', 'bubbles-pet-rescue-core'));
    }
    $image = !empty($payload['pet']['images'][0]) ? $payload['pet']['images'][0] : '';
    if ($image === '') {
        return new WP_Error('bpr_no_image', __('Instagram requires a photo; set a featured image for this pet.', 'bubbles-pet-rescue-core'));
    }

    // Step 1: create the media container.
    $container = bpr_core_graph_request('POST', $ig_id . '/media', array(
        'image_url' => $image,
        'caption' => bpr_core_social_caption($payload, 'instagram'),
    ));
    if (is_wp_error($container)) {
        return $container;
    }
    if (empty($container['id'])) {
        return new WP_Error('bpr_ig_container', __('Instagram did not return a media container.', 'bubbles-pet-rescue-core'));
    }

    // Step 2: publish it.
    $publish = bpr_core_graph_request('POST', $ig_id . '/media_publish', array(
        'creation_id' => (string) $container['id'],
    ));
    if (is_wp_error($publish)) {
        return $publish;
    }

    return isset($publish['id']) ? (string) $publish['id'] : 'ok';
}

/**
 * Announce a pet everywhere that is configured, and log the results.
 */
function bpr_core_social_announce($post_id) {
    $post = get_post($post_id);
    if (!$post || !in_array($post->post_type, array('dog', 'cat'), true) || $post->post_status !== 'publish') {
        return;
    }

    $payload = bpr_core_social_payload($post);
    $log = array('time' => current_time('mysql'));

    if (trim((string) get_option('bpr_core_meta_token', '')) !== '') {
        $fb = bpr_core_social_post_facebook($payload);
        $log['facebook'] = is_wp_error($fb) ? 'Error: ' . $fb->get_error_message() : 'Posted (' . $fb . ')';

        $ig = bpr_core_social_post_instagram($payload);
        $log['instagram'] = is_wp_error($ig) ? 'Error: ' . $ig->get_error_message() : 'Posted (' . $ig . ')';
    }

    $webhook = trim((string) get_option('bpr_core_social_webhook_url', ''));
    if ($webhook !== '') {
        wp_remote_post($webhook, array(
            'timeout' => 15,
            'blocking' => false,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($payload),
        ));
        $log['webhook'] = 'Sent';
    }

    if (count($log) > 1) {
        update_post_meta($post_id, '_bpr_social_announced', current_time('mysql'));
        update_post_meta($post_id, '_bpr_social_log', $log);
    }
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
    bpr_core_social_announce($post_id);
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

    if (trim((string) get_option('bpr_core_meta_token', '')) === '' && trim((string) get_option('bpr_core_social_webhook_url', '')) === '') {
        echo '<p>' . esc_html__('Not configured. Set up the Meta connection under Bubbles Pets Rescue > Social Sharing.', 'bubbles-pet-rescue-core') . '</p>';
        return;
    }

    $log = get_post_meta($post->ID, '_bpr_social_log', true);
    if (is_array($log) && !empty($log['time'])) {
        echo '<p><strong>' . esc_html(sprintf(__('Last announced: %s', 'bubbles-pet-rescue-core'), $log['time'])) . '</strong></p>';
        foreach (array('facebook' => 'Facebook', 'instagram' => 'Instagram', 'webhook' => 'Webhook') as $key => $label) {
            if (!empty($log[$key])) {
                $is_error = strpos((string) $log[$key], 'Error') === 0;
                echo '<p style="margin:2px 0;' . ($is_error ? 'color:#b32d2e;' : 'color:#1d7f2a;') . '">' . esc_html($label . ': ' . $log[$key]) . '</p>';
            }
        }
    } else {
        echo '<p>' . esc_html__('Not announced yet. Publishing will announce automatically.', 'bubbles-pet-rescue-core') . '</p>';
    }

    echo '<p style="margin-top:8px;"><label><input type="checkbox" name="bpr_core_social_reannounce" value="1"> '
        . esc_html__('Announce to social when I save this post', 'bubbles-pet-rescue-core') . '</label></p>';
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
    bpr_core_social_announce($post_id);
}
add_action('save_post_dog', 'bpr_core_social_handle_reannounce', 30);
add_action('save_post_cat', 'bpr_core_social_handle_reannounce', 30);
